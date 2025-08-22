/ src/components/checkout/payment-step.tsx
'use client';

import { useState } from 'react';
import { CreditCard, ArrowLeft, Shield, Smartphone, Banknote, QrCode } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { CheckoutData } from './checkout-flow';
import { Cart } from '@/types/cart';
import { formatPrice } from '@/lib/utils';

interface PaymentStepProps {
  data: CheckoutData;
  cart: Cart;
  onNext: (data: Partial<CheckoutData>) => void;
  onBack: () => void;
}

interface PaymentMethod {
  id: string;
  name: string;
  type: 'credit_card' | 'bank_transfer' | 'e_wallet' | 'qris';
  icon: any;
  description: string;
  fee?: number;
  available: boolean;
}

const paymentMethods: PaymentMethod[] = [
  {
    id: 'credit_card',
    name: 'Kartu Kredit/Debit',
    type: 'credit_card',
    icon: CreditCard,
    description: 'Visa, MasterCard, JCB',
    fee: 0,
    available: true,
  },
  {
    id: 'bank_transfer',
    name: 'Transfer Bank',
    type: 'bank_transfer',
    icon: Banknote,
    description: 'BCA, Mandiri, BNI, BRI',
    fee: 0,
    available: true,
  },
  {
    id: 'gopay',
    name: 'GoPay',
    type: 'e_wallet',
    icon: Smartphone,
    description: 'Bayar dengan GoPay',
    fee: 0,
    available: true,
  },
  {
    id: 'ovo',
    name: 'OVO',
    type: 'e_wallet',
    icon: Smartphone,
    description: 'Bayar dengan OVO',
    fee: 0,
    available: true,
  },
  {
    id: 'qris',
    name: 'QRIS',
    type: 'qris',
    icon: QrCode,
    description: 'Scan QR untuk bayar',
    fee: 0,
    available: true,
  },
];

export function PaymentStep({ data, cart, onNext, onBack }: PaymentStepProps) {
  const [selectedPayment, setSelectedPayment] = useState<PaymentMethod | null>(
    data.payment ? paymentMethods.find(p => p.id === data.payment?.method) || null : null
  );
  const [voucherCode, setVoucherCode] = useState(data.voucher?.code || '');
  const [isApplyingVoucher, setIsApplyingVoucher] = useState(false);

  const handleApplyVoucher = async () => {
    if (!voucherCode.trim()) return;

    setIsApplyingVoucher(true);
    try {
      // Mock API call - replace with actual voucher validation
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Mock successful voucher application
      const mockDiscount = 25000;
      onNext({ 
        voucher: { 
          code: voucherCode, 
          discount: mockDiscount 
        } 
      });
    } catch (error) {
      console.error('Failed to apply voucher');
    } finally {
      setIsApplyingVoucher(false);
    }
  };

  const handleContinue = () => {
    if (selectedPayment) {
      onNext({
        payment: {
          method: selectedPayment.name,
          gateway: selectedPayment.id,
        }
      });
    }
  };

  const shippingCost = data.shipping?.cost || 0;
  const discount = data.voucher?.discount || 0;
  const total = cart.subtotal + shippingCost - discount;

  return (
    <div className="space-y-6">
      {/* Voucher Section */}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Kode Voucher</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex space-x-2">
            <div className="flex-1">
              <Input
                placeholder="Masukkan kode voucher"
                value={voucherCode}
                onChange={(e) => setVoucherCode(e.target.value)}
                disabled={isApplyingVoucher}
              />
            </div>
            <Button
              onClick={handleApplyVoucher}
              disabled={!voucherCode.trim() || isApplyingVoucher}
              variant="outline"
            >
              {isApplyingVoucher ? 'Memproses...' : 'Gunakan'}
            </Button>
          </div>
          
          {data.voucher && (
            <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
              <div className="flex items-center justify-between">
                <div>
                  <span className="font-medium text-green-800">
                    Voucher {data.voucher.code} diterapkan
                  </span>
                  <p className="text-sm text-green-600">
                    Hemat {formatPrice(data.voucher.discount)}
                  </p>
                </div>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => {
                    setVoucherCode('');
                    onNext({ voucher: undefined });
                  }}
                  className="text-green-700 hover:text-green-800"
                >
                  Hapus
                </Button>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Payment Methods */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center space-x-2">
            <Shield className="h-5 w-5" />
            <span>Pilih Metode Pembayaran</span>
          </CardTitle>
          <p className="text-sm text-gray-600">
            Total yang harus dibayar: <span className="font-bold">{formatPrice(total)}</span>
          </p>
        </CardHeader>
        <CardContent className="space-y-3">
          {paymentMethods.map((method) => {
            const Icon = method.icon;
            const isSelected = selectedPayment?.id === method.id;
            
            return (
              <div
                key={method.id}
                className={`p-4 border rounded-lg cursor-pointer transition-colors ${
                  !method.available
                    ? 'opacity-50 cursor-not-allowed bg-gray-50'
                    : isSelected
                    ? 'border-primary-500 bg-primary-50'
                    : 'border-gray-200 hover:border-gray-300'
                }`}
                onClick={() => method.available && setSelectedPayment(method)}
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-4">
                    <div className="w-12 h-12 bg-white border rounded-lg flex items-center justify-center">
                      <Icon className="h-6 w-6 text-gray-600" />
                    </div>
                    
                    <div>
                      <div className="flex items-center space-x-2">
                        <span className="font-medium">{method.name}</span>
                        {method.fee === 0 && (
                          <Badge variant="success" className="text-xs">
                            Gratis
                          </Badge>
                        )}
                        {!method.available && (
                          <Badge variant="secondary" className="text-xs">
                            Tidak Tersedia
                          </Badge>
                        )}
                      </div>
                      <p className="text-sm text-gray-600">
                        {method.description}
                      </p>
                    </div>
                  </div>
                  
                  {method.fee > 0 && (
                    <div className="text-right">
                      <div className="text-sm text-gray-600">
                        Biaya: {formatPrice(method.fee)}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            );
          })}

          {/* Security Notice */}
          <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex items-center space-x-2">
              <Shield className="h-5 w-5 text-blue-600" />
              <span className="font-medium text-blue-800">Pembayaran Aman</span>
            </div>
            <p className="text-sm text-blue-700 mt-1">
              Semua transaksi dilindngi dengan enkripsi SSL dan sistem keamanan berlapis.
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Navigation */}
      <div className="flex justify-between">
        <Button variant="outline" onClick={onBack}>
          <ArrowLeft className="h-4 w-4 mr-2" />
          Kembali
        </Button>
        
        <Button
          onClick={handleContinue}
          disabled={!selectedPayment}
          size="lg"
        >
          Lanjut ke Konfirmasi
        </Button>
      </div>
    </div>
  );
}