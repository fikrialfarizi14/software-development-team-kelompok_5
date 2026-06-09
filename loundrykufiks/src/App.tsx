import { useState, useEffect } from "react";
import Navbar from "./components/Navbar";
import Home from "./components/Home";
import Services from "./components/Services";
import BookingForm from "./components/BookingForm";
import AdminDashboard from "./components/AdminDashboard";
import OwnerDashboard from "./components/OwnerDashboard";
import Auth from "./components/Auth";
import Footer from "./components/Footer";
import { OrderBasketItem, BookedOrder } from "./types";

export default function App() {
  // To show login as the primary entry point when user first opens the app,
  // we set the default opening tab state to "booking".
  const [currentTab, setCurrentTab] = useState<string>("booking");
  
  const [user, setUser] = useState<{ name: string; username: string; phone: string; address: string; role: "pelanggan" | "admin" | "owner" } | null>(null);
  const [authMode, setAuthMode] = useState<"login" | "register">("login");
  const [basket, setBasket] = useState<OrderBasketItem[]>([]);
  const [isExpress, setIsExpress] = useState<boolean>(false);
  const [ordersList, setOrdersList] = useState<BookedOrder[]>([]);

  // Fetch initial orders from our Express backend
  const refreshOrders = async () => {
    try {
      const response = await fetch("/api/orders");
      const result = await response.json();
      if (response.ok && result.success) {
        setOrdersList(result.data);
      }
    } catch (error) {
      console.warn("Gagal menyinkronkan daftar pesanan dari server:", error);
    }
  };

  // Run on mount
  useEffect(() => {
    refreshOrders();
  }, []);

  const addNewOrderClientSide = (newOrder: BookedOrder) => {
    setOrdersList((prev) => [newOrder, ...prev]);
  };

  const updateOrderStatus = (orderId: string, status: BookedOrder["status"]) => {
    setOrdersList((prev) =>
      prev.map((order) =>
        order.id === orderId ? { ...order, status } : order
      )
    );
  };

  const handleLogout = () => {
    setUser(null);
    setCurrentTab("booking"); // Back to central gateway
  };

  const handleAuthTrigger = (mode: "login" | "register") => {
    setAuthMode(mode);
    setCurrentTab("booking");
  };

  return (
    <div className="min-h-screen flex flex-col justify-between bg-[#f8f9ff] font-sans antialiased text-gray-900 selection:bg-[#005da7] selection:text-white">
      
      {/* Dynamic Header / Navbar */}
      <Navbar 
        currentTab={currentTab} 
        setCurrentTab={setCurrentTab} 
        user={user} 
        onLogout={handleLogout} 
        onAuthTrigger={handleAuthTrigger} 
      />

      {/* Primary viewport switch */}
      <main className="flex-grow">
        
        {/* Only allow viewing other tabs if it's the customer role or guests */}
        {currentTab === "home" && (
          <Home setCurrentTab={setCurrentTab} />
        )}
        
        {currentTab === "services" && (
          <Services
            setCurrentTab={setCurrentTab}
            basket={basket}
            setBasket={setBasket}
            isExpress={isExpress}
            setIsExpress={setIsExpress}
          />
        )}
        
        {currentTab === "booking" && (
          user ? (
            // User authenticated -> route based on their explicit role
            user.role === "pelanggan" ? (
              <BookingForm
                basket={basket}
                setBasket={setBasket}
                isExpress={isExpress}
                setIsExpress={setIsExpress}
                ordersList={ordersList}
                refreshOrders={refreshOrders}
                addNewOrderClientSide={addNewOrderClientSide}
                user={user}
              />
            ) : user.role === "admin" ? (
              <AdminDashboard
                ordersList={ordersList}
                refreshOrders={refreshOrders}
                updateOrderStatus={updateOrderStatus}
              />
            ) : (
              <OwnerDashboard
                ordersList={ordersList}
                refreshOrders={refreshOrders}
              />
            )
          ) : (
            // Not authenticated -> show the secure login/register gate
            <div className="py-12 bg-[#f8f9ff]">
              <Auth 
                onSuccess={(userData) => {
                  setUser(userData);
                  // Dynamic routing based on role on successful login
                  setCurrentTab("booking");
                }} 
                initialMode={authMode} 
              />
            </div>
          )
        )}
      </main>

      {/* Footer across all layouts */}
      <Footer />

    </div>
  );
}