import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Minus, Plus, X } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import { formatPrice } from '../../Shared/utils/formatPrice';

function CartItem({ item }) {
    const handleUpdateQuantity = (newQuantity) => {
        router.patch('/carrito/actualizar', {
            cart_key: item.cart_key,
            quantity: newQuantity,
        }, { preserveScroll: true });
    };

    const handleRemove = () => {
        router.delete('/carrito/eliminar', {
            data: { cart_key: item.cart_key },
            preserveScroll: true,
        });
    };

    return (
        <div className="flex items-center gap-4 py-4 border-b border-neutral-100 last:border-b-0">
            {/* Image */}
            <Link href={`/producto/${item.slug}`} className="shrink-0">
                <div className="w-[100px] h-[100px] rounded-xl overflow-hidden bg-neutral-100">
                    <img src={item.image || '/images/img_default.jpg'} alt={item.name} className="w-full h-full object-cover" />
                </div>
            </Link>

            {/* Info */}
            <div className="flex-1 min-w-0">
                <Link href={`/producto/${item.slug}`} className="block">
                    <h3 className="text-sm font-semibold text-neutral-500 line-clamp-2">{item.name}</h3>
                </Link>
                {(item.color || item.size) && (
                    <p className="text-xs text-neutral-400 mt-1">
                        {[item.color, item.size].filter(Boolean).join(' / ')}
                    </p>
                )}
                <p className="text-base font-semibold text-neutral-500 mt-1">
                    {formatPrice(item.unit_price)}
                </p>
            </div>

            {/* Quantity */}
            <div className="flex items-center gap-3 bg-neutral-100 rounded-full px-4 py-2">
                <button
                    onClick={() => handleUpdateQuantity(Math.max(1, item.quantity - 1))}
                    className="text-neutral-500 hover:text-neutral-400"
                >
                    <Minus size={16} weight="bold" />
                </button>
                <span className="text-sm font-medium text-neutral-500 w-5 text-center">{item.quantity}</span>
                <button
                    onClick={() => handleUpdateQuantity(item.quantity + 1)}
                    className="text-neutral-500 hover:text-neutral-400"
                >
                    <Plus size={16} weight="bold" />
                </button>
            </div>

            {/* Remove */}
            <button
                onClick={handleRemove}
                className="text-carmesi-300 hover:text-carmesi-100 transition-colors p-1"
            >
                <X size={20} weight="bold" />
            </button>
        </div>
    );
}

function OrderSummary({ subtotal, discount, couponCode, shipping, total }) {
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
        <div className="bg-white rounded-2xl p-6 sticky top-6">
            <h2 className="text-lg font-semibold text-neutral-500 mb-4">Resumen de Compra</h2>

            <div className="flex flex-col gap-3 text-base">
                <div className="flex justify-between">
                    <span className="text-neutral-400">Subtotal</span>
                    <span className="text-neutral-500 font-medium">{formatPrice(subtotal)}</span>
                </div>

                {discount > 0 && (
                    <div className="flex justify-between">
                        <span className="text-neutral-400">Descuento</span>
                        <span className="text-carmesi-300 font-medium">-{formatPrice(discount)}</span>
                    </div>
                )}

                <div className="flex justify-between">
                    <span className="text-neutral-400">Envío</span>
                    <span className="text-neutral-500 font-medium">
                        {shipping > 0 ? formatPrice(shipping) : 'Free'}
                    </span>
                </div>

                {couponCode && (
                    <div className="flex justify-between items-center">
                        <span className="text-neutral-400">Cupón Aplicado</span>
                        <button
                            onClick={handleRemoveCoupon}
                            className="text-carmesi-300 text-sm hover:underline"
                        >
                            Quitar
                        </button>
                    </div>
                )}

                <hr className="border-neutral-100" />

                <div className="flex justify-between text-lg font-semibold">
                    <span className="text-neutral-500">Total</span>
                    <span className="text-neutral-500">{formatPrice(total)}</span>
                </div>
            </div>

            {/* Coupon Input */}
            {!couponCode && (
                <form onSubmit={handleApplyCoupon} className="mt-4 flex gap-2">
                    <input
                        type="text"
                        value={code}
                        onChange={(e) => setCode(e.target.value)}
                        placeholder="Código de cupón"
                        className="flex-1 px-4 py-2.5 rounded-full bg-neutral-100 border-none text-sm text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                    />
                    <button
                        type="submit"
                        className="px-5 py-2.5 rounded-full bg-neutral-500 text-white text-sm font-medium hover:bg-neutral-400 transition-colors"
                    >
                        Aplicar
                    </button>
                </form>
            )}

            {/* Checkout Button */}
            <Link
                href="/checkout/contacto"
                className="block w-full mt-4 py-3.5 bg-oxido-300 text-oxido-50 text-center font-medium rounded-[8px] hover:opacity-90 transition-opacity"
            >
                Iniciar Compra
            </Link>
        </div>
    );
}

export default function CartIndex({ items, subtotal, discount, couponCode, shipping, total }) {
    const breadcrumbItems = [
        { label: 'Home', href: '/' },
        { label: 'Carrito' },
    ];

    if (items.length === 0) {
        return (
            <Template>
                <Head title="Carrito" />
                <div className="max-w-[1080px] mx-auto px-4 py-6">
                    <Breadcrumb items={breadcrumbItems} />
                    <div className="flex flex-col items-center justify-center py-20 gap-4">
                        <h1 className="text-2xl font-bold text-neutral-500">Tu carrito está vacío</h1>
                        <p className="text-neutral-400">Agregá productos para comenzar tu compra.</p>
                        <Link
                            href="/productos"
                            className="mt-4 px-8 py-3 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors"
                        >
                            Ver Productos
                        </Link>
                    </div>
                </div>
            </Template>
        );
    }

    return (
        <Template>
            <Head title="Carrito" />
            <div className="max-w-[1080px] mx-auto px-4 py-6">
                <Breadcrumb items={breadcrumbItems} />

                <h1 className="text-3xl font-extrabold text-neutral-500 mt-4 mb-6">Tu Carrito</h1>

                <div className="flex gap-6">
                    {/* Items */}
                    <div className="flex-1 bg-white rounded-2xl p-6">
                        {items.map((item) => (
                            <CartItem key={item.cart_key} item={item} />
                        ))}
                    </div>

                    {/* Summary */}
                    <div className="w-[340px] shrink-0">
                        <OrderSummary
                            subtotal={subtotal}
                            discount={discount}
                            couponCode={couponCode}
                            shipping={shipping}
                            total={total}
                        />
                    </div>
                </div>
            </div>
        </Template>
    );
}
