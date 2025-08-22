
// src/types/cart.ts
import { Product, ProductVariant } from './product';

export interface Cart {
  id: number;
  user_id?: number;
  session_id?: string;
  items: CartItem[];
  total_quantity: number;
  subtotal: number;
  total: number;
  created_at: string;
  updated_at: string;
}

export interface CartItem {
  id: number;
  cart_id: number;
  product_id: number;
  product_variant_id?: number;
  quantity: number;
  price: number;
  subtotal: number;
  product: Product;
  variant?: ProductVariant;
}

export interface AddToCartData {
  product_id: number;
  product_variant_id?: number;
  quantity: number;
}