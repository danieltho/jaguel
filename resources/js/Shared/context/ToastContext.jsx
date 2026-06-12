import { createContext, useCallback, useRef, useState } from 'react';
import ToastContainer from '../components/Toast/ToastContainer';

export const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);
  const idRef = useRef(0);

  const showToast = useCallback(({ type = 'success', title, message, image, duration = 3500 }) => {
    const id = ++idRef.current;
    // Máximo 3 toasts visibles a la vez: los más antiguos salen primero
    setToasts(prev => [...prev, { id, type, title, message, image, duration }].slice(-3));
    return id;
  }, []);

  const dismissToast = useCallback((id) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  }, []);

  return (
    <ToastContext.Provider value={{ toasts, showToast, dismissToast }}>
      {children}
      <ToastContainer />
    </ToastContext.Provider>
  );
}
