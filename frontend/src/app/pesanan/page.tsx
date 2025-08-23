// src/app/pesanan/page.tsx
import { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Pesanan Saya - Riwayat Pembelian',
  description: 'Lihat riwayat pesanan dan status pengiriman',
};

export default function OrdersPage() {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-2xl font-bold mb-8">Pesanan Saya</h1>
        <div className="bg-white rounded-lg shadow p-6">
          <p>Halaman pesanan sedang dalam pengembangan...</p>
        </div>
      </div>
    </div>
  );
}