// src/components/product/product-card.tsx - Fixed version
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
import { ROUTES } from '@/lib/constants';

interface ProductCardProps {
  product: Product;
  className?: string;
}

export function ProductCard({ product, className }: ProductCardProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [isWishlisted, setIsWishlisted] = useState(false);

  const handleAddToCart = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (!product.available_stock) return;
    
    setIsLoading(true);
    // Simulate API call
    setTimeout(() => {
      setIsLoading(false);
      alert('Produk ditambahkan ke keranjang!');
    }, 1000);
  };

  const handleWishlist = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsWishlisted(!isWishlisted);
  };

  const discountPercentage = product.compare_price 
    ? Math.round(((product.compare_price - product.price) / product.compare_price) * 100)
    : 0;

  return (
    <Card className={`group hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 ${className}`}>
      <Link href={`${ROUTES.PRODUCTS}/${product.slug}`}>
        <div className="relative aspect-square overflow-hidden rounded-t-lg bg-gray-100">
          {/* Placeholder image dengan gradien */}
          <div className="w-full h-full bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 flex items-center justify-center">
            <div className="text-6xl opacity-50">
              {product.name.includes('iPhone') && '📱'}
              {product.name.includes('Samsung') && '📱'}
              {product.name.includes('MacBook') && '💻'}
              {product.name.includes('AirPods') && '🎧'}
              {!['iPhone', 'Samsung', 'MacBook', 'AirPods'].some(keyword => product.name.includes(keyword)) && '📦'}
            </div>
          </div>
          
          {/* Badges */}
          <div className="absolute top-3 left-3 space-y-1">
            {product.is_featured && (
              <Badge className="text-xs bg-orange-500 hover:bg-orange-600">
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
            className="absolute top-3 right-3 p-2 rounded-full bg-white/90 hover:bg-white transition-colors shadow-sm"
          >
            <Heart 
              className={`h-4 w-4 ${isWishlisted ? 'fill-red-500 text-red-500' : 'text-gray-600'}`} 
            />
          </button>

          {/* Quick Add to Cart - Only show on hover */}
          <div className="absolute bottom-3 left-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <Button
              onClick={handleAddToCart}
              disabled={!product.available_stock || isLoading}
              className="w-full shadow-lg"
              size="sm"
            >
              {isLoading ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <ShoppingCart className="h-4 w-4 mr-2" />
                  {product.available_stock ? 'Tambah' : 'Habis'}
                </>
              )}
            </Button>
          </div>
        </div>

        <CardContent className="p-4">
          <div className="space-y-3">
            {/* Category */}
            {product.categories.length > 0 && (
              <p className="text-xs text-blue-600 uppercase tracking-wide font-medium">
                {product.categories[0].name}
              </p>
            )}

            {/* Product Name */}
            <h3 className="font-semibold text-gray-900 line-clamp-2 group-hover:text-blue-600 transition-colors leading-snug">
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
              <span className="text-xs text-gray-500">(4.5)</span>
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
                    {product.weight < 1000 ? `${product.weight}g` : `${(product.weight/1000).toFixed(1)}kg`}
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