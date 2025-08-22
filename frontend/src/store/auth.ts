
// src/store/auth.ts
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User, LoginData, RegisterData, AuthResponse } from '@/types/auth';
import api from '@/lib/api';
import { API_ENDPOINTS } from '@/lib/constants';

interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

interface AuthActions {
  login: (data: LoginData) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
  fetchUser: () => Promise<void>;
  clearError: () => void;
  setUser: (user: User | null) => void;
}

export const useAuthStore = create<AuthState & AuthActions>()(
  persist(
    (set, get) => ({
      // State
      user: null,
      isAuthenticated: false,
      isLoading: false,
      error: null,

      // Actions
      login: async (data: LoginData) => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.post<AuthResponse>(API_ENDPOINTS.AUTH.LOGIN, data);
          
          api.setAuthToken(response.access_token);
          set({
            user: response.user,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error: any) {
          set({
            error: error.response?.data?.message || 'Login failed',
            isLoading: false,
          });
          throw error;
        }
      },

      register: async (data: RegisterData) => {
        try {
          set({ isLoading: true, error: null });
          
          const response = await api.post<AuthResponse>(API_ENDPOINTS.AUTH.REGISTER, data);
          
          api.setAuthToken(response.access_token);
          set({
            user: response.user,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error: any) {
          set({
            error: error.response?.data?.message || 'Registration failed',
            isLoading: false,
          });
          throw error;
        }
      },

      logout: async () => {
        try {
          await api.post(API_ENDPOINTS.AUTH.LOGOUT);
        } catch (error) {
          // Continue with logout even if API call fails
        } finally {
          api.removeAuthToken();
          set({
            user: null,
            isAuthenticated: false,
            error: null,
          });
        }
      },

      fetchUser: async () => {
        try {
          if (!api.isAuthenticated()) return;
          
          set({ isLoading: true });
          const response = await api.get<{ user: User }>(API_ENDPOINTS.AUTH.ME);
          
          set({
            user: response.user,
            isAuthenticated: true,
            isLoading: false,
          });
        } catch (error) {
          // Token might be invalid
          api.removeAuthToken();
          set({
            user: null,
            isAuthenticated: false,
            isLoading: false,
          });
        }
      },

      clearError: () => set({ error: null }),

      setUser: (user: User | null) => set({ 
        user, 
        isAuthenticated: !!user 
      }),
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({ 
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);