
// src/types/order.ts
export interface Order {
  id: number;
  code: string;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  subtotal: number;
  shipping_cost: number;
  tax_amount: number;
  discount_amount: number;
  total: number;
  payment_status: 'pending' | 'paid' | 'failed' | 'expired' | 'refunded';
  shipping_status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  payment_gateway?: string;
  shipping_address: string;
  notes?: string;
  paid_at?: string;
  shipped_at?: string;
  delivered_at?: string;
  created_at: string;
  items?: OrderItem[];
  vouchers?: OrderVoucher[];
  shipment?: Shipment;
  can_be_cancelled: boolean;
}

export interface OrderItem {
  id: number;
  product_id: number;
  product_variant_id?: number;
  product_name: string;
  variant_name?: string;
  product_sku: string;
  price: number;
  quantity: number;
  subtotal: number;
}

export interface OrderVoucher {
  id: number;
  voucher_code: string;
  discount_amount: number;
}

export interface Shipment {
  id: number;
  courier: string;
  service: string;
  cost: number;
  weight: number;
  tracking_number?: string;
  status: 'pending' | 'picked_up' | 'in_transit' | 'delivered';
  shipped_at?: string;
  estimated_delivery?: string;
  delivered_at?: string;
}

export interface CheckoutData {
  address_id: number;
  courier: string;
  service: string;
  voucher_code?: string;
  notes?: string;
  payment_gateway: string;
  idempotency_key: string;
}