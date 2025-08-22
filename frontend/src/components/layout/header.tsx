'use client';

import Link from 'next/link';
import Image from 'next/image';
import { useState } from 'react';
import { 
  ShoppingCart, 
  Search, 
  Menu, 
  X, 
  User,
  ChevronDown,
  Heart
} from 'lucide-react';
import { useAuth } from '@/hooks/use-auth';
import { useCart } from '@/hooks/use-cart';
import { useUIStore } from '@/store/ui';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { APP_NAME, ROUTES } from '@/lib/constants';

export function Header() {
  const { user, isAuthenticated, logout } = useAuth();
  const { itemCount } = useCart();
  const { 
    isMobileMenuOpen, 
    toggleMobileMenu, 
    closeMobileMenu,
    isSearchOpen,
    toggleSearch,
    closeSearch
  } = useUIStore();

  const [searchQuery, setSearchQuery] = useState('');
  const [showUserMenu, setShowUserMenu] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      window.location.href = `/produk?search=${encodeURIComponent(searchQuery.trim())}`;
    }
  };

  const handleLogout = async () => {
    await logout();
    setShowUserMenu(false);
    closeMobileMenu();
  };

  const navLinks = [
    { href: ROUTES.HOME, label: 'Beranda' },
    { href: ROUTES.PRODUCTS, label: 'Produk' },
    { href: ROUTES.CATEGORIES, label: 'Kategori' },
  ];

  return (
    <header className="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
      <div className="container mx-auto px-4">
        {/* Main Header */}
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <Link href={ROUTES.HOME} className="flex items-center space-x-2">
            <div className="h-8 w-8 rounded bg-primary-600 flex items-center justify-center">
              <span className="text-white font-bold text-sm">T</span>
            </div>
            <span className="font-bold text-xl text-gray-900">{APP_NAME}</span>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            {navLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="text-gray-600 hover:text-gray-900 font-medium transition-colors"
              >
                {link.label}
              </Link>
            ))}
          </nav>

          {/* Desktop Search */}
          <div className="hidden md:flex flex-1 max-w-lg mx-8">
            <form onSubmit={handleSearch} className="relative w-full">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <Input
                type="search"
                placeholder="Cari produk..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10 pr-4"
              />
            </form>
          </div>

          {/* Desktop Actions */}
          <div className="hidden md:flex items-center space-x-4">
            {/* Search Toggle (Mobile) */}
            <Button
              variant="ghost"
              size="icon"
              onClick={toggleSearch}
              className="md:hidden"
            >
              <Search className="h-5 w-5" />
            </Button>

            {/* Wishlist */}
            {isAuthenticated && (
              <Button variant="ghost" size="icon" asChild>
                <Link href="/wishlist">
                  <Heart className="h-5 w-5" />
                </Link>
              </Button>
            )}

            {/* Cart */}
            <Button variant="ghost" size="icon" className="relative" asChild>
              <Link href={ROUTES.CART}>
                <ShoppingCart className="h-5 w-5" />
                {itemCount > 0 && (
                  <Badge className="absolute -top-2 -right-2 h-5 w-5 rounded-full p-0 text-xs">
                    {itemCount > 99 ? '99+' : itemCount}
                  </Badge>
                )}
              </Link>
            </Button>

            {/* User Menu */}
            {isAuthenticated ? (
              <div className="relative">
                <Button
                  variant="ghost"
                  onClick={() => setShowUserMenu(!showUserMenu)}
                  className="flex items-center space-x-2"
                >
                  <User className="h-5 w-5" />
                  <span className="max-w-24 truncate">{user?.name}</span>
                  <ChevronDown className="h-4 w-4" />
                </Button>

                {showUserMenu && (
                  <div className="absolute right-0 mt-2 w-48 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                    <Link
                      href={ROUTES.ACCOUNT}
                      className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                      onClick={() => setShowUserMenu(false)}
                    >
                      Akun Saya
                    </Link>
                    <Link
                      href={ROUTES.ORDERS}
                      className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                      onClick={() => setShowUserMenu(false)}
                    >
                      Pesanan
                    </Link>
                    <Link
                      href={ROUTES.ADDRESSES}
                      className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                      onClick={() => setShowUserMenu(false)}
                    >
                      Alamat
                    </Link>
                    <hr className="my-1" />
                    <button
                      onClick={handleLogout}
                      className="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100"
                    >
                      Keluar
                    </button>
                  </div>
                )}
              </div>
            ) : (
              <div className="flex items-center space-x-2">
                <Button variant="ghost" asChild>
                  <Link href={ROUTES.LOGIN}>Masuk</Link>
                </Button>
                <Button asChild>
                  <Link href={ROUTES.REGISTER}>Daftar</Link>
                </Button>
              </div>
            )}
          </div>

          {/* Mobile Menu Button */}
          <Button
            variant="ghost"
            size="icon"
            onClick={toggleMobileMenu}
            className="md:hidden"
          >
            {isMobileMenuOpen ? (
              <X className="h-5 w-5" />
            ) : (
              <Menu className="h-5 w-5" />
            )}
          </Button>
        </div>

        {/* Mobile Search Bar */}
        {isSearchOpen && (
          <div className="border-t border-gray-200 py-4 md:hidden">
            <form onSubmit={handleSearch} className="relative">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <Input
                type="search"
                placeholder="Cari produk..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10 pr-4"
                autoFocus
              />
            </form>
          </div>
        )}

        {/* Mobile Menu */}
        {isMobileMenuOpen && (
          <div className="border-t border-gray-200 py-4 md:hidden">
            <nav className="space-y-4">
              {navLinks.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="block text-gray-600 hover:text-gray-900 font-medium"
                  onClick={closeMobileMenu}
                >
                  {link.label}
                </Link>
              ))}
              
              <hr className="my-4" />
              
              {isAuthenticated ? (
                <div className="space-y-4">
                  <div className="flex items-center space-x-2 text-gray-900">
                    <User className="h-5 w-5" />
                    <span className="font-medium">{user?.name}</span>
                  </div>
                  
                  <Link
                    href={ROUTES.CART}
                    className="flex items-center space-x-2 text-gray-600 hover:text-gray-900"
                    onClick={closeMobileMenu}
                  >
                    <ShoppingCart className="h-5 w-5" />
                    <span>Keranjang ({itemCount})</span>
                  </Link>
                  
                  <Link
                    href={ROUTES.ACCOUNT}
                    className="block text-gray-600 hover:text-gray-900"
                    onClick={closeMobileMenu}
                  >
                    Akun Saya
                  </Link>
                  
                  <Link
                    href={ROUTES.ORDERS}
                    className="block text-gray-600 hover:text-gray-900"
                    onClick={closeMobileMenu}
                  >
                    Pesanan
                  </Link>
                  
                  <button
                    onClick={handleLogout}
                    className="block w-full text-left text-red-600 hover:text-red-700"
                  >
                    Keluar
                  </button>
                </div>
              ) : (
                <div className="space-y-4">
                  <Link
                    href={ROUTES.CART}
                    className="flex items-center space-x-2 text-gray-600 hover:text-gray-900"
                    onClick={closeMobileMenu}
                  >
                    <ShoppingCart className="h-5 w-5" />
                    <span>Keranjang ({itemCount})</span>
                  </Link>
                  
                  <Link
                    href={ROUTES.LOGIN}
                    className="block text-gray-600 hover:text-gray-900"
                    onClick={closeMobileMenu}
                  >
                    Masuk
                  </Link>
                  
                  <Link
                    href={ROUTES.REGISTER}
                    className="block text-primary-600 hover:text-primary-700 font-medium"
                    onClick={closeMobileMenu}
                  >
                    Daftar
                  </Link>
                </div>
              )}
            </nav>
          </div>
        )}
      </div>
    </header>
  );
}