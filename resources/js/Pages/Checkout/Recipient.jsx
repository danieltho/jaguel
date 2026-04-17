import { Head, useForm } from '@inertiajs/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';

export default function Recipient({ customer, deliveryType, summary }) {
    const { data, setData, post, processing, errors } = useForm({
        firstname: customer.firstname || '',
        lastname: customer.lastname || '',
        phone: customer.phone || '',
        address: customer.address || '',
        department: customer.department || '',
        city: customer.city || '',
        state: customer.state || '',
        document_number: customer.document || '',
        document_type: 'DNI',
        wants_factura_a: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/checkout/destinatario');
    };

    return (
        <CheckoutLayout currentStep={3}>
            <Head title="Datos del Destinatario" />
            <div className="max-w-[1320px] mx-auto px-4">
                <div className="flex gap-6">
                    {/* Form */}
                    <div className="flex-1">
                        <BackButton href={deliveryType === 'pickup' ? '/checkout/contacto' : '/checkout/entrega'} />

                        <form onSubmit={handleSubmit} className="mt-6 flex flex-col gap-6">
                            {/* Recipient data */}
                            <div>
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-4">Datos del Destinatario</h2>
                                <div className="flex flex-col gap-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <CheckoutInput
                                            type="text"
                                            value={data.firstname}
                                            onChange={(e) => setData('firstname', e.target.value)}
                                            placeholder="Nombre"
                                            error={errors.firstname}
                                        />
                                        <CheckoutInput
                                            type="text"
                                            value={data.lastname}
                                            onChange={(e) => setData('lastname', e.target.value)}
                                            placeholder="Apellido"
                                            error={errors.lastname}
                                        />
                                    </div>
                                    <CheckoutInput
                                        type="tel"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        placeholder="Telefono"
                                        error={errors.phone}
                                    />
                                    <CheckoutInput
                                        type="text"
                                        value={data.address}
                                        onChange={(e) => setData('address', e.target.value)}
                                        placeholder="Direccion"
                                        error={errors.address}
                                    />
                                    <CheckoutInput
                                        type="text"
                                        value={data.department}
                                        onChange={(e) => setData('department', e.target.value)}
                                        placeholder="Departamento / Piso (Opcional)"
                                    />
                                    <div className="grid grid-cols-2 gap-4">
                                        <CheckoutInput
                                            type="text"
                                            value={data.city}
                                            onChange={(e) => setData('city', e.target.value)}
                                            placeholder="Ciudad"
                                            error={errors.city}
                                        />
                                        <CheckoutInput
                                            type="text"
                                            value={data.state}
                                            onChange={(e) => setData('state', e.target.value)}
                                            placeholder="Provincia"
                                            error={errors.state}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Billing */}
                            <div>
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-4">Datos de Facturacion</h2>
                                <div className="flex flex-col gap-4">
                                    <CheckoutInput
                                        type="text"
                                        value={data.document_number}
                                        onChange={(e) => setData('document_number', e.target.value)}
                                        placeholder="DNI / CUIT"
                                        error={errors.document_number}
                                    />
                                    <label className="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={data.wants_factura_a}
                                            onChange={(e) => {
                                                setData('wants_factura_a', e.target.checked);
                                                if (e.target.checked) {
                                                    setData('document_type', 'CUIT');
                                                } else {
                                                    setData('document_type', 'DNI');
                                                }
                                            }}
                                            className="w-4 h-4 rounded border-neutral-300 text-oxido-300 focus:ring-oxido-300"
                                        />
                                        <span className="text-sm text-neutral-500">Factura A</span>
                                    </label>
                                </div>
                            </div>

                            <PrimaryButton type="submit" disabled={processing}>
                                Continuar
                            </PrimaryButton>
                        </form>
                    </div>

                    {/* Summary sidebar */}
                    <div className="w-[424px] shrink-0 hidden lg:block">
                        <OrderSummary {...summary} />
                    </div>
                </div>
            </div>
        </CheckoutLayout>
    );
}
