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
};

type ToastContextValue = {
  toasts: Toast[];
  showToast: (message: string, type: ToastType) => void;
};

const ToastContext = createContext<ToastContextValue | null>(null);

const TOAST_BAR_S = 4;
const TOAST_VISIBLE_MS = 4100;

const SuccessIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
    <circle cx="10" cy="10" r="9" stroke="currentColor" strokeWidth="2" />
    <path d="M6 10l3 3 5-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
  </svg>
);

const WarningIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
    <path d="M10 3.5L2 16.5h16L10 3.5z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" fill="none" />
    <path d="M10 8v3M10 13v1" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
  </svg>
);

const ErrorIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
    <path d="M10 3.5L2 16.5h16L10 3.5z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" fill="none" />
    <path d="M10 8v3M10 13v1" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
  </svg>
);

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

  const showToast = useCallback((message: string, type: ToastType) => {
    const id = nextIdRef.current++;
    const toast: Toast = { id, message, type };
    setToasts((prev) => [...prev, toast]);
    const timeoutId = setTimeout(() => {
      removeToast(id);
    }, TOAST_VISIBLE_MS);
    timeoutsRef.current.set(id, timeoutId);
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
          const Icon = isSuccess ? SuccessIcon : isError ? ErrorIcon : WarningIcon;
          return (
            <div
              key={toast.id}
              className={`subscription-toast subscription-toast-${toast.type}`}
              role="status"
              aria-live="polite"
              style={{ ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
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
              <div className="subscription-toast-timer" aria-hidden />
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
