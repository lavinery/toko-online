
// src/lib/api.ts
import axios, { AxiosInstance, AxiosError } from 'axios';
import Cookies from 'js-cookie';
import { API_URL, AUTH_TOKEN_KEY } from './constants';

class ApiClient {
  private instance: AxiosInstance;

  constructor() {
    this.instance = axios.create({
      baseURL: API_URL,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    this.setupInterceptors();
  }

  private setupInterceptors() {
    // Request interceptor - add auth token
    this.instance.interceptors.request.use(
      (config) => {
        const token = Cookies.get(AUTH_TOKEN_KEY);
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }

        // Add session ID for guest cart
        const sessionId = this.getSessionId();
        if (sessionId) {
          config.headers['X-Session-ID'] = sessionId;
        }

        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor - handle errors
    this.instance.interceptors.response.use(
      (response) => response,
      async (error: AxiosError) => {
        if (error.response?.status === 401) {
          // Token expired or invalid
          Cookies.remove(AUTH_TOKEN_KEY);
          
          // Don't redirect on auth endpoints
          if (!error.config?.url?.includes('/auth/')) {
            window.location.href = '/login';
          }
        }

        return Promise.reject(error);
      }
    );
  }

  private getSessionId(): string {
    let sessionId = localStorage.getItem('session_id');
    if (!sessionId) {
      sessionId = Math.random().toString(36).substring(2, 15) + 
                  Math.random().toString(36).substring(2, 15);
      localStorage.setItem('session_id', sessionId);
    }
    return sessionId;
  }

  // HTTP Methods
  async get<T>(url: string, params?: any): Promise<T> {
    const response = await this.instance.get(url, { params });
    return response.data;
  }

  async post<T>(url: string, data?: any): Promise<T> {
    const response = await this.instance.post(url, data);
    return response.data;
  }

  async patch<T>(url: string, data?: any): Promise<T> {
    const response = await this.instance.patch(url, data);
    return response.data;
  }

  async put<T>(url: string, data?: any): Promise<T> {
    const response = await this.instance.put(url, data);
    return response.data;
  }

  async delete<T>(url: string): Promise<T> {
    const response = await this.instance.delete(url);
    return response.data;
  }

  // Auth methods
  setAuthToken(token: string) {
    Cookies.set(AUTH_TOKEN_KEY, token, { expires: 7 }); // 7 days
  }

  removeAuthToken() {
    Cookies.remove(AUTH_TOKEN_KEY);
  }

  getAuthToken(): string | undefined {
    return Cookies.get(AUTH_TOKEN_KEY);
  }

  isAuthenticated(): boolean {
    return !!this.getAuthToken();
  }
}

export const api = new ApiClient();
export default api;