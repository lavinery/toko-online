// src/app/not-found.tsx
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { ROUTES } from '@/lib/constants';

export default function NotFound() {
  return (
    <div className="container mx-auto px-4 py-16 text-center">
      <div className="max-w-md mx-auto">
        <h1 className="text-6xl font-bold text-gray-300 mb-4">404</h1>
        <h2 className="text-2xl font-semibold text-gray-900 mb-4">
          Halaman Tidak Ditemukan
        </h2>
        <p className="text-gray-600 mb-8">
          Maaf, halaman yang Anda cari tidak dapat ditemukan.
        </p>
        <div className="space-y-4">
          <Button asChild>
            <Link href={ROUTES.HOME}>Kembali ke Beranda</Link>
          </Button>
          <div>
            <Button variant="outline" asChild>
              <Link href={ROUTES.PRODUCTS}>Lihat Produk</Link>
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}