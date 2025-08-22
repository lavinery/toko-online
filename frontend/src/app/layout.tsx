
// src/app/layout.tsx
import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import { Toaster } from 'react-hot-toast';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { Providers } from './providers';
import './globals.css';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: {
    default: 'Toko Online - Platform E-commerce Terpercaya',
    template: '%s | Toko Online',
  },
  description: 'Platform e-commerce terpercaya dengan produk berkualitas dan layanan terbaik.',
  keywords: ['toko online', 'e-commerce', 'belanja online', 'indonesia'],
  authors: [{ name: 'Toko Online Team' }],
  creator: 'Toko Online',
  openGraph: {
    type: 'website',
    locale: 'id_ID',
    url: process.env.NEXT_PUBLIC_SITE_URL,
    siteName: 'Toko Online',
    title: 'Toko Online - Platform E-commerce Terpercaya',
    description: 'Platform e-commerce terpercaya dengan produk berkualitas dan layanan terbaik.',
  },
  twitter: {
    card: 'summary_large_image',
    site: '@tokoonline',
    creator: '@tokoonline',
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-video-preview': -1,
      'max-image-preview': 'large',
      'max-snippet': -1,
    },
  },
  verification: {
    google: 'your-google-verification-code',
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="id" className="h-full">
      <body className={`${inter.className} h-full`}>
        <Providers>
          <div className="flex min-h-full flex-col">
            <Header />
            <main className="flex-1">{children}</main>
            <Footer />
          </div>
          <Toaster
            position="top-right"
            toastOptions={{
              duration: 4000,
              style: {
                background: '#363636',
                color: '#fff',
              },
              success: {
                duration: 3000,
                iconTheme: {
                  primary: '#10b981',
                  secondary: '#fff',
                },
              },
              error: {
                duration: 5000,
                iconTheme: {
                  primary: '#ef4444',
                  secondary: '#fff',
                },
              },
            }}
          />
        </Providers>
      </body>
    </html>
  );
}
