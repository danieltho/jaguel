import { useEffect, useRef } from 'react';
import { motion, useAnimate } from 'motion/react';
import { CheckCircle, XCircle, X } from '@phosphor-icons/react';

const variants = {
    success: {
        border: 'border-l-success-300',
        bar: 'bg-success-300',
        icon: <CheckCircle size={28} weight="fill" className="text-success-300 shrink-0" />,
    },
    error: {
        border: 'border-l-error-300',
        bar: 'bg-error-300',
        icon: <XCircle size={28} weight="fill" className="text-error-300 shrink-0" />,
    },
};

export default function Toast({ toast, onDismiss }) {
    const { id, type, title, message, image, duration } = toast;
    const variant = variants[type] ?? variants.success;

    // La barra de progreso es la única fuente de verdad del auto-dismiss:
    // al pausarla en hover, el cierre queda pausado por construcción.
    const [scope, animate] = useAnimate();
    const controlsRef = useRef(null);

    useEffect(() => {
        const controls = animate(scope.current, { scaleX: 0 }, {
            duration: duration / 1000,
            ease: 'linear',
            onComplete: () => onDismiss(id),
        });
        controlsRef.current = controls;
        return () => controls.stop();
    }, []);

    const pause = () => controlsRef.current?.pause();
    const resume = () => controlsRef.current?.play();

    return (
        <motion.div
            layout
            role={type === 'error' ? 'alert' : 'status'}
            aria-live={type === 'error' ? 'assertive' : 'polite'}
            initial={{ opacity: 0, x: 80, scale: 0.95 }}
            animate={{ opacity: 1, x: 0, scale: 1 }}
            exit={{ opacity: 0, x: 40, transition: { duration: 0.2, ease: 'easeIn' } }}
            transition={{ type: 'spring', stiffness: 380, damping: 28 }}
            onMouseEnter={pause}
            onMouseLeave={resume}
            className={`pointer-events-auto relative overflow-hidden rounded-2xl bg-oxido-50 border-l-4 ${variant.border} shadow-[0px_4px_10px_0px_rgba(0,0,0,0.25)]`}
        >
            <div className="flex items-center gap-3 p-4">
                {image ? (
                    <img src={image} alt="" className="w-10 h-12 rounded-lg object-cover shrink-0" />
                ) : (
                    variant.icon
                )}
                <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                    <p className="text-sm font-semibold text-neutral-500">{title}</p>
                    {message && (
                        <p className="text-xs text-neutral-500/80 line-clamp-2">{message}</p>
                    )}
                </div>
                <button
                    type="button"
                    onClick={() => onDismiss(id)}
                    aria-label="Cerrar notificación"
                    className="shrink-0 self-start rounded-md p-1 text-neutral-400 cursor-pointer hover:bg-moss-50 transition-colors"
                >
                    <X size={16} weight="bold" />
                </button>
            </div>
            <div
                ref={scope}
                className={`absolute bottom-0 left-0 h-1 w-full origin-left ${variant.bar}`}
                style={{ scaleX: 1 }}
            />
        </motion.div>
    );
}
