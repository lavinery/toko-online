
// src/components/product/product-grid.tsx
'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { ProductCard } from './product-card';
import { Loading } from '@/components/ui/loading';
import { Button } from '@/components/ui/button';
import { Product, ProductFilters } from '@/types/product';
import { PaginatedResponse } from '@/types/api';
import api from '@/lib/api';
import { API_ENDPOINTS } from '@/lib/constants';

interface ProductGridProps {
  filters?: ProductFilters;
  className?: string;
}

export function ProductGrid({ filters = {}, className }: ProductGridProps) {
  const [page, setPage] = useState(1);

  const { data, isLoading, error } = useQuery({
    queryKey: ['products', { ...filters, page }],
    queryFn: () => 
      api.get<PaginatedResponse<Product>>(API_ENDPOINTS.PRODUCTS.LIST, {
        ...filters,
        page,
        per_page: 12,
      }),
  });

  if (isLoading) {
    return (
      <div className={`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 ${className}`}>
        {[...Array(12)].map((_, i) => (
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
        <p className="text-gray-500">Gagal memuat produk</p>
        <Button 
          variant="outline" 
          onClick={() => window.location.reload()}
          className="mt-4"
        >
          Coba Lagi
        </Button>
      </div>
    );
  }

  if (!data?.data?.length) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-500">Tidak ada produk ditemukan</p>
        <p className="text-sm text-gray-400 mt-2">
          Coba ubah filter atau kata kunci pencarian
        </p>
      </div>
    );
  }

  return (
    <div className={className}>
      {/* Products Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {data.data.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

      {/* Pagination */}
      {data.meta.last_page > 1 && (
        <div className="flex items-center justify-center space-x-4 mt-12">
          <Button
            variant="outline"
            onClick={() => setPage(page - 1)}
            disabled={page === 1}
          >
            Sebelumnya
          </Button>
          
          <div className="flex items-center space-x-2">
            {[...Array(Math.min(5, data.meta.last_page))].map((_, i) => {
              const pageNum = i + 1;
              return (
                <Button
                  key={pageNum}
                  variant={page === pageNum ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => setPage(pageNum)}
                >
                  {pageNum}
                </Button>
              );
            })}
          </div>

          <Button
            variant="outline"
            onClick={() => setPage(page + 1)}
            disabled={page === data.meta.last_page}
          >
            Selanjutnya
          </Button>
        </div>
      )}

      {/* Results Info */}
      <div className="text-center mt-8">
        <p className="text-sm text-gray-500">
          Menampilkan {data.meta.from}-{data.meta.to} dari {data.meta.total} produk
        </p>
      </div>
    </div>
  );
}
