import { useState } from 'react';
import { Dialog, DialogPanel } from '@headlessui/react';
import { CaretLeft, CaretRight, X } from '@phosphor-icons/react';

export default function ImageGallery({
    images,
    alt = '',
    activeIndex,
    onIndexChange,
    className = '',
}) {
    const [internalIndex, setInternalIndex] = useState(0);
    const [isFullscreen, setIsFullscreen] = useState(false);
    const isControlled = typeof activeIndex === 'number';
    const index = isControlled ? activeIndex : internalIndex;

    if (!images?.length) return null;

    const current = images[index] ?? images[0];
    const hasMultiple = images.length > 1;

    const setIndex = (next) => {
        const bounded = (next + images.length) % images.length;
        if (!isControlled) setInternalIndex(bounded);
        onIndexChange?.(bounded);
    };

    return (
        <>
            <div
                className={`relative w-full aspect-[3/4] bg-oxido-50 rounded-br-[20px] overflow-hidden ${className}`.trim()}
            >
                <button
                    type="button"
                    onClick={() => setIsFullscreen(true)}
                    aria-label="Ampliar imagen"
                    className="absolute inset-0 w-full h-full cursor-zoom-in"
                >
                    <img
                        src={current?.url || '/images/img_default.jpg'}
                        alt={alt}
                        className="absolute inset-0 w-full h-full object-contain"
                    />
                </button>

                {hasMultiple && (
                    <>
                        <button
                            type="button"
                            onClick={() => setIndex(index - 1)}
                            aria-label="Imagen anterior"
                            className="absolute left-0 top-1/2 -translate-y-1/2 size-16 flex items-center justify-center text-oxido-300 cursor-pointer hover:opacity-70 transition-opacity z-10"
                        >
                            <CaretLeft size={32} weight="bold" />
                        </button>
                        <button
                            type="button"
                            onClick={() => setIndex(index + 1)}
                            aria-label="Imagen siguiente"
                            className="absolute right-0 top-1/2 -translate-y-1/2 size-16 flex items-center justify-center text-oxido-300 cursor-pointer hover:opacity-70 transition-opacity z-10"
                        >
                            <CaretRight size={32} weight="bold" />
                        </button>
                    </>
                )}
            </div>

            <Dialog
                open={isFullscreen}
                onClose={() => setIsFullscreen(false)}
                className="relative z-50"
            >
                <div className="fixed inset-0 bg-black/70" aria-hidden="true" />

                <div className="fixed inset-0 flex items-center justify-center p-4">
                    <DialogPanel className="relative w-full max-w-[650px] aspect-[650/867] rounded-[20px] overflow-hidden bg-oxido-50">
                        <img
                            src={current?.url || '/images/img_default.jpg'}
                            alt={alt}
                            className="absolute inset-0 w-full h-full object-contain"
                        />

                        <button
                            type="button"
                            onClick={() => setIsFullscreen(false)}
                            aria-label="Cerrar"
                            className="absolute top-4 right-4 size-10 rounded-full bg-white/90 hover:bg-white flex items-center justify-center text-neutral-500 cursor-pointer transition-colors z-10"
                        >
                            <X size={20} weight="bold" />
                        </button>

                        {hasMultiple && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setIndex(index - 1)}
                                    aria-label="Imagen anterior"
                                    className="absolute left-2 top-1/2 -translate-y-1/2 size-16 flex items-center justify-center text-oxido-300 cursor-pointer hover:opacity-70 transition-opacity z-10"
                                >
                                    <CaretLeft size={32} weight="bold" />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setIndex(index + 1)}
                                    aria-label="Imagen siguiente"
                                    className="absolute right-2 top-1/2 -translate-y-1/2 size-16 flex items-center justify-center text-oxido-300 cursor-pointer hover:opacity-70 transition-opacity z-10"
                                >
                                    <CaretRight size={32} weight="bold" />
                                </button>
                            </>
                        )}

                        {hasMultiple && (
                            <div className="absolute bottom-0 inset-x-0 flex items-end justify-center gap-6 pb-[60px] z-10">
                                {images.map((img, idx) => {
                                    const isActive = idx === index;
                                    return (
                                        <button
                                            key={img.id ?? idx}
                                            type="button"
                                            onClick={() => setIndex(idx)}
                                            aria-label={`Ver imagen ${idx + 1}`}
                                            className={`relative w-[88px] h-[106px] rounded-[10px] overflow-hidden bg-oxido-50 cursor-pointer transition-all ${
                                                isActive
                                                    ? 'ring-2 ring-moss-300'
                                                    : 'hover:opacity-80'
                                            }`}
                                        >
                                            <img
                                                src={img.thumb || img.url}
                                                alt=""
                                                className="absolute inset-0 w-full h-full object-cover"
                                            />
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </DialogPanel>
                </div>
            </Dialog>
        </>
    );
}
