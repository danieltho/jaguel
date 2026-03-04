import { Head, useForm } from '@inertiajs/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import { formatPrice } from '../../Shared/utils/formatPrice';

function CheckoutSummary({ summary }) {
    return (
        <div className="bg-white rounded-2xl p-6 sticky top-6">
            <h2 className="text-lg font-semibold text-neutral-500 mb-4">Resumen de Compra</h2>
            <div className="flex flex-col gap-3 text-base">
                <div className="flex justify-between">
                    <span className="text-neutral-400">Subtotal</span>
                    <span className="text-neutral-500 font-medium">{formatPrice(summary.subtotal)}</span>
                </div>
                {summary.discount > 0 && (
                    <div className="flex justify-between">
                        <span className="text-neutral-400">Descuento</span>
                        <span className="text-carmesi-300 font-medium">-{formatPrice(summary.discount)}</span>
                    </div>
                )}
                <div className="flex justify-between">
                    <span className="text-neutral-400">Envio</span>
                    <span className="text-neutral-500 font-medium">
                        {summary.shipping > 0 ? formatPrice(summary.shipping) : 'A calcular'}
                    </span>
                </div>
                <hr className="border-neutral-100" />
                <div className="flex justify-between text-lg font-semibold">
                    <span className="text-neutral-500">Total</span>
                    <span className="text-neutral-500">{formatPrice(summary.total)}</span>
                </div>
            </div>
        </div>
    );
}

export default function Address({ customer, summary }) {
    const { data, setData, post, processing, errors } = useForm({
        address: customer.address || '',
        address_number: customer.address_number || '',
        department: customer.department || '',
        city: customer.city || '',
        state: customer.state || '',
        postal_code: '',
    });

    const breadcrumbItems = [
        { label: 'Home', href: '/' },
        { label: 'Carrito', href: '/carrito' },
        { label: 'Direccion de Envio' },
    ];

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/checkout/direccion');
    };

    return (
        <Template>
            <Head title="Direccion de Envio" />
            <div className="max-w-[1080px] mx-auto px-4 py-6">
                <Breadcrumb items={breadcrumbItems} />

                {/* Steps indicator */}
                <div className="flex items-center gap-3 mt-6 mb-8">
                    <span className="flex items-center gap-2 text-sm font-medium text-neutral-500">
                        <span className="w-7 h-7 rounded-full bg-neutral-500 text-white flex items-center justify-center text-xs font-bold">1</span>
                        Direccion
                    </span>
                    <span className="h-px flex-1 bg-neutral-200" />
                    <span className="flex items-center gap-2 text-sm text-neutral-300">
                        <span className="w-7 h-7 rounded-full bg-neutral-200 text-neutral-400 flex items-center justify-center text-xs font-bold">2</span>
                        Envio
                    </span>
                    <span className="h-px flex-1 bg-neutral-200" />
                    <span className="flex items-center gap-2 text-sm text-neutral-300">
                        <span className="w-7 h-7 rounded-full bg-neutral-200 text-neutral-400 flex items-center justify-center text-xs font-bold">3</span>
                        Pago
                    </span>
                </div>

                <div className="flex gap-6">
                    {/* Form */}
                    <div className="flex-1">
                        <div className="bg-white rounded-2xl p-6">
                            <h1 className="text-2xl font-extrabold text-neutral-500 mb-6">Direccion de Envio</h1>

                            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="col-span-2 sm:col-span-1">
                                        <input
                                            type="text"
                                            value={data.address}
                                            onChange={(e) => setData('address', e.target.value)}
                                            placeholder="Calle"
                                            className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                        />
                                        {errors.address && <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.address}</p>}
                                    </div>
                                    <div className="col-span-2 sm:col-span-1">
                                        <input
                                            type="text"
                                            value={data.address_number}
                                            onChange={(e) => setData('address_number', e.target.value)}
                                            placeholder="Numero"
                                            className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                        />
                                        {errors.address_number && <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.address_number}</p>}
                                    </div>
                                </div>

                                <div>
                                    <input
                                        type="text"
                                        value={data.department}
                                        onChange={(e) => setData('department', e.target.value)}
                                        placeholder="Departamento / Piso (Opcional)"
                                        className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                    />
                                    {errors.department && <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.department}</p>}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <input
                                            type="text"
                                            value={data.city}
                                            onChange={(e) => setData('city', e.target.value)}
                                            placeholder="Ciudad"
                                            className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                        />
                                        {errors.city && <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.city}</p>}
                                    </div>
                                    <div>
                                        <input
                                            type="text"
                                            value={data.state}
                                            onChange={(e) => setData('state', e.target.value)}
                                            placeholder="Provincia"
                                            className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                        />
                                        {errors.state && <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.state}</p>}
                                    </div>
                                </div>

                                <div>
                                    <input
                                        type="text"
                                        value={data.postal_code}
                                        onChange={(e) => setData('postal_code', e.target.value)}
                                        placeholder="Codigo Postal"
                                        className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                    />
                                    {errors.postal_code && <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.postal_code}</p>}
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full mt-2 py-3.5 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors disabled:opacity-50"
                                >
                                    Continuar
                                </button>
                            </form>
                        </div>
                    </div>

                    {/* Summary */}
                    <div className="w-[340px] shrink-0 hidden lg:block">
                        <CheckoutSummary summary={summary} />
                    </div>
                </div>
            </div>
        </Template>
    );
}
