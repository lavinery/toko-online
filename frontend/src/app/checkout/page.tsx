// src/app/checkout/page.tsx
import { Metadata } from 'next';
import { CheckoutFlow } from '@/components/checkout/checkout-flow';

export const metadata: Metadata = {
  title: 'Checkout - Selesaikan Pesanan',
  description: 'Selesaikan pembayaran dan pengiriman pesanan Anda',
};

export default function CheckoutPage() {
  return (
    <div className="min-h-screen bg-gray-50">
      <CheckoutFlow />
    </div>
  );
}
