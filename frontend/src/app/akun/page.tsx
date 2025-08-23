// src/app/akun/page.tsx
import { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Akun Saya - Kelola Profil',
  description: 'Kelola informasi akun dan preferensi Anda',
};

export default function AccountPage() {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-2xl font-bold mb-8">Akun Saya</h1>
        <div className="bg-white rounded-lg shadow p-6">
          <p>Halaman akun sedang dalam pengembangan...</p>
        </div>
      </div>
    </div>
  );
}