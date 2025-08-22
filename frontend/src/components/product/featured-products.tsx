// src/components/product/featured-products.tsx - Fixed with mock data
'use client';

import { ProductCard } from './product-card';

// Mock data untuk produk unggulan
const mockProducts = [
  {
    id: 1,
    name: 'iPhone 15 Pro Max 256GB',
    slug: 'iphone-15-pro-max-256gb',
    sku: 'IP15PM256',
    description: 'iPhone terbaru dengan chip A17 Pro dan kamera 48MP',
    short_description: 'iPhone 15 Pro Max dengan teknologi terdepan',
    price: 18999000,
    compare_price: 20999000,
    display_price: 'Rp 18.999.000',
    weight: 221,
    status: 'active' as const,
    is_featured: true,
    total_stock: 50,
    available_stock: 50,
    has_variants: false,
    images: [
      {
        id: 1,
        path: '/images/products/iphone-15-pro.jpg',
        url: '/images/products/iphone-15-pro.jpg',
        alt_text: 'iPhone 15 Pro Max',
        is_primary: true,
        sort_order: 1,
      }
    ],
    primary_image: {
      id: 1,
      path: '/images/products/iphone-15-pro.jpg',
      url: '/images/products/iphone-15-pro.jpg',
      alt_text: 'iPhone 15 Pro Max',
      is_primary: true,
      sort_order: 1,
    },
    categories: [
      {
        id: 1,
        name: 'Elektronik',
        slug: 'elektronik',
        sort_order: 1,
        is_active: true,
      }
    ],
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  {
    id: 2,
    name: 'Samsung Galaxy S24 Ultra',
    slug: 'samsung-galaxy-s24-ultra',
    sku: 'SGS24U',
    description: 'Samsung flagship dengan S Pen dan kamera 200MP',
    short_description: 'Galaxy S24 Ultra dengan S Pen built-in',
    price: 16999000,
    compare_price: 18999000,
    display_price: 'Rp 16.999.000',
    weight: 232,
    status: 'active' as const,
    is_featured: true,
    total_stock: 30,
    available_stock: 30,
    has_variants: false,
    images: [
      {
        id: 2,
        path: '/images/products/galaxy-s24.jpg',
        url: '/images/products/galaxy-s24.jpg',
        alt_text: 'Samsung Galaxy S24 Ultra',
        is_primary: true,
        sort_order: 1,
      }
    ],
    primary_image: {
      id: 2,
      path: '/images/products/galaxy-s24.jpg',
      url: '/images/products/galaxy-s24.jpg',
      alt_text: 'Samsung Galaxy S24 Ultra',
      is_primary: true,
      sort_order: 1,
    },
    categories: [
      {
        id: 1,
        name: 'Elektronik',
        slug: 'elektronik',
        sort_order: 1,
        is_active: true,
      }
    ],
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  // Add more mock products...
  {
    id: 3,
    name: 'MacBook Air M3 13"',
    slug: 'macbook-air-m3-13',
    sku: 'MBA13M3',
    description: 'MacBook Air dengan chip M3 performa tinggi',
    short_description: 'MacBook Air M3 ringan dan powerful',
    price: 17999000,
    display_price: 'Rp 17.999.000',
    weight: 1240,
    status: 'active' as const,
    is_featured: true,
    total_stock: 20,
    available_stock: 20,
    has_variants: false,
    images: [
      {
        id: 3,
        path: '/images/products/macbook-air.jpg',
        url: '/images/products/macbook-air.jpg',
        alt_text: 'MacBook Air M3',
        is_primary: true,
        sort_order: 1,
      }
    ],
    primary_image: {
      id: 3,
      path: '/images/products/macbook-air.jpg',
      url: '/images/products/macbook-air.jpg',
      alt_text: 'MacBook Air M3',
      is_primary: true,
      sort_order: 1,
    },
    categories: [
      {
        id: 1,
        name: 'Elektronik',
        slug: 'elektronik',
        sort_order: 1,
        is_active: true,
      }
    ],
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  {
    id: 4,
    name: 'AirPods Pro (3rd Gen)',
    slug: 'airpods-pro-3rd-gen',
    sku: 'APP3G',
    description: 'AirPods Pro dengan spatial audio dan noise cancelling',
    short_description: 'AirPods Pro generasi terbaru',
    price: 3999000,
    compare_price: 4499000,
    display_price: 'Rp 3.999.000',
    weight: 61,
    status: 'active' as const,
    is_featured: true,
    total_stock: 100,
    available_stock: 100,
    has_variants: false,
    images: [
      {
        id: 4,
        path: '/images/products/airpods-pro.jpg',
        url: '/images/products/airpods-pro.jpg',
        alt_text: 'AirPods Pro 3rd Gen',
        is_primary: true,
        sort_order: 1,
      }
    ],
    primary_image: {
      id: 4,
      path: '/images/products/airpods-pro.jpg',
      url: '/images/products/airpods-pro.jpg',
      alt_text: 'AirPods Pro 3rd Gen',
      is_primary: true,
      sort_order: 1,
    },
    categories: [
      {
        id: 1,
        name: 'Elektronik',
        slug: 'elektronik',
        sort_order: 1,
        is_active: true,
      }
    ],
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
];

export function FeaturedProducts() {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      {mockProducts.map((product) => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}