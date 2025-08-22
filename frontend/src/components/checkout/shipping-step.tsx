
// src/components/checkout/shipping-step.tsx
'use client';

import { useState, useEffect } from 'react';
import { ArrowLeft, Truck, Clock } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckoutData } from './checkout-flow';
import { Cart } from '@/types/cart';
import { formatPrice } from '@/lib/utils';

interface ShippingStepProps {
  data: CheckoutData;
  cart: Cart;
  onNext: (data: Partial<CheckoutData>) => void;
  onBack: () => void;
}

interface ShippingOption {
  courier: string;
  service: string;
  cost: number;
  etd: string;
  description: string;
}

export function ShippingStep({ data, cart, onNext, onBack }: ShippingStepProps) {
  const [shippingOptions, setShippingOptions] = useState<ShippingOption[]>([]);
  const [selectedShipping, setSelectedShipping] = useState<ShippingOption | null>(
    data.shipping || null
  );
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    fetchShippingOptions();
  }, []);

  const fetchShippingOptions = async () => {
    setIsLoading(true);
    try {
      // Mock API call - replace with actual shipping cost calculation
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      const mockOptions: ShippingOption[] = [
        {
          courier: 'JNE',
          service: 'REG',
          cost: 15000,
          etd: '2-3 hari',
          description: 'Layanan reguler JNE'
        },
        {
          courier: 'JNE',
          service: 'YES',
          cost: 25000,
          etd: '1-2 hari',
          description: 'Layanan express JNE'
        },
        {
          courier: 'JNT',
          service: 'EZ',
          cost: 12000,
          etd: '2-4 hari',
          description: 'Layanan ekonomis J&T'
        },
        {
          courier: 'SiCepat',
          service: 'REG',
          cost: 18000,
          etd: '1-3 hari',
          description: 'Layanan reguler SiCepat'
        },
      ];
      
      setShippingOptions(mockOptions);
    } catch (error) {
      console.error('Failed to fetch shipping options:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleContinue = () => {
    if (selectedShipping) {
      onNext({ shipping: selectedShipping });
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h2 className="text-xl font-semibold">Menghitung Ongkos Kirim...</h2>
        <div className="space-y-4">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="animate-pulse">
              <div className="h-24 bg-gray-200 rounded-lg"></div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h2 className="text-xl font-semibold">Pilih Metode Pengiriman</h2>
      
      {/* Shipping Address Info */}
      <Card className="bg-blue-50 border-blue-200">
        <CardContent className="p-4">
          <div className="flex items-start space-x-3">
            <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
              <Truck className="h-4 w-4 text-blue-600" />
            </div>
            <div>
              <h4 className="font-medium text-blue-900 mb-1">Dikirim ke:</h4>
              <p className="text-sm text-blue-800">
                {data.address?.name} - {data.address?.phone}
              </p>
              <p className="text-sm text-blue-700">
                {data.address?.full_address}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Shipping Options */}
      <div className="space-y-3">
        {shippingOptions.map((option, index) => {
          const isSelected = selectedShipping?.courier === option.courier && 
                           selectedShipping?.service === option.service;
          
          return (
            <Card
              key={index}
              className={`cursor-pointer border-2 transition-colors ${
                isSelected
                  ? 'border-primary-500 bg-primary-50'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
              onClick={() => setSelectedShipping(option)}
            >
              <CardContent className="p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-4">
                    <div className="w-12 h-12 bg-white border rounded-lg flex items-center justify-center">
                      <Truck className="h-6 w-6 text-gray-600" />
                    </div>
                    
                    <div>
                      <div className="flex items-center space-x-2 mb-1">
                        <span className="font-medium">
                          {option.courier} - {option.service}
                        </span>
                        <Badge variant="outline">
                          <Clock className="h-3 w-3 mr-1" />
                          {option.etd}
                        </Badge>
                      </div>
                      <p className="text-sm text-gray-600">
                        {option.description}
                      </p>
                    </div>
                  </div>
                  
                  <div className="text-right">
                    <div className="font-bold text-lg">
                      {formatPrice(option.cost)}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Navigation */}
      <div className="flex justify-between">
        <Button variant="outline" onClick={onBack}>
          <ArrowLeft className="h-4 w-4 mr-2" />
          Kembali
        </Button>
        
        <Button
          onClick={handleContinue}
          disabled={!selectedShipping}
          size="lg"
        >
          Lanjut ke Pembayaran
        </Button>
      </div>
    </div>
  );
}