// src/components/checkout/checkout-flow.tsx
'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { CheckCircle, MapPin, Truck, CreditCard, ShoppingBag } from 'lucide-react';
import { useCart } from '@/hooks/use-cart';
import { useAuth } from '@/hooks/use-auth';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AddressStep } from './address-step';
import { ShippingStep } from './shipping-step';
import { PaymentStep } from './payment-step';
import { ReviewStep } from './review-step';
import { CheckoutSummary } from './checkout-summary';
import { formatPrice } from '@/lib/utils';
import { ROUTES } from '@/lib/constants';
import toast from 'react-hot-toast';

export type CheckoutStep = 'address' | 'shipping' | 'payment' | 'review';

export interface CheckoutData {
  address?: any;
  shipping?: {
    courier: string;
    service: string;
    cost: number;
    etd: string;
  };
  payment?: {
    method: string;
    gateway: string;
  };
  voucher?: {
    code: string;
    discount: number;
  };
}

const steps = [
  { key: 'address', label: 'Alamat Pengiriman', icon: MapPin },
  { key: 'shipping', label: 'Metode Pengiriman', icon: Truck },
  { key: 'payment', label: 'Pembayaran', icon: CreditCard },
  { key: 'review', label: 'Konfirmasi', icon: CheckCircle },
];

export function CheckoutFlow() {
  const [currentStep, setCurrentStep] = useState<CheckoutStep>('address');
  const [checkoutData, setCheckoutData] = useState<CheckoutData>({});
  const [isProcessing, setIsProcessing] = useState(false);
  
  const { cart, isLoading } = useCart();
  const { isAuthenticated, user } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isAuthenticated) {
      toast.error('Silakan login untuk melanjutkan checkout');
      router.push(ROUTES.LOGIN);
      return;
    }

    if (!isLoading && (!cart?.items?.length)) {
      toast.error('Keranjang belanja kosong');
      router.push(ROUTES.CART);
      return;
    }
  }, [isAuthenticated, cart, isLoading, router]);

  const updateCheckoutData = (stepData: Partial<CheckoutData>) => {
    setCheckoutData(prev => ({ ...prev, ...stepData }));
  };

  const goToNextStep = () => {
    const stepIndex = steps.findIndex(step => step.key === currentStep);
    if (stepIndex < steps.length - 1) {
      setCurrentStep(steps[stepIndex + 1].key as CheckoutStep);
    }
  };

  const goToPreviousStep = () => {
    const stepIndex = steps.findIndex(step => step.key === currentStep);
    if (stepIndex > 0) {
      setCurrentStep(steps[stepIndex - 1].key as CheckoutStep);
    }
  };

  const isStepCompleted = (stepKey: string): boolean => {
    switch (stepKey) {
      case 'address':
        return !!checkoutData.address;
      case 'shipping':
        return !!checkoutData.shipping;
      case 'payment':
        return !!checkoutData.payment;
      default:
        return false;
    }
  };

  const isStepActive = (stepKey: string): boolean => {
    return currentStep === stepKey;
  };

  const canProceedToStep = (stepKey: string): boolean => {
    const stepIndex = steps.findIndex(step => step.key === stepKey);
    const currentStepIndex = steps.findIndex(step => step.key === currentStep);
    
    if (stepIndex <= currentStepIndex) return true;
    
    // Check if all previous steps are completed
    for (let i = 0; i < stepIndex; i++) {
      if (!isStepCompleted(steps[i].key)) return false;
    }
    return true;
  };

  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-6xl mx-auto">
          <div className="animate-pulse space-y-8">
            <div className="h-16 bg-gray-200 rounded"></div>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8"></div>