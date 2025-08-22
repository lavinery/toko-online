// src/components/product/category-grid.tsx - Fixed with mock data
'use client';

import Link from 'next/link';
import Image from 'next/image';
import { Card, CardContent } from '@/components/ui/card';
import { ROUTES } from '@/lib/constants';

// Mock data untuk kategori
const mockCategories = [
  {
    id: 1,
    name: 'Elektronik',
    slug: 'elektronik',
    image: '/images/categories/elektronik.jpg',
    products_count: 245
  },
  {
    id: 2,
    name: 'Fashion',
    slug: 'fashion',
    image: '/images/categories/fashion.jpg',
    products_count: 189
  },
  {
    id: 3,
    name: 'Rumah & Taman',
    slug: 'rumah-taman',
    image: '/images/categories/rumah.jpg',
    products_count: 156
  },
  {
    id: 4,
    name: 'Olahraga',
    slug: 'olahraga',
    image: '/images/categories/olahraga.jpg',
    products_count: 98
  },
  {
    id: 5,
    name: 'Kecantikan',
    slug: 'kecantikan',
    image: '/images/categories/kecantikan.jpg',
    products_count: 167
  },
  {
    id: 6,
    name: 'Makanan',
    slug: 'makanan',
    image: '/images/categories/makanan.jpg',
    products_count: 134
  },
];

export function CategoryGrid() {
  return (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      {mockCategories.map((category) => (
        <Link key={category.id} href={`${ROUTES.CATEGORIES}/${category.slug}`}>
          <Card className="group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <CardContent className="p-4 text-center">
              <div className="aspect-square relative mb-3 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg overflow-hidden">
                {/* Placeholder dengan gradien dan icon */}
                <div className="flex items-center justify-center h-full">
                  <div className="text-4xl">
                    {category.name === 'Elektronik' && '📱'}
                    {category.name === 'Fashion' && '👕'}
                    {category.name === 'Rumah & Taman' && '🏠'}
                    {category.name === 'Olahraga' && '⚽'}
                    {category.name === 'Kecantikan' && '💄'}
                    {category.name === 'Makanan' && '🍕'}
                  </div>
                </div>
                
                {/* Overlay effect */}
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
              </div>
              
              <h3 className="font-semibold text-sm text-gray-900 group-hover:text-blue-600 transition-colors">
                {category.name}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                {category.products_count} produk
              </p>
            </CardContent>
          </Card>
        </Link>
      ))}
    </div>
  );
}