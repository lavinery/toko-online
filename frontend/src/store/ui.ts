
// src/store/ui.ts
import { create } from 'zustand';

interface UIState {
  isMobileMenuOpen: boolean;
  isCartOpen: boolean;
  isSearchOpen: boolean;
  isLoading: boolean;
  notification: {
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
    isVisible: boolean;
  } | null;
}

interface UIActions {
  toggleMobileMenu: () => void;
  closeMobileMenu: () => void;
  toggleCart: () => void;
  closeCart: () => void;
  toggleSearch: () => void;
  closeSearch: () => void;
  setLoading: (loading: boolean) => void;
  showNotification: (message: string, type: UIState['notification']['type']) => void;
  hideNotification: () => void;
}

export const useUIStore = create<UIState & UIActions>((set) => ({
  // State
  isMobileMenuOpen: false,
  isCartOpen: false,
  isSearchOpen: false,
  isLoading: false,
  notification: null,

  // Actions
  toggleMobileMenu: () => set((state) => ({ 
    isMobileMenuOpen: !state.isMobileMenuOpen,
    isCartOpen: false,
    isSearchOpen: false,
  })),
  
  closeMobileMenu: () => set({ isMobileMenuOpen: false }),

  toggleCart: () => set((state) => ({ 
    isCartOpen: !state.isCartOpen,
    isMobileMenuOpen: false,
    isSearchOpen: false,
  })),
  
  closeCart: () => set({ isCartOpen: false }),

  toggleSearch: () => set((state) => ({ 
    isSearchOpen: !state.isSearchOpen,
    isMobileMenuOpen: false,
    isCartOpen: false,
  })),
  
  closeSearch: () => set({ isSearchOpen: false }),

  setLoading: (loading: boolean) => set({ isLoading: loading }),

  showNotification: (message: string, type: UIState['notification']['type']) => 
    set({ 
      notification: { message, type, isVisible: true } 
    }),

  hideNotification: () => set({ notification: null }),
}));