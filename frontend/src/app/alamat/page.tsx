// src/app/alamat/page.tsx
import { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Alamat Saya - Kelola Alamat Pengiriman',
  description: 'Kelola alamat pengiriman untuk kemudahan berbelanja',
};

export default function AddressesPage() {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-2xl font-bold mb-8">Alamat Saya</h1>
        <div className="bg-white rounded-lg shadow p-6">
          <p>Halaman alamat sedang dalam pengembangan...</p>
        </div>
      </div>
    </div>
  );
}