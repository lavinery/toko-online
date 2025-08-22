// src/components/product/product-detail.tsx
'use client';

import { useState } from 'react';
import Image from 'next/image';
import { 
  Heart, 
  Share2, 
  ShoppingCart, 
  Star, 
  Plus, 
  Minus,
  Truck,
  Shield,
  RotateCcw
} from 'lucide-react';
import { Product } from '@/types/product';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { useCart } from '@/hooks/use-cart';
import { useAuth } from '@/hooks/use-auth';
import { formatPrice } from '@/lib/utils';
import { ROUTES } from '@/lib/constants';
import toast from 'react-hot-toast';

interface ProductDetailProps {
  product: Product;
}

export function ProductDetail({ product }: ProductDetailProps) {
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [selectedVariant, setSelectedVariant] = useState(
    product.variants?.[0]?.id || null
  );
  const [quantity, setQuantity] = useState(1);
  const [isAddingToCart, setIsAddingToCart] = useState(false);
  const [isWishlisted, setIsWishlisted] = useState(false);
  
  const { addItem } = useCart();
  const { isAuthenticated } = useAuth();

  const selectedVariantData = product.variants?.find(v => v.id === selectedVariant);
  const finalPrice = selectedVariantData 
    ? selectedVariantData.final_price 
    : product.price;
  const availableStock = selectedVariantData 
    ? selectedVariantData.stock 
    : product.available_stock;

  const discountPercentage = product.compare_price 
    ? Math.round(((product.compare_price - finalPrice) / product.compare_price) * 100)
    : 0;

  const handleAddToCart = async () => {
    if (!availableStock) {
      toast.error('Produk sedang tidak tersedia');
      return;
    }

    if (quantity > availableStock) {
      toast.error(`Stok hanya tersedia ${availableStock} item`);
      return;
    }

    try {
      setIsAddingToCart(true);
      await addItem({
        product_id: product.id,
        product_variant_id: selectedVariant,
        quantity,
      });
    } catch (error) {
      // Error handled by store
    } finally {
      setIsAddingToCart(false);
    }
  };

  const handleWishlist = () => {
    if (!isAuthenticated) {
      window.location.href = ROUTES.LOGIN;
      return;
    }
    
    setIsWishlisted(!isWishlisted);
    toast.success(isWishlisted ? 'Dihapus dari wishlist' : 'Ditambahkan ke wishlist');
  };

  const handleShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: product.name,
          text: product.short_description,
          url: window.location.href,
        });
      } catch (error) {
        // User cancelled share
      }
    } else {
      // Fallback: copy to clipboard
      navigator.clipboard.writeText(window.location.href);
      toast.success('Link produk disalin ke clipboard');
    }
  };

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
      {/* Product Images */}
      <div className="space-y-4">
        {/* Main Image */}
        <div className="aspect-square bg-white rounded-lg border overflow-hidden">
          <Image
            src={product.images[selectedImageIndex]?.url || '/images/placeholder.jpg'}
            alt={product.images[selectedImageIndex]?.alt_text || product.name}
            width={600}
            height={600}
            className="w-full h-full object-cover"
            priority
          />
        </div>

        {/* Thumbnail Images */}
        {product.images.length > 1 && (
          <div className="grid grid-cols-4 gap-2">
            {product.images.map((image, index) => (
              <button
                key={image.id}
                onClick={() => setSelectedImageIndex(index)}
                className={`aspect-square bg-white rounded-lg border-2 overflow-hidden transition-colors ${
                  selectedImageIndex === index
                    ? 'border-primary-500'
                    : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <Image
                  src={image.url}
                  alt={image.alt_text || product.name}
                  width={150}
                  height={150}
                  className="w-full h-full object-cover"
                />
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Product Info */}
      <div className="space-y-6">
        {/* Header */}
        <div>
          {product.categories.length > 0 && (
            <p className="text-sm text-primary-600 font-medium mb-2">
              {product.categories.map(cat => cat.name).join(' • ')}
            </p>
          )}
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            {product.name}
          </h1>
          
          {/* Rating & Reviews */}
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-1">
              {[...Array(5)].map((_, i) => (
                <Star
                  key={i}
                  className={`h-4 w-4 ${
                    i < 4 ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'
                  }`}
                />
              ))}
              <span className="text-sm text-gray-600 ml-2">(4.0)</span>
            </div>
            <span className="text-sm text-gray-400">•</span>
            <span className="text-sm text-gray-600">247 ulasan</span>
            <span className="text-sm text-gray-400">•</span>
            <span className="text-sm text-gray-600">1.2k terjual</span>
          </div>
        </div>

        {/* Price */}
        <div className="space-y-2">
          <div className="flex items-center space-x-3">
            <span className="text-3xl font-bold text-gray-900">
              {formatPrice(finalPrice)}
            </span>
            {product.compare_price && (
              <>
                <span className="text-lg text-gray-500 line-through">
                  {formatPrice(product.compare_price)}
                </span>
                <Badge variant="destructive" className="text-sm">
                  -{discountPercentage}%
                </Badge>
              </>
            )}
          </div>
          <p className="text-sm text-gray-600">
            *Harga sudah termasuk PPN
          </p>
        </div>

        {/* Variants */}
        {product.variants && product.variants.length > 0 && (
          <div className="space-y-3">
            <h3 className="font-medium text-gray-900">Pilih Varian:</h3>
            <div className="grid grid-cols-3 gap-2">
              {product.variants.map((variant) => (
                <button
                  key={variant.id}
                  onClick={() => setSelectedVariant(variant.id)}
                  disabled={!variant.in_stock}
                  className={`p-3 rounded-lg border text-sm font-medium transition-colors ${
                    selectedVariant === variant.id
                      ? 'border-primary-500 bg-primary-50 text-primary-700'
                      : variant.in_stock
                      ? 'border-gray-300 hover:border-gray-400'
                      : 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed'
                  }`}
                >
                  <div className="text-center">
                    <div>{variant.name}</div>
                    {variant.price_adjustment !== 0 && (
                      <div className="text-xs mt-1">
                        {variant.price_adjustment > 0 ? '+' : ''}
                        {formatPrice(variant.price_adjustment)}
                      </div>
                    )}
                  </div>
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Quantity */}
        <div className="space-y-3">
          <h3 className="font-medium text-gray-900">Jumlah:</h3>
          <div className="flex items-center space-x-4">
            <div className="flex items-center border border-gray-300 rounded-lg">
              <button
                onClick={() => setQuantity(Math.max(1, quantity - 1))}
                disabled={quantity <= 1}
                className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Minus className="h-4 w-4" />
              </button>
              <span className="px-4 py-2 font-medium">{quantity}</span>
              <button
                onClick={() => setQuantity(Math.min(availableStock, quantity + 1))}
                disabled={quantity >= availableStock}
                className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Plus className="h-4 w-4" />
              </button>
            </div>
            <span className="text-sm text-gray-600">
              Stok tersedia: {availableStock}
            </span>
          </div>
        </div>

        {/* Actions */}
        <div className="space-y-4">
          <div className="flex space-x-4">
            <Button
              onClick={handleAddToCart}
              disabled={!availableStock || isAddingToCart}
              className="flex-1"
              size="lg"
            >
              {isAddingToCart ? (
                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <ShoppingCart className="h-5 w-5 mr-2" />
                  {availableStock ? 'Tambah ke Keranjang' : 'Stok Habis'}
                </>
              )}
            </Button>
            
            <Button
              variant="outline"
              size="lg"
              onClick={handleWishlist}
              className="px-4"
            >
              <Heart className={`h-5 w-5 ${isWishlisted ? 'fill-red-500 text-red-500' : ''}`} />
            </Button>
            
            <Button
              variant="outline"
              size="lg"
              onClick={handleShare}
              className="px-4"
            >
              <Share2 className="h-5 w-5" />
            </Button>
          </div>

          <Button
            variant="outline"
            size="lg"
            className="w-full"
            onClick={() => {
              handleAddToCart().then(() => {
                window.location.href = ROUTES.CART;
              });
            }}
            disabled={!availableStock}
          >
            Beli Sekarang
          </Button>
        </div>

        {/* Product Features */}
        <div className="grid grid-cols-3 gap-4 pt-4 border-t">
          <div className="text-center">
            <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <Truck className="h-5 w-5 text-blue-600" />
            </div>
            <p className="text-xs text-gray-600">Gratis Ongkir</p>
          </div>
          <div className="text-center">
            <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <Shield className="h-5 w-5 text-green-600" />
            </div>
            <p className="text-xs text-gray-600">Garansi Resmi</p>
          </div>
          <div className="text-center">
            <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <RotateCcw className="h-5 w-5 text-orange-600" />
            </div>
            <p className="text-xs text-gray-600">30 Hari Retur</p>
          </div>
        </div>

        {/* Product Info */}
        <Card>
          <CardContent className="p-4">
            <h3 className="font-medium text-gray-900 mb-3">Informasi Produk</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-600">SKU:</span>
                <span className="font-medium">{selectedVariantData?.sku || product.sku}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Berat:</span>
                <span className="font-medium">{product.weight}g</span>
              </div>
              {product.dimensions && (
                <div className="flex justify-between">
                  <span className="text-gray-600">Dimensi:</span>
                  <span className="font-medium">{product.dimensions} cm</span>
                </div>
              )}
              <div className="flex justify-between">
                <span className="text-gray-600">Kondisi:</span>
                <span className="font-medium">Baru</span>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Description */}
        <Card>
          <CardContent className="p-4">
            <h3 className="font-medium text-gray-900 mb-3">Deskripsi Produk</h3>
            <div 
              className="prose prose-sm max-w-none text-gray-700"
              dangerouslySetInnerHTML={{ __html: product.description || product.short_description }}
            />
          </CardContent>
        </Card>

        {/* Specifications */}
        {product.specifications && Object.keys(product.specifications).length > 0 && (
          <Card>
            <CardContent className="p-4">
              <h3 className="font-medium text-gray-900 mb-3">Spesifikasi</h3>
              <div className="space-y-2">
                {Object.entries(product.specifications).map(([key, value]) => (
                  <div key={key} className="flex justify-between text-sm">
                    <span className="text-gray-600 capitalize">{key}:</span>
                    <span className="font-medium">{value}</span>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
 