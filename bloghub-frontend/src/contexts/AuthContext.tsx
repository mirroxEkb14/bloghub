import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from 'react';
import { authApi, type User } from '../api/client';

const TOKEN_KEY = 'auth_token';

type AuthState = {
  user: User | null;
  token: string | null;
  loading: boolean;
  error: string | null;
};

type AuthContextValue = AuthState & {
  login: (email: string, password: string) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
  clearError: () => void;
};

export type RegisterData = {
  name: string;
  username: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  terms_accepted?: boolean;
  privacy_accepted?: boolean;
};

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(() =>
    localStorage.getItem(TOKEN_KEY)
  );
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const clearError = useCallback(() => setError(null), []);

  const fetchUser = useCallback(async () => {
    const t = localStorage.getItem(TOKEN_KEY);
    if (!t) {
      setUser(null);
      setLoading(false);
      return;
    }
    try {
      const { user: u } = await authApi.user();
      setUser(u);
      setToken(t);
    } catch {
      localStorage.removeItem(TOKEN_KEY);
      setUser(null);
      setToken(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchUser();
  }, [fetchUser]);

  const login = useCallback(
    async (email: string, password: string) => {
      setError(null);
      try {
        const res = await authApi.login({ email, password });
        localStorage.setItem(TOKEN_KEY, res.token);
        setToken(res.token);
        setUser(res.user);
      } catch (e) {
        setError(e instanceof Error ? e.message : 'Login failed');
        throw e;
      }
    },
    []
  );

  const register = useCallback(async (data: RegisterData) => {
    setError(null);
    try {
      const res = await authApi.register(data);
      localStorage.setItem(TOKEN_KEY, res.token);
      setToken(res.token);
      setUser(res.user);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Registration failed');
      throw e;
    }
  }, []);

  const logout = useCallback(async () => {
    try {
      await authApi.logout();
    } finally {
      localStorage.removeItem(TOKEN_KEY);
      setToken(null);
      setUser(null);
    }
  }, []);

  const value: AuthContextValue = {
    user,
    token,
    loading,
    error,
    login,
    register,
    logout,
    clearError,
  };

  return (
    <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
