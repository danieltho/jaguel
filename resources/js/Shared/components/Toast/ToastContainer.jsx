import { useContext } from 'react';
import { AnimatePresence } from 'motion/react';
import { ToastContext } from '../../context/ToastContext';
import Toast from './Toast';

export default function ToastContainer() {
    const { toasts, dismissToast } = useContext(ToastContext);

    return (
        <div
            className="pointer-events-none fixed top-4 inset-x-4 z-[60] flex flex-col gap-3 sm:top-6 sm:right-6 sm:left-auto sm:w-[360px]"
        >
            <AnimatePresence mode="popLayout" initial={false}>
                {toasts.map((toast) => (
                    <Toast key={toast.id} toast={toast} onDismiss={dismissToast} />
                ))}
            </AnimatePresence>
        </div>
    );
}
