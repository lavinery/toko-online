
// src/components/checkout/address-form.tsx
'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';

const addressSchema = z.object({
  label: z.string().min(1, 'Label alamat wajib diisi'),
  name: z.string().min(1, 'Nama penerima wajib diisi'),
  phone: z.string().min(10, 'Nomor telepon minimal 10 digit'),
  address: z.string().min(10, 'Alamat lengkap wajib diisi'),
  province: z.string().min(1, 'Provinsi wajib dipilih'),
  city: z.string().min(1, 'Kota wajib dipilih'),
  subdistrict: z.string().min(1, 'Kecamatan wajib dipilih'),
  postal_code: z.string().min(5, 'Kode pos wajib diisi'),
  is_default: z.boolean().optional(),
});

type AddressFormData = z.infer<typeof addressSchema>;

interface AddressFormProps {
  initialData?: any;
  onSubmit: (data: AddressFormData) => void;
  onCancel: () => void;
}

// Mock data for dropdowns
const provinces = [
  { id: 1, name: 'DKI Jakarta' },
  { id: 2, name: 'Jawa Barat' },
  { id: 3, name: 'Jawa Tengah' },
  { id: 4, name: 'Jawa Timur' },
];

const cities = [
  { id: 1, province_id: 1, name: 'Jakarta Pusat' },
  { id: 2, province_id: 1, name: 'Jakarta Selatan' },
  { id: 3, province_id: 1, name: 'Jakarta Utara' },
  { id: 4, province_id: 2, name: 'Bandung' },
  { id: 5, province_id: 2, name: 'Bekasi' },
];

const subdistricts = [
  { id: 1, city_id: 1, name: 'Gambir' },
  { id: 2, city_id: 1, name: 'Tanah Abang' },
  { id: 3, city_id: 2, name: 'Kebayoran Baru' },
  { id: 4, city_id: 2, name: 'Tebet' },
];

export function AddressForm({ initialData, onSubmit, onCancel }: AddressFormProps) {
  const [selectedProvince, setSelectedProvince] = useState(initialData?.province || '');
  const [selectedCity, setSelectedCity] = useState(initialData?.city || '');

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
    watch,
    setValue,
  } = useForm<AddressFormData>({
    resolver: zodResolver(addressSchema),
    defaultValues: initialData || {
      label: 'Rumah',
      is_default: false,
    },
  });

  const watchedProvince = watch('province');
  const watchedCity = watch('city');

  const filteredCities = cities.filter(city => 
    city.province_id === provinces.find(p => p.name === watchedProvince)?.id
  );

  const filteredSubdistricts = subdistricts.filter(sub => 
    sub.city_id === cities.find(c => c.name === watchedCity)?.id
  );

  const handleFormSubmit = (data: AddressFormData) => {
    onSubmit(data);
  };

  return (
    <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-6">
      {/* Label & Name */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="label">Label Alamat</Label>
          <select
            {...register('label')}
            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
          >
            <option value="Rumah">Rumah</option>
            <option value="Kantor">Kantor</option>
            <option value="Apartemen">Apartemen</option>
            <option value="Lainnya">Lainnya</option>
          </select>
          {errors.label && (
            <p className="mt-1 text-xs text-red-600">{errors.label.message}</p>
          )}
        </div>

        <div>
          <Label htmlFor="name">Nama Penerima</Label>
          <Input
            {...register('name')}
            id="name"
            placeholder="Nama lengkap penerima"
            className="mt-1"
          />
          {errors.name && (
            <p className="mt-1 text-xs text-red-600">{errors.name.message}</p>
          )}
        </div>
      </div>

      {/* Phone */}
      <div>
        <Label htmlFor="phone">Nomor Telepon</Label>
        <Input
          {...register('phone')}
          id="phone"
          type="tel"
          placeholder="08123456789"
          className="mt-1"
        />
        {errors.phone && (
          <p className="mt-1 text-xs text-red-600">{errors.phone.message}</p>
        )}
      </div>

      {/* Address */}
      <div>
        <Label htmlFor="address">Alamat Lengkap</Label>
        <textarea
          {...register('address')}
          id="address"
          rows={3}
          placeholder="Nama jalan, nomor rumah, RT/RW, landmark"
          className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
        {errors.address && (
          <p className="mt-1 text-xs text-red-600">{errors.address.message}</p>
        )}
      </div>

      {/* Location */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="province">Provinsi</Label>
          <select
            {...register('province')}
            onChange={(e) => {
              setValue('province', e.target.value);
              setValue('city', '');
              setValue('subdistrict', '');
            }}
            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
          >
            <option value="">Pilih Provinsi</option>
            {provinces.map((province) => (
              <option key={province.id} value={province.name}>
                {province.name}
              </option>
            ))}
          </select>
          {errors.province && (
            <p className="mt-1 text-xs text-red-600">{errors.province.message}</p>
          )}
        </div>

        <div>
          <Label htmlFor="city">Kota/Kabupaten</Label>
          <select
            {...register('city')}
            onChange={(e) => {
              setValue('city', e.target.value);
              setValue('subdistrict', '');
            }}
            disabled={!watchedProvince}
            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-50"
          >
            <option value="">Pilih Kota</option>
            {filteredCities.map((city) => (
              <option key={city.id} value={city.name}>
                {city.name}
              </option>
            ))}
          </select>
          {errors.city && (
            <p className="mt-1 text-xs text-red-600">{errors.city.message}</p>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="subdistrict">Kecamatan</Label>
          <select
            {...register('subdistrict')}
            disabled={!watchedCity}
            className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-50"
          >
            <option value="">Pilih Kecamatan</option>
            {filteredSubdistricts.map((subdistrict) => (
              <option key={subdistrict.id} value={subdistrict.name}>
                {subdistrict.name}
              </option>
            ))}
          </select>
          {errors.subdistrict && (
            <p className="mt-1 text-xs text-red-600">{errors.subdistrict.message}</p>
          )}
        </div>

        <div>
          <Label htmlFor="postal_code">Kode Pos</Label>
          <Input
            {...register('postal_code')}
            id="postal_code"
            placeholder="12345"
            className="mt-1"
          />
          {errors.postal_code && (
            <p className="mt-1 text-xs text-red-600">{errors.postal_code.message}</p>
          )}
        </div>
      </div>

      {/* Default Address */}
      <div className="flex items-center space-x-2">
        <input
          type="checkbox"
          {...register('is_default')}
          id="is_default"
          className="rounded border-gray-300"
        />
        <Label htmlFor="is_default" className="text-sm">
          Jadikan sebagai alamat utama
        </Label>
      </div>

      {/* Actions */}
      <div className="flex justify-end space-x-4 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>
          Batal
        </Button>
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Menyimpan...' : 'Simpan Alamat'}
        </Button>
      </div>
    </form>
  );
}
