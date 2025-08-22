// src/components/product/product-card.tsx
'use client';

import Image from 'next/image';
import Link from 'next/link';
import { useState } from 'react';
import { Heart, ShoppingCart, Star } from 'lucide-react';
import { Product } from '@/types/product';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { formatPrice } from '@/lib/utils';
import { useCart } from '@/hooks/use-cart';
import { useAuth } from '@/hooks/use-auth';
import { ROUTES } from '@/lib/constants';

interface ProductCardProps {
  product: Product;
  className?: string;
}

export function ProductCard({ product, className }: ProductCardProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [isWishlisted, setIsWishlisted] = useState(false);
  const { addItem } = useCart();
  const { isAuthenticated } = useAuth();

  const handleAddToCart = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (!product.available_stock) return;
    
    try {
      setIsLoading(true);
      await addItem({
        product_id: product.id,
        quantity: 1,
      });
    } catch (error) {
      // Error handled by store
    } finally {
      setIsLoading(false);
    }
  };

  const handleWishlist = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (!isAuthenticated) {
      window.location.href = ROUTES.LOGIN;
      return;
    }
    
    setIsWishlisted(!isWishlisted);
    // TODO: Implement wishlist API
  };

  const discountPercentage = product.compare_price 
    ? Math.round(((product.compare_price - product.price) / product.compare_price) * 100)
    : 0;

  return (
    <Card className={`group hover:shadow-lg transition-shadow duration-200 ${className}`}>
      <Link href={`${ROUTES.PRODUCTS}/${product.slug}`}>
        <div className="relative aspect-square overflow-hidden rounded-t-lg">
          <Image
            src={product.primary_image?.url || '/images/placeholder.jpg'}
            alt={product.name}
            fill
            className="object-cover transition-transform duration-300 group-hover:scale-105"
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
          />
          
          {/* Badges */}
          <div className="absolute top-2 left-2 space-y-1">
            {product.is_featured && (
              <Badge variant="default" className="text-xs">
                Unggulan
              </Badge>
            )}
            {discountPercentage > 0 && (
              <Badge variant="destructive" className="text-xs">
                -{discountPercentage}%
              </Badge>
            )}
            {!product.available_stock && (
              <Badge variant="secondary" className="text-xs">
                Stok Habis
              </Badge>
            )}
          </div>

          {/* Wishlist Button */}
          <button
            onClick={handleWishlist}
            className="absolute top-2 right-2 p-2 rounded-full bg-white/80 hover:bg-white transition-colors"
          >
            <Heart 
              className={`h-4 w-4 ${isWishlisted ? 'fill-red-500 text-red-500' : 'text-gray-600'}`} 
            />
          </button>

          {/* Quick Add to Cart */}
          <div className="absolute bottom-2 left-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <Button
              onClick={handleAddToCart}
              disabled={!product.available_stock || isLoading}
              className="w-full"
              size="sm"
            >
              {isLoading ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <ShoppingCart className="h-4 w-4 mr-2" />
                  {product.available_stock ? 'Tambah ke Keranjang' : 'Stok Habis'}
                </>
              )}
            </Button>
          </div>
        </div>

        <CardContent className="p-4">
          <div className="space-y-2">
            {/* Category */}
            {product.categories.length > 0 && (
              <p className="text-xs text-gray-500 uppercase tracking-wide">
                {product.categories[0].name}
              </p>
            )}

            {/* Product Name */}
            <h3 className="font-medium text-gray-900 line-clamp-2 group-hover:text-primary-600 transition-colors">
              {product.name}
            </h3>

            {/* Rating & Reviews */}
            <div className="flex items-center space-x-1">
              <div className="flex items-center space-x-1">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`h-3 w-3 ${
                      i < 4 ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'
                    }`}
                  />
                ))}
              </div>
              <span className="text-xs text-gray-500">(4.0)</span>
              <span className="text-xs text-gray-400">•</span>
              <span className="text-xs text-gray-500">128 terjual</span>
            </div>

            {/* Price */}
            <div className="space-y-1">
              <div className="flex items-center space-x-2">
                <span className="text-lg font-bold text-gray-900">
                  {formatPrice(product.price)}
                </span>
                {product.compare_price && (
                  <span className="text-sm text-gray-500 line-through">
                    {formatPrice(product.compare_price)}
                  </span>
                )}
              </div>
              
              {/* Stock Info */}
              <div className="flex items-center justify-between">
                <span className="text-xs text-gray-500">
                  Stok: {product.available_stock}
                </span>
                {product.weight && (
                  <span className="text-xs text-gray-400">
                    {product.weight}g
                  </span>
                )}
              </div>
            </div>
          </div>
        </CardContent>
      </Link>
    </Card>
  );
}
