'use client';
// src/components/checkout/checkout-flow.tsx (completion of the file)
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
  notes?: string;
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

  const handleSubmitOrder = async (finalData: CheckoutData) => {
    setIsProcessing(true);
    try {
      // Mock API call for order submission
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      toast.success('Pesanan berhasil dibuat!');
      router.push('/pesanan');
    } catch (error) {
      toast.error('Gagal membuat pesanan. Silakan coba lagi.');
    } finally {
      setIsProcessing(false);
    }
  };

  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-6xl mx-auto">
          <div className="animate-pulse space-y-8">
            <div className="h-16 bg-gray-200 rounded"></div>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <div className="lg:col-span-2 space-y-6">
                <div className="h-64 bg-gray-200 rounded"></div>
              </div>
              <div className="h-96 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-6xl mx-auto">
        {/* Progress Steps */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            {steps.map((step, index) => {
              const Icon = step.icon;
              const isCompleted = isStepCompleted(step.key);
              const isActive = isStepActive(step.key);
              const canProceed = canProceedToStep(step.key);
              
              return (
                <div key={step.key} className="flex items-center">
                  <button
                    onClick={() => canProceed && setCurrentStep(step.key as CheckoutStep)}
                    disabled={!canProceed}
                    className={`flex items-center justify-center w-12 h-12 rounded-full border-2 transition-colors ${
                      isCompleted
                        ? 'bg-green-500 border-green-500 text-white'
                        : isActive
                        ? 'bg-primary-600 border-primary-600 text-white'
                        : canProceed
                        ? 'border-gray-300 text-gray-400 hover:border-primary-600'
                        : 'border-gray-200 text-gray-300 cursor-not-allowed'
                    }`}
                  >
                    {isCompleted ? (
                      <CheckCircle className="h-6 w-6" />
                    ) : (
                      <Icon className="h-6 w-6" />
                    )}
                  </button>
                  
                  {index < steps.length - 1 && (
                    <div className={`w-16 h-0.5 mx-4 ${
                      isCompleted ? 'bg-green-500' : 'bg-gray-200'
                    }`} />
                  )}
                </div>
              );
            })}
          </div>
          
          <div className="flex justify-between mt-2">
            {steps.map((step) => (
              <div key={step.key} className="text-center">
                <p className={`text-sm font-medium ${
                  isStepActive(step.key) 
                    ? 'text-primary-600' 
                    : isStepCompleted(step.key)
                    ? 'text-green-600'
                    : 'text-gray-500'
                }`}>
                  {step.label}
                </p>
              </div>
            ))}
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2">
            <Card>
              <CardContent className="p-6">
                {currentStep === 'address' && (
                  <AddressStep
                    data={checkoutData}
                    onNext={(data) => {
                      updateCheckoutData(data);
                      goToNextStep();
                    }}
                  />
                )}
                
                {currentStep === 'shipping' && (
                  <ShippingStep
                    data={checkoutData}
                    cart={cart!}
                    onNext={(data) => {
                      updateCheckoutData(data);
                      goToNextStep();
                    }}
                    onBack={goToPreviousStep}
                  />
                )}
                
                {currentStep === 'payment' && (
                  <PaymentStep
                    data={checkoutData}
                    cart={cart!}
                    onNext={(data) => {
                      updateCheckoutData(data);
                      goToNextStep();
                    }}
                    onBack={goToPreviousStep}
                  />
                )}
                
                {currentStep === 'review' && (
                  <ReviewStep
                    data={checkoutData}
                    cart={cart!}
                    onBack={goToPreviousStep}
                    onSubmit={handleSubmitOrder}
                    isProcessing={isProcessing}
                  />
                )}
              </CardContent>
            </Card>
          </div>

          {/* Sidebar Summary */}
          <div>
            <CheckoutSummary cart={cart!} data={checkoutData} />
          </div>
        </div>
      </div>
    </div>
  );
}