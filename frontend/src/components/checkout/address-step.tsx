
// src/components/checkout/address-step.tsx
'use client';

import { useState } from 'react';
import { MapPin, Plus, Edit, Trash2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AddressForm } from './address-form';
import { CheckoutData } from './checkout-flow';
import { useAuth } from '@/hooks/use-auth';

interface AddressStepProps {
  data: CheckoutData;
  onNext: (data: Partial<CheckoutData>) => void;
}

export function AddressStep({ data, onNext }: AddressStepProps) {
  const [selectedAddress, setSelectedAddress] = useState<any>(data.address || null);
  const [showAddForm, setShowAddForm] = useState(false);
  const [editingAddress, setEditingAddress] = useState<any>(null);
  
  const { user } = useAuth();

  // Mock addresses - replace with actual API call
  const addresses = user?.addresses || [];

  const handleSelectAddress = (address: any) => {
    setSelectedAddress(address);
  };

  const handleContinue = () => {
    if (selectedAddress) {
      onNext({ address: selectedAddress });
    }
  };

  const handleAddAddress = (formData: any) => {
    // Mock API call to add address
    console.log('Adding address:', formData);
    setShowAddForm(false);
    // Refresh addresses
  };

  if (showAddForm || editingAddress) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold">
            {editingAddress ? 'Edit Alamat' : 'Tambah Alamat Baru'}
          </h2>
          <Button
            variant="outline"
            onClick={() => {
              setShowAddForm(false);
              setEditingAddress(null);
            }}
          >
            Kembali
          </Button>
        </div>
        
        <AddressForm
          initialData={editingAddress}
          onSubmit={handleAddAddress}
          onCancel={() => {
            setShowAddForm(false);
            setEditingAddress(null);
          }}
        />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-xl font-semibold">Pilih Alamat Pengiriman</h2>
        <Button onClick={() => setShowAddForm(true)}>
          <Plus className="h-4 w-4 mr-2" />
          Tambah Alamat
        </Button>
      </div>

      {addresses.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <MapPin className="h-12 w-12 text-gray-400 mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              Belum ada alamat tersimpan
            </h3>
            <p className="text-gray-600 text-center mb-6">
              Tambahkan alamat pengiriman untuk melanjutkan checkout
            </p>
            <Button onClick={() => setShowAddForm(true)}>
              <Plus className="h-4 w-4 mr-2" />
              Tambah Alamat Pertama
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-4">
          {addresses.map((address) => (
            <Card
              key={address.id}
              className={`cursor-pointer border-2 transition-colors ${
                selectedAddress?.id === address.id
                  ? 'border-primary-500 bg-primary-50'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
              onClick={() => handleSelectAddress(address)}
            >
              <CardContent className="p-4">
                <div className="flex items-start justify-between">
                  <div className="flex-1">
                    <div className="flex items-center space-x-2 mb-2">
                      <Badge variant={address.is_default ? 'default' : 'secondary'}>
                        {address.label}
                      </Badge>
                      {address.is_default && (
                        <Badge variant="success">Utama</Badge>
                      )}
                    </div>
                    
                    <h4 className="font-medium text-gray-900 mb-1">
                      {address.name}
                    </h4>
                    <p className="text-sm text-gray-600 mb-1">
                      {address.phone}
                    </p>
                    <p className="text-sm text-gray-600">
                      {address.full_address}
                    </p>
                  </div>
                  
                  <div className="flex space-x-2">
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={(e) => {
                        e.stopPropagation();
                        setEditingAddress(address);
                      }}
                    >
                      <Edit className="h-4 w-4" />
                    </Button>
                    {!address.is_default && (
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          // Handle delete
                        }}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {selectedAddress && (
        <div className="flex justify-end">
          <Button onClick={handleContinue} size="lg">
            Lanjut ke Pengiriman
          </Button>
        </div>
      )}
    </div>
  );
}
