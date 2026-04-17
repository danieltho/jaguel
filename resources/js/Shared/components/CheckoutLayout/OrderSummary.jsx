import { Tag } from '@phosphor-icons/react';
import { formatPrice } from '../../utils/formatPrice';

function SummaryProductCard({ item }) {
    return (
        <div className="flex gap-4 items-center py-2">
            <div className="w-[62px] h-[75px] rounded-[10px] overflow-hidden bg-neutral-100 shrink-0">
                {item.image ? (
                    <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                ) : (
                    <div className="w-full h-full bg-neutral-100" />
                )}
            </div>
            <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-neutral-500 line-clamp-2">{item.name}</p>
                <div className="flex flex-wrap gap-x-4 gap-y-0.5 mt-1 text-sm text-neutral-500">
                    {item.size && (
                        <span>Talle: <span className="bg-oxido-50 px-2 py-0.5 rounded text-xs">{item.size}</span></span>
                    )}
                    {item.color && (
                        <span className="flex items-center gap-1">
                            Color: <span className="w-4 h-4 rounded bg-neutral-500 inline-block" style={{ backgroundColor: item.colorHex || '#202322' }} />
                        </span>
                    )}
                    <span>Cantidad: <span className="bg-oxido-50 px-2 py-0.5 rounded text-xs">{item.quantity}</span></span>
                </div>
            </div>
            <p className="text-base font-medium text-neutral-500 shrink-0 whitespace-nowrap">
                {formatPrice(item.unit_price * item.quantity)}
            </p>
        </div>
    );
}

export default function OrderSummary({ items = [], subtotal, discount, couponCode, shipping, total }) {
    return (
        <div className="sticky top-6">
            {/* Products */}
            {items.length > 0 && (
                <div className="flex flex-col gap-2 py-4">
                    {items.map((item, i) => (
                        <div key={item.cart_key || i}>
                            <SummaryProductCard item={item} />
                            {i < items.length - 1 && <hr className="border-neutral-100 mt-2" />}
                        </div>
                    ))}
                </div>
            )}

            <hr className="border-neutral-100" />

            {/* Summary lines */}
            <div className="py-4 flex flex-col gap-4">
                <div className="flex justify-between items-center">
                    <span className="text-sm text-neutral-500">Subtotal</span>
                    <span className="text-base font-medium text-neutral-500">{formatPrice(subtotal)}</span>
                </div>

                {discount > 0 && (
                    <div className="flex justify-between items-center">
                        <span className="text-sm text-neutral-500">Cupon de Descuento</span>
                        <span className="text-base font-medium text-neutral-500">-{formatPrice(discount)}</span>
                    </div>
                )}

                <hr className="border-neutral-100" />

                <div className="flex justify-between items-center">
                    <span className="text-xl font-semibold text-neutral-500">Total</span>
                    <span className="text-xl font-semibold text-neutral-500">{formatPrice(total)}</span>
                </div>
            </div>

            {/* Applied coupon */}
            {couponCode && (
                <div className="py-3 px-5 flex items-center gap-4">
                    <Tag size={24} className="text-neutral-500 shrink-0" />
                    <div className="flex-1">
                        <p className="text-sm font-semibold text-neutral-500">Cupon Aplicado</p>
                        <p className="text-sm text-neutral-500">{couponCode}</p>
                    </div>
                </div>
            )}
        </div>
    );
}
