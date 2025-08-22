/ src/components/checkout/review-step.tsx
'use client';

import { useState } from 'react';
import { ArrowLeft, MapPin, Truck, CreditCard, Edit, AlertCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { CheckoutData } from './checkout-flow';
import { Cart } from '@/types/cart';
import { formatPrice } from '@/lib/utils';

interface ReviewStepProps {
  data: CheckoutData;
  cart: Cart;
  onBack: () => void;
  onSubmit: (finalData: CheckoutData) => Promise<void>;
  isProcessing: boolean;
}

export function ReviewStep({ data, cart, onBack, onSubmit, isProcessing }: ReviewStepProps) {
  const [agreedToTerms, setAgreedToTerms] = useState(false);
  const [notes, setNotes] = useState('');

  const shippingCost = data.shipping?.cost || 0;
  const discount = data.voucher?.discount || 0;
  const total = cart.subtotal + shippingCost - discount;

  const handleSubmit = async () => {
    if (!agreedToTerms) return;

    const finalData: CheckoutData = {
      ...data,
      notes,
    };

    await onSubmit(finalData);
  };

  return (
    <div className="space-y-6">
      {/* Order Summary */}
      <Card>
        <CardHeader>
          <CardTitle>Konfirmasi Pesanan</CardTitle>
          <p className="text-sm text-gray-600">
            Periksa kembali detail pesanan Anda sebelum melanjutkan pembayaran
          </p>
        </CardHeader>
        <CardContent className="space-y-6">
          {/* Address */}
          <div className="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
            <div className="flex items-start space-x-3">
              <MapPin className="h-5 w-5 text-gray-600 mt-0.5" />
              <div>
                <h4 className="font-medium text-gray-900 mb-1">Alamat Pengiriman</h4>
                <div className="text-sm text-gray-600">
                  <p className="font-medium">{data.address?.name}</p>
                  <p>{data.address?.phone}</p>
                  <p>{data.address?.full_address}</p>
                </div>
              </div>
            </div>
            <Button variant="ghost" size="sm">
              <Edit className="h-4 w-4" />
            </Button>
          </div>

          {/* Shipping */}
          <div className="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
            <div className="flex items-start space-x-3">
              <Truck className="h-5 w-5 text-gray-600 mt-0.5" />
              <div>
                <h4 className="font-medium text-gray-900 mb-1">Metode Pengiriman</h4>
                <div className="text-sm text-gray-600">
                  <p>{data.shipping?.courier} - {data.shipping?.service}</p>
                  <p>Estimasi: {data.shipping?.etd}</p>
                  <p className="font-medium">{formatPrice(shippingCost)}</p>
                </div>
              </div>
            </div>
            <Button variant="ghost" size="sm">
              <Edit className="h-4 w-4" />
            </Button>
          </div>

          {/* Payment */}
          <div className="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
            <div className="flex items-start space-x-3">
              <CreditCard className="h-5 w-5 text-gray-600 mt-0.5" />
              <div>
                <h4 className="font-medium text-gray-900 mb-1">Metode Pembayaran</h4>
                <div className="text-sm text-gray-600">
                  <p>{data.payment?.method}</p>
                </div>
              </div>
            </div>
            <Button variant="ghost" size="sm">
              <Edit className="h-4 w-4" />
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Order Items */}
      <Card>
        <CardHeader>
          <CardTitle>Item Pesanan ({cart.total_quantity})</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {cart.items.map((item) => (
              <div key={item.id} className="flex items-center space-x-4 p-3 border rounded-lg">
                <div className="w-16 h-16 bg-gray-100 rounded-lg"></div>
                <div className="flex-1">
                  <h4 className="font-medium">{item.product.name}</h4>
                  {item.variant && (
                    <p className="text-sm text-gray-600">{item.variant.name}</p>
                  )}
                  <p className="text-sm text-gray-600">
                    {item.quantity} × {formatPrice(item.price)}
                  </p>
                </div>
                <div className="font-medium">
                  {formatPrice(item.subtotal)}
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Price Summary */}
      <Card>
        <CardContent className="p-6">
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Subtotal</span>
              <span className="font-medium">{formatPrice(cart.subtotal)}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Ongkos Kirim</span>
              <span className="font-medium">{formatPrice(shippingCost)}</span>
            </div>
            {data.voucher && (
              <div className="flex justify-between text-green-600">
                <span>Diskon ({data.voucher.code})</span>
                <span>-{formatPrice(discount)}</span>
              </div>
            )}
            <hr />
            <div className="flex justify-between text-lg font-bold">
              <span>Total Pembayaran</span>
              <span>{formatPrice(total)}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Order Notes */}
      <Card>
        <CardHeader>
          <CardTitle>Catatan Pesanan (Opsional)</CardTitle>
        </CardHeader>
        <CardContent>
          <Label htmlFor="notes" className="text-sm text-gray-600">
            Tambahkan catatan khusus untuk pesanan Anda
          </Label>
          <Textarea
            id="notes"
            placeholder="Contoh: Tolong kirim pada siang hari, titip ke satpam, dll."
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            className="mt-2"
            rows={3}
          />
        </CardContent>
      </Card>

      {/* Terms & Conditions */}
      <Card className="border-orange-200 bg-orange-50">
        <CardContent className="p-6">
          <div className="flex items-start space-x-3">
            <AlertCircle className="h-5 w-5 text-orange-600 mt-0.5" />
            <div className="flex-1">
              <div className="flex items-center space-x-2 mb-3">
                <Checkbox
                  id="terms"
                  checked={agreedToTerms}
                  onCheckedChange={(checked) => setAgreedToTerms(checked as boolean)}
                />
                <Label htmlFor="terms" className="text-sm font-medium">
                  Saya setuju dengan{' '}
                  <a href="/terms" className="text-primary-600 hover:underline">
                    Syarat & Ketentuan
                  </a>{' '}
                  dan{' '}
                  <a href="/privacy" className="text-primary-600 hover:underline">
                    Kebijakan Privasi
                  </a>
                </Label>
              </div>
              <p className="text-xs text-gray-600">
                Dengan melanjutkan pembayaran, Anda menyetujui semua syarat dan ketentuan 
                yang berlaku serta kebijakan privasi kami.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Navigation */}
      <div className="flex justify-between">
        <Button variant="outline" onClick={onBack} disabled={isProcessing}>
          <ArrowLeft className="h-4 w-4 mr-2" />
          Kembali
        </Button>
        
        <Button
          onClick={handleSubmit}
          disabled={!agreedToTerms || isProcessing}
          size="lg"
          className="min-w-[200px]"
        >
          {isProcessing ? (
            <>
              <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
              Memproses...
            </>
          ) : (
            `Bayar ${formatPrice(total)}`
          )}
        </Button>
      </div>
    </div>
  );