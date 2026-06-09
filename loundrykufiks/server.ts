import express from "express";
import path from "path";
import dotenv from "dotenv";
import { GoogleGenAI } from "@google/genai";
import { createServer as createViteServer } from "vite";

// Load variables
dotenv.config();

const app = express();
const PORT = 3000;

// Middleware
app.use(express.json());


const ai = null;

// In-memory simple database for simulation
interface Order {
  id: string;
  name: string;
  phone: string;
  address: string;
  pickupDate: string;
  pickupTime: string;
  services: {
    id: string;
    name: string;
    quantity: number;
    price: number;
    unit: string;
  }[];
  notes?: string;
  status: "Diproses" | "Penjemputan" | "Pencucian" | "Pengiriman" | "Selesai";
  total: number;
  createdAt: string;
}

const orders: Order[] = [
  {
    id: "OR-2026-001",
    name: "Budiman Nafiah",
    phone: "081234567890",
    address: "Jl. Sudirman No. 23, Jakarta Selatan",
    pickupDate: "2026-06-08",
    pickupTime: "10:00 - 12:00",
    services: [
      { id: "reguler", name: "Cuci & Lipat Reguler", quantity: 5, price: 10000, unit: "kg" }
    ],
    notes: "Tolong pisahkan pakaian putih dan berwarna.",
    status: "Penjemputan",
    total: 50000,
    createdAt: new Date().toISOString()
  }
];

// API
app.post("/api/order/submit", (req, res) => {
  try {
    const { name, phone, address, pickupDate, pickupTime, services, notes, total } = req.body;
    
    if (!name || !phone || !address || !pickupDate || !pickupTime || !services || services.length === 0) {
      return res.status(400).json({ error: "Mohon lengkapi semua data wajib." });
    }
    
    const randomId = `OR-2026-${Math.floor(100 + Math.random() * 900)}`;
    const newOrder: Order = {
      id: randomId,
      name,
      phone,
      address,
      pickupDate,
      pickupTime,
      services,
      notes,
      total,
      status: "Diproses",
      createdAt: new Date().toISOString()
    };
    
    orders.unshift(newOrder); // Add to the beginning of list
    return res.json({ success: true, order: newOrder });
  } catch (error: any) {
    return res.status(500).json({ error: error.message || "Gagal membuat pesanan." });
  }
});


app.get("/api/orders", (req, res) => {
  return res.json({ success: true, data: orders });
});

// 2b. API buat admin
app.post("/api/order/update-status", (req, res) => {
  try {
    const { orderId, status } = req.body;
    if (!orderId || !status) {
      return res.status(400).json({ error: "Order ID dan status wajib diisi." });
    }
    const matchedOrder = orders.find(o => o.id === orderId);
    if (matchedOrder) {
      matchedOrder.status = status;
      return res.json({ success: true, order: matchedOrder });
    } else {
      return res.status(404).json({ error: "Pesanan tidak ditemukan." });
    }
  } catch (error: any) {
    return res.status(500).json({ error: "Gagal memperbarui status order." });
  }
});


app.post("/api/gemini/care-guide", (req, res) => {
  try {
    const { fabricType, stainType } = req.body;

    // Database tiruan untuk memberikan panduan mencuci otomatis
    let guideResponse = `Panduan umum untuk bahan ${fabricType || 'pakaian'} dengan noda ${stainType || 'ringan'}: Gunakan detergen cair lembut, cuci dengan air bersuhu normal, dan hindari menyikat terlalu keras agar serat kain tidak rusak.`;

    const fabric = (fabricType || '').toLowerCase();
    const stain = (stainType || '').toLowerCase();

    // Logika pintar otomatis berdasarkan jenis kain dan noda
    if (fabric.includes('batik') || fabric.includes('sutra')) {
      guideResponse = `[Panduan Khusus ${fabricType}] Hindari penggunaan mesin cuci biasa. Gunakan sabun lerak atau sampo bayi. Jangan diperas melintir, cukup diangin-anginkan di tempat teduh tanpa terkena matahari langsung agar warna tidak pudar.`;
    } else if (fabric.includes('wol') || fabric.includes('rajut')) {
      guideResponse = `[Panduan Khusus ${fabricType}] Cuci dengan air dingin menggunakan metode pencucian tangan (handwash). Jemur pakaian secara mendatar (flat dry) di atas handuk kering agar bentuk pakaian tidak melar akibat beban air.`;
    } else if (fabric.includes('jeans') || fabric.includes('denim')) {
      guideResponse = `[Panduan Khusus ${fabricType}] Balik celana (bagian dalam di luar) sebelum dicuci untuk menjaga warna denim. Gunakan air dingin dan jemur dengan cara digantung terbalik.`;
    }

    if (stain.includes('darah') || stain.includes('kopi')) {
      guideResponse += ` Tambahan info untuk noda ${stainType}: Segera bilas dengan air mengalir dingin sebelum dicuci dengan sabun. Jangan gunakan air hangat karena justru akan mengunci noda di serat kain.`;
    } else if (stain.includes('minyak') || stain.includes('makanan')) {
      guideResponse += ` Tambahan info untuk noda ${stainType}: Oleskan sedikit sabun pencuci piring cair pada area noda, diamkan selama 5-10 menit, lalu gosok perlahan sebelum dimasukkan ke mesin cuci.`;
    }

    // Kirim jawaban kembali ke tampilan web
    res.json({ text: guideResponse });

  } catch (error) {
    console.error("Error pada panduan perawatan:", error);
    res.status(500).json({ error: "Gagal memproses panduan perawatan pakaian." });
  }
});;


async function startServer() {
  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), "dist");
    app.use(express.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`[LaundryKu Fullstack] Server beralih ke port ${PORT} (http://localhost:${PORT})`);
  });
}

startServer();

