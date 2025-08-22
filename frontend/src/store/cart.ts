
// src/store/cart.ts
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { Cart, CartItem, AddToCartData } from '@/types/cart';
import api from '@/lib/api';
import { API_ENDPOINTS, CART_STORAGE_KEY } from '@/lib/constants';
import toast from 'react-hot-toast';

interface CartState {
  cart: Cart | null;
  isLoading: boolean;
  error: string | null;
}

interface CartActions {
  fetchCart: () => Promise<void>;
  addItem: (data: AddToCartData) => Promise<void>;
  updateItem: (itemId: number, quantity: number) => Promise<void>;
  removeItem: (itemId: number) => Promise<void>;
  clearCart: () => Promise<void>;
  mergeGuestCart: (items: AddToCartData[]) => Promise<void>;
  clearError: () => void;
}

export const useCartStore = create<CartState & CartActions>()(
  persist(
    (set, get) => ({
      // State
      cart: null,
      isLoading: false,
      error: null,

      // Actions
      fetchCart: async () => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.get<{ data: Cart }>(API_ENDPOINTS.CART.GET);
          
          set({
            cart: response.data,
            isLoading: false,
          });
        } catch (error: any) {
          set({
            error: error.response?.data?.message || 'Failed to fetch cart',
            isLoading: false,
          });
        }
      },

      addItem: async (data: AddToCartData) => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.post<{ 
            message: string; 
            data: { cart_summary: { total_items: number; subtotal: number } } 
          }>(API_ENDPOINTS.CART.ADD, data);
          
          // Refresh cart after adding item
          await get().fetchCart();
          
          toast.success(response.message || 'Item added to cart');
          set({ isLoading: false });
        } catch (error: any) {
          const errorMessage = error.response?.data?.message || 'Failed to add item to cart';
          set({
            error: errorMessage,
            isLoading: false,
          });
          toast.error(errorMessage);
          throw error;
        }
      },

      updateItem: async (itemId: number, quantity: number) => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.patch<{ 
            message: string; 
            data: { cart_summary: { total_items: number; subtotal: number } } 
          }>(API_ENDPOINTS.CART.UPDATE(itemId), { quantity });
          
          // Update cart state optimistically
          const currentCart = get().cart;
          if (currentCart) {
            const updatedItems = currentCart.items.map(item => 
              item.id === itemId ? { ...item, quantity, subtotal: item.price * quantity } : item
            );
            const updatedCart = {
              ...currentCart,
              items: updatedItems,
              total_quantity: updatedItems.reduce((sum, item) => sum + item.quantity, 0),
              subtotal: updatedItems.reduce((sum, item) => sum + item.subtotal, 0),
            };
            set({ cart: updatedCart });
          }
          
          toast.success(response.message || 'Cart updated');
          set({ isLoading: false });
        } catch (error: any) {
          const errorMessage = error.response?.data?.message || 'Failed to update cart item';
          set({
            error: errorMessage,
            isLoading: false,
          });
          toast.error(errorMessage);
          throw error;
        }
      },

      removeItem: async (itemId: number) => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.delete<{ 
            message: string; 
            data: { cart_summary: { total_items: number; subtotal: number } } 
          }>(API_ENDPOINTS.CART.REMOVE(itemId));
          
          // Update cart state optimistically
          const currentCart = get().cart;
          if (currentCart) {
            const updatedItems = currentCart.items.filter(item => item.id !== itemId);
            const updatedCart = {
              ...currentCart,
              items: updatedItems,
              total_quantity: updatedItems.reduce((sum, item) => sum + item.quantity, 0),
              subtotal: updatedItems.reduce((sum, item) => sum + item.subtotal, 0),
            };
            set({ cart: updatedCart });
          }
          
          toast.success(response.message || 'Item removed from cart');
          set({ isLoading: false });
        } catch (error: any) {
          const errorMessage = error.response?.data?.message || 'Failed to remove cart item';
          set({
            error: errorMessage,
            isLoading: false,
          });
          toast.error(errorMessage);
          throw error;
        }
      },

      clearCart: async () => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.delete<{ message: string }>(API_ENDPOINTS.CART.CLEAR);
          
          set({
            cart: { ...get().cart!, items: [], total_quantity: 0, subtotal: 0 },
            isLoading: false,
          });
          
          toast.success(response.message || 'Cart cleared');
        } catch (error: any) {
          const errorMessage = error.response?.data?.message || 'Failed to clear cart';
          set({
            error: errorMessage,
            isLoading: false,
          });
          toast.error(errorMessage);
        }
      },

      mergeGuestCart: async (items: AddToCartData[]) => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.post<{
            message: string;
            data: { merged_items: number; cart_summary: { total_items: number; subtotal: number } }
          }>(API_ENDPOINTS.CART.MERGE, { items });
          
          // Refresh cart after merging
          await get().fetchCart();
          
          if (response.data.merged_items > 0) {
            toast.success(`${response.data.merged_items} items merged to your cart`);
          }
          
          set({ isLoading: false });
        } catch (error: any) {
          set({
            error: error.response?.data?.message || 'Failed to merge guest cart',
            isLoading: false,
          });
        }
      },

      clearError: () => set({ error: null }),
    }),
    {
      name: CART_STORAGE_KEY,
      partialize: (state) => ({
        // Only persist cart data, not loading states
        cart: state.cart,
      }),
    }
  )
);