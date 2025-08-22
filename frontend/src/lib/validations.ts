
// src/lib/validations.ts
import { z } from 'zod';

// Auth validations
export const loginSchema = z.object({
  email: z.string().email('Email tidak valid'),
  password: z.string().min(6, 'Password minimal 6 karakter'),
});

export const registerSchema = z.object({
  name: z.string().min(2, 'Nama minimal 2 karakter'),
  email: z.string().email('Email tidak valid'),
  phone: z.string().min(10, 'Nomor telepon minimal 10 digit').optional(),
  password: z.string().min(6, 'Password minimal 6 karakter'),
  password_confirmation: z.string().min(6, 'Konfirmasi password minimal 6 karakter'),
}).refine((data) => data.password === data.password_confirmation, {
  message: "Password tidak sama",
  path: ["password_confirmation"],
});

// Address validations
export const addressSchema = z.object({
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

// Checkout validations
export const checkoutSchema = z.object({
  address_id: z.number().min(1, 'Alamat pengiriman wajib dipilih'),
  courier: z.string().min(1, 'Kurir wajib dipilih'),
  service: z.string().min(1, 'Layanan kurir wajib dipilih'),
  voucher_code: z.string().optional(),
  notes: z.string().optional(),
  payment_gateway: z.string().min(1, 'Metode pembayaran wajib dipilih'),
});

export type LoginData = z.infer<typeof loginSchema>;
export type RegisterData = z.infer<typeof registerSchema>;
export type AddressData = z.infer<typeof addressSchema>;
export type CheckoutData = z.infer<typeof checkoutSchema>;
export function validateLogin(data: LoginData) {
  return loginSchema.safeParse(data);
}