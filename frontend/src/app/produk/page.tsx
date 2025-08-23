// src/app/produk/page.tsx
import { Metadata } from 'next';
import { Suspense } from 'react';
import { ProductGrid } from '@/components/product/product-grid';
import { ProductFilters } from '@/components/product/product-filters';
import { Loading } from '@/components/ui/loading';

export const metadata: Metadata = {
  title: 'Produk - Semua Produk Terbaik',
  description: 'Jelajahi koleksi produk lengkap dengan harga terbaik dan kualitas terjamin',
};

interface ProductsPageProps {
  searchParams: {
    search?: string;
    category?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
    page?: string;
  };
}

export default function ProductsPage({ searchParams }: ProductsPageProps) {
  const filters = {
    search: searchParams.search,
    category: searchParams.category,
    min_price: searchParams.min_price ? parseInt(searchParams.min_price) : undefined,
    max_price: searchParams.max_price ? parseInt(searchParams.max_price) : undefined,
    sort: searchParams.sort as any,
    page: searchParams.page ? parseInt(searchParams.page) : 1,
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            {searchParams.search ? `Hasil pencarian "${searchParams.search}"` : 'Semua Produk'}
          </h1>
          <p className="text-gray-600">
            Temukan produk terbaik dengan harga terjangkau
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
          {/* Filters Sidebar */}
          <div className="lg:col-span-1">
            <Suspense fallback={<Loading />}>
              <ProductFilters />
            </Suspense>
          </div>

          {/* Products Grid */}
          <div className="lg:col-span-3">
            <Suspense fallback={<Loading />}>
              <ProductGrid filters={filters} />
            </Suspense>
          </div>
        </div>
      </div>
    </div>
  );
}