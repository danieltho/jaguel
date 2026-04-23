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
                <DialogPanel className="fixed inset-0 bg-oxido-50/95 flex flex-col">
                    <header className="flex items-center justify-between px-15 pt-[30px] pb-[30px]">
                        <p className="text-base font-medium text-neutral-500">{alt}</p>
                        <button
                            type="button"
                            onClick={() => setIsFullscreen(false)}
                            aria-label="Cerrar"
                            className="size-8 flex items-center justify-center text-neutral-500 cursor-pointer hover:opacity-70 transition-opacity"
                        >
                            <X size={32} weight="bold" />
                        </button>
                    </header>

                    <div className="flex-1 flex items-center justify-center px-15 pb-[60px] min-h-0">
                        <div className="relative h-full w-full max-w-[900px] flex flex-col items-center justify-end gap-6">
                            <img
                                src={current?.url || '/images/img_default.jpg'}
                                alt={alt}
                                className="absolute inset-0 w-full h-full object-contain"
                            />

                            {hasMultiple && (
                                <div className="relative flex gap-6 items-center z-10">
                                    {images.map((img, idx) => {
                                        const isActive = idx === index;
                                        return (
                                            <button
                                                key={img.id ?? idx}
                                                type="button"
                                                onClick={() => setIndex(idx)}
                                                aria-label={`Ver imagen ${idx + 1}`}
                                                className={`w-[88px] h-[106px] rounded-[10px] overflow-hidden bg-oxido-50 cursor-pointer transition-all ${
                                                    isActive
                                                        ? 'ring-2 ring-moss-300'
                                                        : 'hover:opacity-80'
                                                }`}
                                            >
                                                <img
                                                    src={img.thumb || img.url}
                                                    alt=""
                                                    className="w-full h-full object-cover"
                                                />
                                            </button>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </div>
                </DialogPanel>
            </Dialog>
        </>
    );
}
