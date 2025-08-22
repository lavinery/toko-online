
// src/components/checkout/checkout-summary.tsx
'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Cart } from '@/types/cart';
import { CheckoutData } from './checkout-flow';
import { formatPrice } from '@/lib/utils';

interface CheckoutSummaryProps {
  cart: Cart;
  data: CheckoutData;
}

export function CheckoutSummary({ cart, data }: CheckoutSummaryProps) {
  const shippingCost = data.shipping?.cost || 0;
  const discount = data.voucher?.discount || 0;
  const total = cart.subtotal + shippingCost - discount;

  return (
    <Card className="sticky top-4">
      <CardHeader>
        <CardTitle className="text-lg">Ringkasan Pesanan</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Items */}
        <div>
          <h4 className="font-medium mb-3">Items ({cart.total_quantity})</h4>
          <div className="space-y-3">
            {cart.items.map((item) => (
              <div key={item.id} className="flex items-center space-x-3">
                <div className="w-12 h-12 bg-gray-100 rounded"></div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium truncate">
                    {item.product.name}
                  </p>
                  {item.variant && (
                    <p className="text-xs text-gray-500">{item.variant.name}</p>
                  )}
                  <p className="text-xs text-gray-500">
                    {item.quantity} × {formatPrice(item.price)}
                  </p>
                </div>
                <div className="text-sm font-medium">
                  {formatPrice(item.subtotal)}
                </div>
              </div>
            ))}
          </div>
        </div>

        <hr />

        {/* Price Breakdown */}
        <div className="space-y-2">
          <div className="flex justify-between text-sm">
            <span>Subtotal</span>
            <span>{formatPrice(cart.subtotal)}</span>
          </div>
          
          {data.shipping && (
            <div className="flex justify-between text-sm">
              <span>Ongkos Kirim</span>
              <span>{formatPrice(shippingCost)}</span>
            </div>
          )}
          
          {data.voucher && (
            <div className="flex justify-between text-sm text-green-600">
              <span>Diskon ({data.voucher.code})</span>
              <span>-{formatPrice(discount)}</span>
            </div>
          )}
          
          <hr />
          
          <div className="flex justify-between font-bold">
            <span>Total</span>
            <span className="text-lg">{formatPrice(total)}</span>
          </div>
        </div>

        {/* Selected Options Summary */}
        {data.address && (
          <div className="pt-4 border-t">
            <h4 className="font-medium mb-2">Alamat Pengiriman</h4>
            <div className="text-sm text-gray-600">
              <p className="font-medium">{data.address.name}</p>
              <p>{data.address.full_address}</p>
            </div>
          </div>
        )}

        {data.shipping && (
          <div>
            <h4 className="font-medium mb-2">Metode Pengiriman</h4>
            <div className="text-sm text-gray-600">
              <p>{data.shipping.courier} - {data.shipping.service}</p>
              <p>Estimasi: {data.shipping.etd}</p>
            </div>
          </div>
        )}

        {data.payment && (
          <div>
            <h4 className="font-medium mb-2">Metode Pembayaran</h4>
            <div className="text-sm text-gray-600">
              <p>{data.payment.method}</p>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}