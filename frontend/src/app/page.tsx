// src/app/page.tsx - Fixed version
import { Suspense } from 'react';
import Link from 'next/link';
import { ArrowRight, Star, Truck, Shield, Headphones } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Loading } from '@/components/ui/loading';
import { FeaturedProducts } from '@/components/product/featured-products';
import { CategoryGrid } from '@/components/product/category-grid';
import { ROUTES } from '@/lib/constants';

export default function HomePage() {
  return (
    <div className="space-y-16">
      {/* Hero Section - DIPERBAIKI */}
      <section className="relative min-h-[80vh] bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 text-white flex items-center">
        <div className="absolute inset-0 bg-black/20"></div>
        <div className="container relative z-10 py-24 md:py-32">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-6xl font-bold mb-6 leading-tight">
              Belanja Online
              <br />
              <span className="text-blue-200">Mudah & Aman</span>
            </h1>
            <p className="text-xl md:text-2xl text-blue-100 mb-8 leading-relaxed">
              Temukan ribuan produk berkualitas dengan harga terbaik. 
              Gratis ongkir ke seluruh Indonesia.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <Button size="lg" className="bg-white text-blue-600 hover:bg-blue-50" asChild>
                <Link href={ROUTES.PRODUCTS}>
                  Mulai Belanja
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Link>
              </Button>
              <Button 
                size="lg" 
                variant="outline" 
                className="border-white text-white hover:bg-white hover:text-blue-600 transition-all"
              >
                Lihat Promo
              </Button>
            </div>
          </div>
        </div>
        
        {/* Decorative Pattern */}
        <div className="absolute bottom-0 left-0 right-0">
          <svg viewBox="0 0 1200 120" className="w-full h-16 text-gray-50">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="currentColor"></path>
          </svg>
        </div>
      </section>

      {/* Features */}
      <section className="container">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card className="text-center border-0 shadow-lg hover:shadow-xl transition-shadow">
            <CardContent className="pt-8 pb-6">
              <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Truck className="h-8 w-8 text-blue-600" />
              </div>
              <h3 className="font-semibold text-lg mb-2">Gratis Ongkir</h3>
              <p className="text-gray-600">
                Gratis ongkos kirim ke seluruh Indonesia untuk pembelian minimal Rp 100.000
              </p>
            </CardContent>
          </Card>

          <Card className="text-center border-0 shadow-lg hover:shadow-xl transition-shadow">
            <CardContent className="pt-8 pb-6">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Shield className="h-8 w-8 text-green-600" />
              </div>
              <h3 className="font-semibold text-lg mb-2">Pembayaran Aman</h3>
              <p className="text-gray-600">
                Sistem pembayaran yang aman dan terpercaya dengan berbagai pilihan metode
              </p>
            </CardContent>
          </Card>

          <Card className="text-center border-0 shadow-lg hover:shadow-xl transition-shadow">
            <CardContent className="pt-8 pb-6">
              <div className="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Headphones className="h-8 w-8 text-purple-600" />
              </div>
              <h3 className="font-semibold text-lg mb-2">Customer Service 24/7</h3>
              <p className="text-gray-600">
                Tim customer service yang siap membantu Anda kapan saja
              </p>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* Categories */}
      <section className="container">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Kategori Populer</h2>
          <p className="text-gray-600 text-lg max-w-2xl mx-auto">
            Jelajahi berbagai kategori produk pilihan dengan kualitas terbaik
          </p>
        </div>
        
        <Suspense fallback={<CategoryLoadingSkeleton />}>
          <CategoryGrid />
        </Suspense>
      </section>

      {/* Featured Products */}
      <section className="container">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-3xl md:text-4xl font-bold mb-2">Produk Unggulan</h2>
            <p className="text-gray-600">Produk terpilih dengan kualitas terbaik</p>
          </div>
          <Button variant="outline" asChild>
            <Link href={ROUTES.PRODUCTS}>
              Lihat Semua
              <ArrowRight className="ml-2 h-4 w-4" />
            </Link>
          </Button>
        </div>
        
        <Suspense fallback={<ProductLoadingSkeleton />}>
          <FeaturedProducts />
        </Suspense>
      </section>

      {/* Newsletter */}
      <section className="bg-gradient-to-r from-blue-50 to-purple-50">
        <div className="container py-16">
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-3xl font-bold mb-4">Dapatkan Update Terbaru</h2>
            <p className="text-gray-600 mb-8">
              Berlangganan newsletter kami dan dapatkan informasi promo, produk baru, dan tips menarik lainnya.
            </p>
            
            <form className="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
              <input
                type="email"
                placeholder="Masukkan email Anda"
                className="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                required
              />
              <Button type="submit" className="whitespace-nowrap bg-blue-600 hover:bg-blue-700">
                Berlangganan
              </Button>
            </form>
          </div>
        </div>
      </section>
    </div>
  );
}

// Loading skeletons untuk better UX
function CategoryLoadingSkeleton() {
  return (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      {[...Array(6)].map((_, i) => (
        <div key={i} className="animate-pulse">
          <div className="aspect-square bg-gray-200 rounded-lg mb-2"></div>
          <div className="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
        </div>
      ))}
    </div>
  );
}

function ProductLoadingSkeleton() {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      {[...Array(8)].map((_, i) => (
        <div key={i} className="animate-pulse">
          <div className="aspect-square bg-gray-200 rounded-lg mb-4"></div>
          <div className="space-y-2">
            <div className="h-4 bg-gray-200 rounded w-3/4"></div>
            <div className="h-4 bg-gray-200 rounded w-1/2"></div>
            <div className="h-6 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>
      ))}
    </div>
  );
}