// src/app/kategori/[slug]/page.tsx
import { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import { ProductGrid } from '@/components/product/product-grid';
import { Loading } from '@/components/ui/loading';

interface CategoryPageProps {
  params: { slug: string };
  searchParams: {
    search?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
    page?: string;
  };
}

export async function generateMetadata({ params }: CategoryPageProps): Promise<Metadata> {
  // Mock category data - replace with actual API call
  const categoryName = params.slug.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
  
  return {
    title: `${categoryName} - Kategori Produk`,
    description: `Jelajahi produk ${categoryName} terbaik dengan harga terjangkau`,
  };
}

export default function CategoryPage({ params, searchParams }: CategoryPageProps) {
  const filters = {
    category: params.slug,
    search: searchParams.search,
    min_price: searchParams.min_price ? parseInt(searchParams.min_price) : undefined,
    max_price: searchParams.max_price ? parseInt(searchParams.max_price) : undefined,
    sort: searchParams.sort as any,
    page: searchParams.page ? parseInt(searchParams.page) : 1,
  };

  const categoryName = params.slug.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <nav className="flex items-center space-x-2 text-sm mb-4">
            <a href="/" className="text-primary-600 hover:text-primary-700">Beranda</a>
            <span className="text-gray-400">/</span>
            <a href="/kategori" className="text-primary-600 hover:text-primary-700">Kategori</a>
            <span className="text-gray-400">/</span>
            <span className="text-gray-500">{categoryName}</span>
          </nav>
          
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            {categoryName}
          </h1>
          <p className="text-gray-600">
            Temukan produk {categoryName.toLowerCase()} terbaik dengan kualitas terjamin
          </p>
        </div>

        {/* Products */}
        <Suspense fallback={<Loading />}>
          <ProductGrid filters={filters} />
        </Suspense>
      </div>
    </div>
  );
}