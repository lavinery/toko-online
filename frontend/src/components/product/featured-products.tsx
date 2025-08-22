// src/components/product/featured-products.tsx
'use client';

import { useQuery } from '@tanstack/react-query';
import { ProductCard } from './product-card';
import { Loading } from '@/components/ui/loading';
import { Product } from '@/types/product';
import api from '@/lib/api';
import { API_ENDPOINTS } from '@/lib/constants';

export function FeaturedProducts() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['featured-products'],
    queryFn: () => api.get<{ data: Product[] }>(API_ENDPOINTS.PRODUCTS.FEATURED),
  });

  if (isLoading) {
    return (
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {[...Array(8)].map((_, i) => (
          <div key={i} className="animate-pulse">
            <div className="aspect-square bg-gray-200 rounded-lg mb-4"></div>
            <div className="space-y-2">
              <div className="h-4 bg-gray-200 rounded w-3/4"></div>
              <div className="h-4 bg-gray-200 rounded w-1/2"></div>
              <div className="h-6 bg-gray-200 rounded w-1/3"></div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-500">Gagal memuat produk unggulan</p>
      </div>
    );
  }

  if (!data?.data?.length) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-500">Tidak ada produk unggulan</p>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      {data.data.map((product) => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}
