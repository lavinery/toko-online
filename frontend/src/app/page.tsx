/ src/app/page.tsx
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
      {/* Hero Section */}
      <section className="relative bg-gradient-to-r from-primary-600 to-primary-700 text-white">
        <div className="container py-24 md:py-32">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-6xl font-bold mb-6">
              Belanja Online
              <br />
              <span className="text-primary-200">Mudah & Aman</span>
            </h1>
            <p className="text-xl md:text-2xl text-primary-100 mb-8">
              Temukan ribuan produk berkualitas dengan harga terbaik. 
              Gratis ongkir ke seluruh Indonesia.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <Button size="lg" variant="secondary" asChild>
                <Link href={ROUTES.PRODUCTS}>
                  Mulai Belanja
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Link>
              </Button>
              <Button size="lg" variant="outline" className="text-white border-white hover:bg-white hover:text-primary-600">
                Lihat Promo
              </Button>
            </div>
          </div>
        </div>
        
        {/* Hero Image/Graphics */}
        <div className="absolute inset-y-0 right-0 w-1/2 hidden lg:block">
          <div className="h-full w-full bg-gradient-to-l from-transparent to-primary-600/20">
            {/* You can add hero image here */}
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="container">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card className="text-center border-0 shadow-lg">
            <CardContent className="pt-8 pb-6">
              <div className="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Truck className="h-6 w-6 text-primary-600" />
              </div>
              <h3 className="font-semibold text-lg mb-2">Gratis Ongkir</h3>
              <p className="text-gray-600">
                Gratis ongkos kirim ke seluruh Indonesia untuk pembelian minimal Rp 100.000
              </p>
            </CardContent>
          </Card>

          <Card className="text-center border-0 shadow-lg">
            <CardContent className="pt-8 pb-6">
              <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Shield className="h-6 w-6 text-green-600" />
              </div>
              <h3 className="font-semibold text-lg mb-2">Pembayaran Aman</h3>
              <p className="text-gray-600">
                Sistem pembayaran yang aman dan terpercaya dengan berbagai pilihan metode
              </p>
            </CardContent>
          </Card>

          <Card className="text-center border-0 shadow-lg">
            <CardContent className="pt-8 pb-6">
              <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Headphones className="h-6 w-6 text-blue-600" />
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
        
        <Suspense fallback={<Loading />}>
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
        
        <Suspense fallback={<Loading />}>
          <FeaturedProducts />
        </Suspense>
      </section>

      {/* Newsletter */}
      <section className="bg-gray-50">
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
                className="flex-1 px-4 py-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                required
              />
              <Button type="submit" className="whitespace-nowrap">
                Berlangganan
              </Button>
            </form>
          </div>
        </div>
      </section>
    </div>
  );
}