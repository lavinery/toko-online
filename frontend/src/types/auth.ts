// src/types/auth.ts
export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role: 'admin' | 'customer';
  is_active: boolean;
  email_verified_at?: string;
  created_at: string;
  addresses?: Address[];
  default_address?: Address;
}

export interface Address {
  id: number;
  label: string;
  name: string;
  phone: string;
  address: string;
  province: string;
  city: string;
  subdistrict: string;
  postal_code: string;
  province_id?: number;
  city_id?: number;
  subdistrict_id?: number;
  is_default: boolean;
  full_address: string;
}

export interface AuthResponse {
  message: string;
  user: User;
  access_token: string;
  token_type: string;
  expires_in: number;
}

export interface LoginData {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
}