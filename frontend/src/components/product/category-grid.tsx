
// src/components/product/category-grid.tsx
'use client';

import Link from 'next/link';
import Image from 'next/image';
import { useQuery } from '@tanstack/react-query';
import { Category } from '@/types/product';
import { Card, CardContent } from '@/components/ui/card';
import { Loading } from '@/components/ui/loading';
import api from '@/lib/api';
import { API_ENDPOINTS, ROUTES } from '@/lib/constants';

export function CategoryGrid() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['categories'],
    queryFn: () => api.get<{ data: Category[] }>(API_ENDPOINTS.CATEGORIES.LIST),
  });

  if (isLoading) {
    return (
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {[...Array(6)].map((_, i) => (
          <div key={i} className="animate-pulse">
            <div className="aspect-square bg-gray-200 rounded-lg mb-2"></div>
            <div className="h-4 bg-gray-200 rounded"></div>
          </div>
        ))}
      </div>
    );
  }

  if (error || !data?.data?.length) {
    return (
      <div className="text-center py-8">
        <p className="text-gray-500">Gagal memuat kategori</p>
      </div>
    );
  }

  // Show only root categories (no parent)
  const rootCategories = data.data.filter(cat => !cat.parent_id).slice(0, 6);

  return (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      {rootCategories.map((category) => (
        <Link key={category.id} href={`${ROUTES.CATEGORIES}/${category.slug}`}>
          <Card className="group hover:shadow-md transition-shadow">
            <CardContent className="p-4 text-center">
              <div className="aspect-square relative mb-3 bg-gray-100 rounded-lg overflow-hidden">
                {category.image ? (
                  <Image
                    src={category.image}
                    alt={category.name}
                    fill
                    className="object-cover group-hover:scale-105 transition-transform"
                  />
                ) : (
                  <div className="flex items-center justify-center h-full text-gray-400">
                    <span className="text-2xl font-bold">
                      {category.name.charAt(0)}
                    </span>
                  </div>
                )}
              </div>
              <h3 className="font-medium text-sm text-gray-900 group-hover:text-primary-600 transition-colors">
                {category.name}
              </h3>
              {category.products_count !== undefined && (
                <p className="text-xs text-gray-500 mt-1">
                  {category.products_count} produk
                </p>
              )}
            </CardContent>
          </Card>
        </Link>
      ))}
    </div>
  );
}
