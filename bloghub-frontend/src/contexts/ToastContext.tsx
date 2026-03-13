import {
  createContext,
  useCallback,
  useContext,
  useRef,
  useState,
  type ReactNode,
} from 'react';

export type ToastType = 'success' | 'warning' | 'error';

export type Toast = {
  id: number;
  message: string;
  type: ToastType;
  persistent?: boolean;
};

export type ShowToastOptions = {
  persistent?: boolean;
};

type ToastContextValue = {
  toasts: Toast[];
  showToast: (message: string, type: ToastType, options?: ShowToastOptions) => void;
};

const ToastContext = createContext<ToastContextValue | null>(null);

const TOAST_BAR_S = 4;
const TOAST_VISIBLE_MS = 4100;

import { SuccessToastIcon, WarningToastIcon, ErrorToastIcon } from '../components/icons';

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const nextIdRef = useRef(0);
  const timeoutsRef = useRef<Map<number, ReturnType<typeof setTimeout>>>(new Map());

  const removeToast = useCallback((id: number) => {
    const t = timeoutsRef.current.get(id);
    if (t) {
      clearTimeout(t);
      timeoutsRef.current.delete(id);
    }
    setToasts((prev) => prev.filter((toast) => toast.id !== id));
  }, []);

  const showToast = useCallback((message: string, type: ToastType, options?: ShowToastOptions) => {
    const id = nextIdRef.current++;
    const persistent = options?.persistent ?? false;
    const toast: Toast = { id, message, type, persistent };
    setToasts((prev) => [...prev, toast]);
    if (!persistent) {
      const timeoutId = setTimeout(() => {
        removeToast(id);
      }, TOAST_VISIBLE_MS);
      timeoutsRef.current.set(id, timeoutId);
    }
  }, [removeToast]);

  return (
    <ToastContext.Provider value={{ toasts, showToast }}>
      {children}
      <div className="toast-container" role="region" aria-label="Notifications">
        {toasts.map((toast) => {
          const isSuccess = toast.type === 'success';
          const isError = toast.type === 'error';
          const iconClass = isSuccess
            ? 'subscription-toast-icon-success'
            : isError
              ? 'subscription-toast-icon-error'
              : 'subscription-toast-icon-warning';
          const Icon = isSuccess ? SuccessToastIcon : isError ? ErrorToastIcon : WarningToastIcon;
          return (
            <div
              key={toast.id}
              className={`subscription-toast subscription-toast-${toast.type}${toast.persistent ? ' subscription-toast-persistent' : ''}`}
              role="status"
              aria-live="polite"
              style={toast.persistent ? undefined : { ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
            >
              <span className={`subscription-toast-icon ${iconClass}`} aria-hidden>
                <Icon />
              </span>
              <p className="subscription-toast-msg">{toast.message}</p>
              <button
                type="button"
                className="subscription-toast-close"
                onClick={() => removeToast(toast.id)}
                aria-label="Dismiss"
              >
                ×
              </button>
              {!toast.persistent && <div className="subscription-toast-timer" aria-hidden />}
            </div>
          );
        })}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const ctx = useContext(ToastContext);
  if (!ctx) throw new Error('useToast must be used within ToastProvider');
  return ctx;
}
