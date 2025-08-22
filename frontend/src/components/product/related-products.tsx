
// src/components/product/related-products.tsx
'use client';

import { useQuery } from '@tanstack/react-query';
import { ProductCard } from './product-card';
import { Loading } from '@/components/ui/loading';
import { Product } from '@/types/product';
import api from '@/lib/api';

interface RelatedProductsProps {
  productId: number;
  categoryId?: number;
}

export function RelatedProducts({ productId, categoryId }: RelatedProductsProps) {
  const { data, isLoading } = useQuery({
    queryKey: ['related-products', productId, categoryId],
    queryFn: async () => {
      // Mock API call - replace with actual endpoint
      const response = await api.get<{ data: Product[] }>('/products', {
        category: categoryId,
        exclude: productId,
        limit: 8,
      });
      return response;
    },
  });

  if (isLoading) {
    return (
      <div>
        <h2 className="text-2xl font-bold mb-6">Produk Terkait</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="animate-pulse">
              <div className="aspect-square bg-gray-200 rounded-lg mb-4"></div>
              <div className="space-y-2">
                <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                <div className="h-4 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  if (!data?.data?.length) {
    return null;
  }

  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Produk Terkait</h2>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {data.data.slice(0, 4).map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    </div>
  );
}