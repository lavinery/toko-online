// src/types/product.ts
export interface Product {
  id: number;
  name: string;
  slug: string;
  sku: string;
  description?: string;
  short_description: string;
  price: number;
  compare_price?: number;
  display_price: string;
  weight: number;
  dimensions?: string;
  status: 'active' | 'inactive' | 'draft';
  is_featured: boolean;
  total_stock: number;
  available_stock: number;
  has_variants: boolean;
  images: ProductImage[];
  primary_image?: ProductImage;
  categories: Category[];
  variants?: ProductVariant[];
  specifications?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface ProductImage {
  id: number;
  path: string;
  url: string;
  alt_text?: string;
  is_primary: boolean;
  sort_order: number;
}

export interface ProductVariant {
  id: number;
  name: string;
  sku: string;
  price_adjustment: number;
  final_price: number;
  stock: number;
  in_stock: boolean;
  sort_order: number;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image?: string;
  parent_id?: number;
  sort_order: number;
  is_active: boolean;
  children?: Category[];
  parent?: Category;
  products_count?: number;
}

export interface ProductFilters {
  search?: string;
  category?: string;
  min_price?: number;
  max_price?: number;
  featured?: boolean;
  sort?: 'created_at' | 'price_asc' | 'price_desc' | 'name' | 'popular';
  order?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}