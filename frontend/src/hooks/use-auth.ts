
// src/hooks/use-auth.ts
import { useEffect } from 'react';
import { useAuthStore } from '@/store/auth';
import { useCartStore } from '@/store/cart';

export const useAuth = () => {
  const {
    user,
    isAuthenticated,
    isLoading,
    error,
    login,
    register,
    logout,
    fetchUser,
    clearError,
  } = useAuthStore();

  const { mergeGuestCart } = useCartStore();

  useEffect(() => {
    // Fetch user data on mount if token exists
    if (!user && useAuthStore.getState().isAuthenticated) {
      fetchUser();
    }
  }, [user, fetchUser]);

  const handleLogin = async (data: any) => {
    try {
      await login(data);
      
      // Merge guest cart after successful login
      const guestCartData = localStorage.getItem('guest_cart_items');
      if (guestCartData) {
        try {
          const guestItems = JSON.parse(guestCartData);
          if (guestItems.length > 0) {
            await mergeGuestCart(guestItems);
            localStorage.removeItem('guest_cart_items');
          }
        } catch (e) {
          console.error('Failed to merge guest cart:', e);
        }
      }
    } catch (error) {
      throw error;
    }
  };

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    login: handleLogin,
    register,
    logout,
    fetchUser,
    clearError,
  };
};
