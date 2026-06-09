export interface ServiceItem {
  id: string;
  name: string;
  price: number;
  unit: string;
  category: "all" | "kiloan" | "satuan" | "express";
  icon: string;
  description: string;
  badge?: string;
}

export interface OrderBasketItem {
  service: ServiceItem;
  quantity: number;
}

export interface BookedOrder {
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
