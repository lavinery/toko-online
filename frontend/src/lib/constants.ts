
// src/lib/constants.ts
export const APP_NAME = process.env.NEXT_PUBLIC_APP_NAME || 'Toko Online';
export const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
export const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000';

export const ROUTES = {
  HOME: '/',
  PRODUCTS: '/produk',
  CATEGORIES: '/kategori',
  CART: '/keranjang',
  CHECKOUT: '/checkout',
  LOGIN: '/login',
  REGISTER: '/register',
  ACCOUNT: '/akun',
  ORDERS: '/pesanan',
  ADDRESSES: '/alamat',
} as const;

export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
    LOGOUT: '/auth/logout',
    ME: '/auth/me',
    REFRESH: '/auth/refresh',
  },
  PRODUCTS: {
    LIST: '/products',
    DETAIL: (slug: string) => `/products/${slug}`,
    FEATURED: '/products/featured',
    SEARCH: '/products/search',
  },
  CATEGORIES: {
    LIST: '/categories',
    DETAIL: (slug: string) => `/categories/${slug}`,
  },
  CART: {
    GET: '/cart',
    ADD: '/cart/items',
    UPDATE: (id: number) => `/cart/items/${id}`,
    REMOVE: (id: number) => `/cart/items/${id}`,
    CLEAR: '/cart/clear',
    MERGE: '/cart/merge',
  },
  CHECKOUT: '/checkout',
  ORDERS: {
    LIST: '/orders',
    DETAIL: (code: string) => `/orders/${code}`,
  },
  SHIPPING: {
    PROVINCES: '/shipping/provinces',
    CITIES: '/shipping/cities',
    COST: '/shipping/cost',
  },
} as const;

export const PAYMENT_METHODS = {
  MIDTRANS: 'midtrans',
  XENDIT: 'xendit',
} as const;

export const ORDER_STATUS = {
  PENDING: 'pending',
  PAID: 'paid',
  FAILED: 'failed',
  EXPIRED: 'expired',
  SHIPPED: 'shipped',
  DELIVERED: 'delivered',
  CANCELLED: 'cancelled',
} as const;

export const CART_STORAGE_KEY = 'guest_cart';
export const AUTH_TOKEN_KEY = 'auth_token';
