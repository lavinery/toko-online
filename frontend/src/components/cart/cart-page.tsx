// src/components/cart/cart-page.tsx
'use client';

import { useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { 
  Plus, 
  Minus, 
  Trash2, 
  ArrowLeft, 
  ShoppingBag,
  Heart,
  Gift
} from 'lucide-react';
import { useCart } from '@/hooks/use-cart';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Loading } from '@/components/ui/loading';
import { formatPrice } from '@/lib/utils';
import { ROUTES } from '@/lib/constants';

export function CartPage() {
  const { cart, isLoading, updateItem, removeItem } = useCart();
  const { isAuthenticated } = useAuth();
  const [voucherCode, setVoucherCode] = useState('');
  const [isApplyingVoucher, setIsApplyingVoucher] = useState(false);

  if (isLoading) {
    return <Loading className="min-h-screen" />;
  }

  if (!cart || cart.items.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50">
        <div className="container mx-auto px-4 py-16">
          <div className="max-w-md mx-auto text-center">
            <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <ShoppingBag className="h-12 w-12 text-gray-400" />
            </div>
            <h1 className="text-2xl font-bold text-gray-900 mb-4">
              Keranjang Kosong
            </h1>
            <p className="text-gray-600 mb-8">
              Belum ada produk dalam keranjang belanja Anda
            </p>
            <div className="space-y-4">
              <Button asChild size="lg">
                <Link href={ROUTES.PRODUCTS}>
                  Mulai Belanja
                </Link>
              </Button>
              <div>
                <Button variant="outline" asChild>
                  <Link href={ROUTES.HOME}>
                    <ArrowLeft className="h-4 w-4 mr-2" />
                    Kembali ke Beranda
                  </Link>
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  const handleQuantityChange = async (itemId: number, newQuantity: number) => {
    if (newQuantity < 1) return;
    try {
      await updateItem(itemId, newQuantity);
    } catch (error) {
      // Error handled by store
    }
  };

  const handleRemoveItem = async (itemId: number) => {
    try {
      await removeItem(itemId);
    } catch (error) {
      // Error handled by store
    }
  };

  const handleApplyVoucher = async () => {
    if (!voucherCode.trim()) return;
    
    setIsApplyingVoucher(true);
    try {
      // Mock voucher application
      await new Promise(resolve => setTimeout(resolve, 1000));
      console.log('Applying voucher:', voucherCode);
    } catch (error) {
      console.error('Failed to apply voucher');
    } finally {
      setIsApplyingVoucher(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div className="flex items-center space-x-4">
            <Button variant="ghost" size="icon" asChild>
              <Link href={ROUTES.PRODUCTS}>
                <ArrowLeft className="h-5 w-5" />
              </Link>
            </Button>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">
                Keranjang Belanja
              </h1>
              <p className="text-gray-600">
                {cart.total_quantity} item dalam keranjang
              </p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-2 space-y-4">
            {cart.items.map((item) => (
              <Card key={item.id}>
                <CardContent className="p-6">
                  <div className="flex items-start space-x-4">
                    {/* Product Image */}
                    <div className="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                      <Image
                        src={item.product.primary_image?.url || '/images/placeholder.jpg'}
                        alt={item.product.name}
                        width={80}
                        height={80}
                        className="w-full h-full object-cover"
                      />
                    </div>

                    {/* Product Info */}
                    <div className="flex-1 min-w-0">
                      <Link
                        href={`${ROUTES.PRODUCTS}/${item.product.slug}`}
                        className="font-medium text-gray-900 hover:text-primary-600 line-clamp-2"
                      >
                        {item.product.name}
                      </Link>
                      
                      {item.variant && (
                        <p className="text-sm text-gray-600 mt-1">
                          Varian: {item.variant.name}
                        </p>
                      )}
                      
                      <div className="flex items-center justify-between mt-3">
                        <div className="flex items-center space-x-1">
                          <span className="font-bold text-lg">
                            {formatPrice(item.price)}
                          </span>
                          {item.product.compare_price && (
                            <span className="text-sm text-gray-500 line-through">
                              {formatPrice(item.product.compare_price)}
                            </span>
                          )}
                        </div>
                        
                        <div className="text-right">
                          <div className="font-bold">
                            {formatPrice(item.subtotal)}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Quantity Controls */}
                  <div className="flex items-center justify-between mt-4 pt-4 border-t">
                    <div className="flex items-center space-x-3">
                      {/* Quantity Selector */}
                      <div className="flex items-center border border-gray-300 rounded-lg">
                        <button
                          onClick={() => handleQuantityChange(item.id, item.quantity - 1)}
                          disabled={item.quantity <= 1}
                          className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          <Minus className="h-4 w-4" />
                        </button>
                        <span className="px-4 py-2 font-medium min-w-[60px] text-center">
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => handleQuantityChange(item.id, item.quantity + 1)}
                          className="p-2 hover:bg-gray-100"
                        >
                          <Plus className="h-4 w-4" />
                        </button>
                      </div>

                      {/* Stock Info */}
                      <span className="text-sm text-gray-500">
                        Stok: {item.product.available_stock}
                      </span>
                    </div>

                    <div className="flex items-center space-x-2">
                      {/* Move to Wishlist */}
                      {isAuthenticated && (
                        <Button variant="ghost" size="sm">
                          <Heart className="h-4 w-4 mr-1" />
                          Simpan
                        </Button>
                      )}

                      {/* Remove Item */}
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleRemoveItem(item.id)}
                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                      >
                        <Trash2 className="h-4 w-4 mr-1" />
                        Hapus
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}

            {/* Continue Shopping */}
            <Card>
              <CardContent className="p-6 text-center">
                <h3 className="font-medium text-gray-900 mb-2">
                  Masih ingin belanja lagi?
                </h3>
                <p className="text-gray-600 mb-4">
                  Jelajahi produk lainnya dan dapatkan penawaran menarik
                </p>
                <Button variant="outline" asChild>
                  <Link href={ROUTES.PRODUCTS}>
                    Lanjut Belanja
                  </Link>
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Order Summary */}
          <div className="space-y-6">
            {/* Voucher */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <Gift className="h-5 w-5" />
                  <span>Kode Promo</span>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex space-x-2">
                  <Input
                    placeholder="Masukkan kode promo"
                    value={voucherCode}
                    onChange={(e) => setVoucherCode(e.target.value)}
                    disabled={isApplyingVoucher}
                  />
                  <Button
                    onClick={handleApplyVoucher}
                    disabled={!voucherCode.trim() || isApplyingVoucher}
                    variant="outline"
                  >
                    {isApplyingVoucher ? 'Memproses...' : 'Gunakan'}
                  </Button>
                </div>
              </CardContent>
            </Card>

            {/* Summary */}
            <Card>
              <CardHeader>
                <CardTitle>Ringkasan Belanja</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-gray-600">
                      Subtotal ({cart.total_quantity} item)
                    </span>
                    <span className="font-medium">
                      {formatPrice(cart.subtotal)}
                    </span>
                  </div>
                  
                  <div className="flex justify-between text-sm text-gray-600">
                    <span>Estimasi Ongkos Kirim</span>
                    <span>Dihitung di checkout</span>
                  </div>
                </div>

                <hr />

                <div className="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span>{formatPrice(cart.subtotal)}</span>
                </div>

                <Button 
                  className="w-full" 
                  size="lg"
                  asChild
                >
                  <Link href={ROUTES.CHECKOUT}>
                    Lanjut ke Checkout
                  </Link>
                </Button>

                <div className="text-center">
                  <p className="text-xs text-gray-500">
                    Dengan melanjutkan, Anda menyetujui{' '}
                    <Link href="/terms" className="text-primary-600 hover:underline">
                      Syarat & Ketentuan
                    </Link>
                  </p>
                </div>
              </CardContent>
            </Card>

            {/* Security Info */}
            <Card className="bg-blue-50 border-blue-200">
              <CardContent className="p-4">
                <div className="flex items-start space-x-3">
                  <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-blue-600 text-sm">🔒</span>
                  </div>
                  <div>
                    <h4 className="font-medium text-blue-900 mb-1">
                      Pembayaran Aman
                    </h4>
                    <p className="text-sm text-blue-800">
                      Transaksi Anda dilindungi dengan enkripsi SSL dan sistem keamanan berlapis
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}
