import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Tag } from '@phosphor-icons/react';
import { formatPrice } from '../../utils/formatPrice';

function InlineChip({ children }) {
    return (
        <span className="inline-flex size-4 items-center justify-center rounded-[3px] bg-oxido-50 px-1 text-xs text-neutral-500">
            {children}
        </span>
    );
}

function SummaryProductCard({ item }) {
    return (
        <div className="flex items-center gap-4">
            <div className="h-[75px] w-[67px] shrink-0 overflow-hidden rounded-[10px] bg-neutral-100">
                {item.image ? (
                    <img src={item.image} alt={item.name} className="h-full w-full object-contain" />
                ) : (
                    <div className="h-full w-full bg-neutral-100" />
                )}
            </div>
            <div className="flex min-w-0 flex-1 items-center gap-8">
                <div className="flex min-w-0 flex-1 flex-col gap-[5px]">
                    <p className="text-xs font-semibold text-neutral-500 line-clamp-2">{item.name}</p>
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-0.5 text-xs text-neutral-500">
                        {item.size && (
                            <span className="inline-flex items-center gap-2">
                                Talle: <InlineChip>{item.size}</InlineChip>
                            </span>
                        )}
                        {item.color && (
                            <span className="inline-flex items-center gap-2">
                                Color:{' '}
                                <span
                                    className="inline-block size-4 rounded-[3px]"
                                    style={{ backgroundColor: item.colorHex || '#172037' }}
                                />
                            </span>
                        )}
                        <span className="inline-flex items-center gap-2">
                            Cantidad: <InlineChip>{item.quantity}</InlineChip>
                        </span>
                    </div>
                </div>
                <p className="whitespace-nowrap text-base font-medium text-neutral-500">
                    {formatPrice(item.unit_price * item.quantity)}
                </p>
            </div>
        </div>
    );
}

export default function OrderSummary({
    items = [],
    subtotal,
    discount = 0,
    couponCode,
    shipping = 0,
    total,
    showCouponInput = true,
}) {
    const [code, setCode] = useState('');

    const handleApplyCoupon = (e) => {
        e.preventDefault();
        if (!code.trim()) return;
        router.post('/carrito/cupon', { code: code.trim() }, { preserveScroll: true });
    };

    const handleRemoveCoupon = () => {
        router.delete('/carrito/cupon', { preserveScroll: true });
        setCode('');
    };

    return (
        <div className="sticky top-6 flex w-full flex-col">
            {items.length > 0 && (
                <div className="flex flex-col gap-2.5 py-4">
                    {items.map((item, i) => (
                        <div key={item.cart_key || i} className="flex flex-col gap-2.5">
                            <SummaryProductCard item={item} />
                            {i < items.length - 1 && <hr className="border-neutral-200" />}
                        </div>
                    ))}
                </div>
            )}

            <hr className="border-neutral-200" />

            <div className="flex flex-col py-4">
                <div className="flex items-center justify-between pb-4">
                    <span className="text-xs text-neutral-500">Subtotal</span>
                    <span className="text-base font-medium text-neutral-500">{formatPrice(subtotal)}</span>
                </div>

                {discount > 0 && (
                    <div className="flex items-center justify-between py-4">
                        <span className="text-xs text-neutral-500">Cupón de Descuento</span>
                        <span className="text-base font-medium text-neutral-500">-{formatPrice(discount)}</span>
                    </div>
                )}

                {shipping > 0 && (
                    <div className="flex items-center justify-between py-4">
                        <span className="text-xs text-neutral-500">Envío</span>
                        <span className="text-base font-medium text-neutral-500">{formatPrice(shipping)}</span>
                    </div>
                )}

                <hr className="border-neutral-200" />

                <div className="flex items-center justify-between py-4">
                    <span className="text-sm font-medium text-neutral-500">Total</span>
                    <span className="text-sm font-medium text-neutral-500">{formatPrice(total)}</span>
                </div>
            </div>

            {couponCode ? (
                <div className="flex items-center gap-6 px-5 py-2.5">
                    <Tag size={24} className="shrink-0 text-neutral-500" />
                    <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                        <p className="text-sm font-semibold text-neutral-500">Cupón Aplicado</p>
                        <p className="text-xs text-neutral-500">{couponCode}</p>
                    </div>
                    <button
                        type="button"
                        onClick={handleRemoveCoupon}
                        className="shrink-0 cursor-pointer text-sm font-semibold text-neutral-500 hover:underline"
                    >
                        Cambiar
                    </button>
                </div>
            ) : (
                showCouponInput && (
                    <form onSubmit={handleApplyCoupon} className="flex flex-col gap-2.5">
                        <label htmlFor="coupon-code" className="text-xs text-neutral-500">
                            ¿Tenés un cupón de descuento?
                        </label>
                        <div className="flex h-14 items-center gap-4 rounded-lg border border-neutral-300 bg-neutral-100 px-4">
                            <Tag size={20} className="shrink-0 text-neutral-500" />
                            <input
                                id="coupon-code"
                                type="text"
                                value={code}
                                onChange={(e) => setCode(e.target.value.toUpperCase())}
                                placeholder="EJEMPLO26"
                                className="min-w-0 flex-1 bg-transparent text-xs text-neutral-500 placeholder:text-neutral-300 focus:outline-none"
                            />
                            <button
                                type="submit"
                                className="shrink-0 cursor-pointer text-xs font-semibold text-oxido-300 hover:underline"
                            >
                                Aplicar
                            </button>
                        </div>
                    </form>
                )
            )}
        </div>
    );
}
