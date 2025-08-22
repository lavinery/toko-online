// src/hooks/use-cart.ts
import { useEffect } from 'react';
import { useCartStore } from '@/store/cart';
import { useAuthStore } from '@/store/auth';

export const useCart = () => {
  const {
    cart,
    isLoading,
    error,
    fetchCart,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    clearError,
  } = useCartStore();

  const { isAuthenticated } = useAuthStore();

  useEffect(() => {
    // Fetch cart when user is authenticated
    if (isAuthenticated) {
      fetchCart();
    }
  }, [isAuthenticated, fetchCart]);

  const itemCount = cart?.total_quantity || 0;
  const subtotal = cart?.subtotal || 0;
  const isEmpty = !cart?.items?.length;

  return {
    cart,
    isLoading,
    error,
    itemCount,
    subtotal,
    isEmpty,
    fetchCart,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    clearError,
  };
};