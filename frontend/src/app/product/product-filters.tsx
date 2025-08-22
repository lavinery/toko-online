
// src/components/product/product-filters.tsx
'use client';

import { useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { Filter, X } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { formatPrice } from '@/lib/utils';

export function ProductFilters() {
  const router = useRouter();
  const searchParams = useSearchParams();
  
  const [minPrice, setMinPrice] = useState(searchParams.get('min_price') || '');
  const [maxPrice, setMaxPrice] = useState(searchParams.get('max_price') || '');
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '');

  // Mock categories - replace with actual API call
  const categories = [
    { id: 1, name: 'Elektronik', slug: 'elektronik', count: 245 },
    { id: 2, name: 'Fashion', slug: 'fashion', count: 189 },
    { id: 3, name: 'Rumah & Taman', slug: 'rumah-taman', count: 156 },
    { id: 4, name: 'Olahraga', slug: 'olahraga', count: 98 },
    { id: 5, name: 'Kecantikan', slug: 'kecantikan', count: 167 },
  ];

  const priceRanges = [
    { label: 'Di bawah Rp 100.000', min: 0, max: 100000 },
    { label: 'Rp 100.000 - Rp 500.000', min: 100000, max: 500000 },
    { label: 'Rp 500.000 - Rp 1.000.000', min: 500000, max: 1000000 },
    { label: 'Di atas Rp 1.000.000', min: 1000000, max: null },
  ];

  const sortOptions = [
    { value: 'created_at', label: 'Terbaru' },
    { value: 'popular', label: 'Terpopuler' },
    { value: 'price_asc', label: 'Harga Terendah' },
    { value: 'price_desc', label: 'Harga Tertinggi' },
    { value: 'name', label: 'Nama A-Z' },
  ];

  const updateFilters = (newFilters: Record<string, string | null>) => {
    const params = new URLSearchParams(searchParams.toString());
    
    Object.entries(newFilters).forEach(([key, value]) => {
      if (value) {
        params.set(key, value);
      } else {
        params.delete(key);
      }
    });

    // Reset to first page when filters change
    params.delete('page');
    
    router.push(`/produk?${params.toString()}`);
  };

  const handlePriceFilter = () => {
    updateFilters({
      min_price: minPrice || null,
      max_price: maxPrice || null,
    });
  };

  const clearFilters = () => {
    setMinPrice('');
    setMaxPrice('');
    setSelectedCategory('');
    router.push('/produk');
  };

  const activeFiltersCount = [
    searchParams.get('category'),
    searchParams.get('min_price'),
    searchParams.get('max_price'),
  ].filter(Boolean).length;

  return (
    <div className="space-y-6">
      {/* Active Filters */}
      {activeFiltersCount > 0 && (
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="text-base">Filter Aktif</CardTitle>
              <Button variant="ghost" size="sm" onClick={clearFilters}>
                <X className="h-4 w-4 mr-1" />
                Hapus Semua
              </Button>
            </div>
          </CardHeader>
          <CardContent className="space-y-2">
            {searchParams.get('category') && (
              <Badge variant="secondary" className="mr-2">
                {categories.find(c => c.slug === searchParams.get('category'))?.name}
                <button
                  onClick={() => updateFilters({ category: null })}
                  className="ml-1 hover:text-red-600"
                >
                  <X className="h-3 w-3" />
                </button>
              </Badge>
            )}
            
            {(searchParams.get('min_price') || searchParams.get('max_price')) && (
              <Badge variant="secondary" className="mr-2">
                {searchParams.get('min_price') && formatPrice(parseInt(searchParams.get('min_price')!))}
                {searchParams.get('min_price') && searchParams.get('max_price') && ' - '}
                {searchParams.get('max_price') && formatPrice(parseInt(searchParams.get('max_price')!))}
                <button
                  onClick={() => updateFilters({ min_price: null, max_price: null })}
                  className="ml-1 hover:text-red-600"
                >
                  <X className="h-3 w-3" />
                </button>
              </Badge>
            )}
          </CardContent>
        </Card>
      )}

      {/* Sort */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Urutkan</CardTitle>
        </CardHeader>
        <CardContent>
          <select
            value={searchParams.get('sort') || ''}
            onChange={(e) => updateFilters({ sort: e.target.value || null })}
            className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
          >
            <option value="">Pilih Urutan</option>
            {sortOptions.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </CardContent>
      </Card>

      {/* Categories */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Kategori</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            {categories.map((category) => (
              <button
                key={category.id}
                onClick={() => updateFilters({ 
                  category: selectedCategory === category.slug ? null : category.slug 
                })}
                className={`w-full text-left px-3 py-2 rounded-md text-sm transition-colors ${
                  selectedCategory === category.slug
                    ? 'bg-primary-50 text-primary-700 border border-primary-200'
                    : 'hover:bg-gray-100'
                }`}
              >
                <div className="flex items-center justify-between">
                  <span>{category.name}</span>
                  <span className="text-gray-500 text-xs">({category.count})</span>
                </div>
              </button>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Price Range */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Harga</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {/* Quick Price Ranges */}
          <div className="space-y-2">
            {priceRanges.map((range, index) => (
              <button
                key={index}
                onClick={() => {
                  setMinPrice(range.min.toString());
                  setMaxPrice(range.max?.toString() || '');
                  updateFilters({
                    min_price: range.min.toString(),
                    max_price: range.max?.toString() || null,
                  });
                }}
                className="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-gray-100 transition-colors"
              >
                {range.label}
              </button>
            ))}
          </div>

          <hr />

          {/* Custom Price Range */}
          <div className="space-y-3">
            <Label className="text-sm font-medium">Rentang Harga Kustom</Label>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label htmlFor="min-price" className="text-xs text-gray-500">Min</Label>
                <Input
                  id="min-price"
                  type="number"
                  placeholder="0"
                  value={minPrice}
                  onChange={(e) => setMinPrice(e.target.value)}
                  className="text-sm"
                />
              </div>
              <div>
                <Label htmlFor="max-price" className="text-xs text-gray-500">Max</Label>
                <Input
                  id="max-price"
                  type="number"
                  placeholder="Unlimited"
                  value={maxPrice}
                  onChange={(e) => setMaxPrice(e.target.value)}
                  className="text-sm"
                />
              </div>
            </div>
            <Button
              onClick={handlePriceFilter}
              size="sm"
              className="w-full"
              variant="outline"
            >
              Terapkan Harga
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Rating Filter */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Rating</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            {[5, 4, 3, 2, 1].map((rating) => (
              <button
                key={rating}
                className="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-gray-100 transition-colors"
              >
                <div className="flex items-center space-x-2">
                  <div className="flex items-center">
                    {[...Array(5)].map((_, i) => (
                      <span
                        key={i}
                        className={`text-sm ${
                          i < rating ? 'text-yellow-400' : 'text-gray-300'
                        }`}
                      >
                        ★
                      </span>
                    ))}
                  </div>
                  <span className="text-gray-600">& keatas</span>
                </div>
              </button>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}