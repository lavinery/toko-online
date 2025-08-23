// src/app/keranjang/page.tsx
import { Metadata } from 'next';
import { CartPage } from '@/components/cart/cart-page';

export const metadata: Metadata = {
  title: 'Keranjang Belanja - Toko Online',
  description: 'Lihat dan kelola item dalam keranjang belanja Anda',
};

export default function CartPageRoute() {
  return <CartPage />;
}