// src/hooks/use-api.ts
import { useState, useCallback } from 'react';
import api from '@/lib/api';

interface UseApiOptions<T> {
  onSuccess?: (data: T) => void;
  onError?: (error: any) => void;
}

export const useApi = <T = any>(options?: UseApiOptions<T>) => {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const execute = useCallback(async (
    method: 'get' | 'post' | 'patch' | 'put' | 'delete',
    url: string,
    payload?: any
  ) => {
    try {
      setLoading(true);
      setError(null);

      const response = await api[method]<T>(url, payload);
      setData(response);
      
      if (options?.onSuccess) {
        options.onSuccess(response);
      }

      return response;
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || err.message || 'An error occurred';
      setError(errorMessage);
      
      if (options?.onError) {
        options.onError(err);
      }

      throw err;
    } finally {
      setLoading(false);
    }
  }, [options]);

  const get = useCallback((url: string) => execute('get', url), [execute]);
  const post = useCallback((url: string, data?: any) => execute('post', url, data), [execute]);
  const patch = useCallback((url: string, data?: any) => execute('patch', url, data), [execute]);
  const put = useCallback((url: string, data?: any) => execute('put', url, data), [execute]);
  const del = useCallback((url: string) => execute('delete', url), [execute]);

  const reset = useCallback(() => {
    setData(null);
    setError(null);
    setLoading(false);
  }, []);

  return {
    data,
    loading,
    error,
    get,
    post,
    patch,
    put,
    delete: del,
    reset,
  };
};