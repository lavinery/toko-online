// src/app/(shop)/produk/[slug]/page.tsx
import { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ProductDetail } from '@/components/product/product-detail';
import { RelatedProducts } from '@/components/product/related-products';
import { ProductReviews } from '@/components/product/product-reviews';
import api from '@/lib/api';
import { API_ENDPOINTS } from '@/lib/constants';
import { Product } from '@/types/product';

interface Props {
  params: { slug: string };
}

async function getProduct(slug: string): Promise<Product | null> {
  try {
    const response = await api.get<{ data: Product }>(
      API_ENDPOINTS.PRODUCTS.DETAIL(slug)
    );
    return response.data;
  } catch (error) {
    return null;
  }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const product = await getProduct(params.slug);

  if (!product) {
    return {
      title: 'Produk Tidak Ditemukan',
    };
  }

  return {
    title: product.name,
    description: product.short_description,
    keywords: [
      product.name,
      ...product.categories.map(cat => cat.name),
      'belanja online',
      'toko online'
    ].join(', '),
    openGraph: {
      title: product.name,
      description: product.short_description,
      images: product.images.map(img => ({
        url: img.url,
        width: 800,
        height: 800,
        alt: img.alt_text || product.name,
      })),
      type: 'website',
    },
    twitter: {
      card: 'summary_large_image',
      title: product.name,
      description: product.short_description,
      images: [product.primary_image?.url || ''],
    },
  };
}

export default async function ProductDetailPage({ params }: Props) {
  const product = await getProduct(params.slug);

  if (!product) {
    notFound();
  }

  const breadcrumbs = [
    { label: 'Beranda', href: '/' },
    { label: 'Produk', href: '/produk' },
    ...(product.categories.length > 0 ? [
      { label: product.categories[0].name, href: `/kategori/${product.categories[0].slug}` }
    ] : []),
    { label: product.name, href: '' },
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Breadcrumb */}
      <div className="bg-white border-b">
        <div className="container mx-auto px-4 py-3">
          <nav className="flex items-center space-x-2 text-sm">
            {breadcrumbs.map((crumb, index) => (
              <div key={index} className="flex items-center">
                {index > 0 && <span className="text-gray-400 mx-2">/</span>}
                {crumb.href ? (
                  <a href={crumb.href} className="text-primary-600 hover:text-primary-700">
                    {crumb.label}
                  </a>
                ) : (
                  <span className="text-gray-500">{crumb.label}</span>
                )}
              </div>
            ))}
          </nav>
        </div>
      </div>

      {/* Product Detail */}
      <div className="container mx-auto px-4 py-8">
        <ProductDetail product={product} />
      </div>

      {/* Product Reviews */}
      <div className="container mx-auto px-4 py-8">
        <ProductReviews productId={product.id} />
      </div>

      {/* Related Products */}
      <div className="container mx-auto px-4 py-8">
        <RelatedProducts 
          productId={product.id}
          categoryId={product.categories[0]?.id}
        />
      </div>
    </div>
  );
}