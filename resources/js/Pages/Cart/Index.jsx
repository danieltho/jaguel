import { Head, Link, router } from '@inertiajs/react';
import { Minus, Plus, Trash } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';
import { formatPrice } from '../../Shared/utils/formatPrice';

function CartItem({ item }) {
    const updateQuantity = (newQuantity) => {
        router.patch(
            '/carrito/actualizar',
            { cart_key: item.cart_key, quantity: newQuantity },
            { preserveScroll: true },
        );
    };

    const remove = () => {
        router.delete('/carrito/eliminar', {
            data: { cart_key: item.cart_key },
            preserveScroll: true,
        });
    };

    return (
        <div className="flex items-center gap-4 py-4">
            <Link href={`/producto/${item.slug}`} className="shrink-0">
                <div className="h-[100px] w-[100px] overflow-hidden rounded-xl bg-neutral-100">
                    <img
                        src={item.image || '/images/img_default.jpg'}
                        alt={item.name}
                        className="h-full w-full object-cover"
                    />
                </div>
            </Link>

            <div className="min-w-0 flex-1">
                <Link href={`/producto/${item.slug}`} className="block">
                    <h3 className="line-clamp-2 text-sm font-semibold text-neutral-500">
                        {item.name}
                    </h3>
                </Link>
                {(item.size || item.color) && (
                    <p className="mt-1 text-xs text-neutral-400">
                        {[item.color, item.size].filter(Boolean).join(' / ')}
                    </p>
                )}
                <p className="mt-1 text-base font-semibold text-neutral-500">
                    {formatPrice(item.unit_price)}
                </p>
            </div>

            <div className="flex items-center gap-3 rounded-lg bg-oxido-50 px-4 py-2">
                <button
                    type="button"
                    onClick={() => updateQuantity(Math.max(1, item.quantity - 1))}
                    aria-label="Restar"
                    className="text-neutral-500 hover:text-neutral-400"
                >
                    <Minus size={16} weight="bold" />
                </button>
                <span className="w-5 text-center text-sm font-medium text-neutral-500">
                    {item.quantity}
                </span>
                <button
                    type="button"
                    onClick={() => updateQuantity(item.quantity + 1)}
                    aria-label="Sumar"
                    className="text-neutral-500 hover:text-neutral-400"
                >
                    <Plus size={16} weight="bold" />
                </button>
            </div>

            <button
                type="button"
                onClick={remove}
                aria-label="Eliminar"
                className="p-1 text-carmesi-300 transition-colors hover:text-carmesi-100"
            >
                <Trash size={20} weight="bold" />
            </button>
        </div>
    );
}

export default function CartIndex({ items, subtotal, discount, couponCode, shipping, total }) {
    const breadcrumbItems = [
        { label: 'Inicio', href: '/' },
        { label: 'Carrito' },
    ];

    if (items.length === 0) {
        return (
            <Template>
                <Head title="Carrito" />
                <div className="mx-auto max-w-[1320px] px-15 py-6">
                    <Breadcrumb items={breadcrumbItems} />
                    <div className="flex flex-col items-center justify-center gap-4 py-20">
                        <h1 className="text-2xl font-bold text-neutral-500">Tu carrito está vacío</h1>
                        <p className="text-sm text-neutral-400">Agregá productos para comenzar tu compra.</p>
                        <Link
                            href="/productos"
                            className="mt-4 inline-flex h-10 items-center justify-center rounded-lg bg-oxido-300 px-6 text-xs font-medium text-oxido-50 hover:opacity-90"
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
            <div className="mx-auto max-w-[1320px] px-15 py-6">
                <Breadcrumb items={breadcrumbItems} />

                <h1 className="mt-4 mb-6 text-2xl font-bold text-neutral-500">Tu Carrito</h1>

                <div className="flex items-start gap-6">
                    <div className="flex-1 divide-y divide-neutral-200 rounded-2xl bg-white p-6">
                        {items.map((item) => (
                            <CartItem key={item.cart_key} item={item} />
                        ))}
                    </div>

                    <div className="w-[424px] shrink-0">
                        <div className="flex flex-col gap-4">
                            <OrderSummary
                                items={[]}
                                subtotal={subtotal}
                                discount={discount}
                                couponCode={couponCode}
                                shipping={shipping}
                                total={total}
                            />
                            <Link
                                href="/checkout/contacto"
                                className="block w-full rounded-lg bg-oxido-300 py-3.5 text-center text-xs font-medium text-oxido-50 hover:opacity-90"
                            >
                                Iniciar Compra
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Template>
    );
}
