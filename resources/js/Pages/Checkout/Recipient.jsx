import { Head, useForm } from '@inertiajs/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';
import {
    SectionHeading,
    Checkbox,
} from '../../Shared/components/CheckoutLayout/CheckoutPrimitives';

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
        <CheckoutLayout currentStep={2}>
            <Head title="Datos del Destinatario" />
            <div className="mx-auto w-full max-w-[1320px] px-15 py-[30px]">
                <form onSubmit={handleSubmit} className="flex flex-col gap-2.5">
                    <BackButton href={deliveryType === 'pickup' ? '/checkout/contacto' : '/checkout/entrega'} />

                    <div className="flex items-start justify-between gap-6">
                        <div className="flex w-[872px] flex-col items-end gap-4">
                            <div className="flex w-full flex-col gap-2.5">
                                <SectionHeading>Datos del Destinatario</SectionHeading>

                                <div className="grid grid-cols-2 gap-2.5">
                                    <CheckoutInput
                                        label="Nombre*"
                                        type="text"
                                        value={data.firstname}
                                        onChange={(e) => setData('firstname', e.target.value)}
                                        placeholder="Nombre"
                                        error={errors.firstname}
                                    />
                                    <CheckoutInput
                                        label="Apellido*"
                                        type="text"
                                        value={data.lastname}
                                        onChange={(e) => setData('lastname', e.target.value)}
                                        placeholder="Apellido"
                                        error={errors.lastname}
                                    />
                                </div>
                                <CheckoutInput
                                    label="Teléfono*"
                                    type="tel"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="+54 9 ..."
                                    error={errors.phone}
                                />
                                <CheckoutInput
                                    label="Dirección*"
                                    type="text"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Calle y número"
                                    error={errors.address}
                                />
                                <CheckoutInput
                                    label="Departamento / Piso (Opcional)"
                                    type="text"
                                    value={data.department}
                                    onChange={(e) => setData('department', e.target.value)}
                                    placeholder="Depto / Piso"
                                />
                                <div className="grid grid-cols-2 gap-2.5">
                                    <CheckoutInput
                                        label="Ciudad*"
                                        type="text"
                                        value={data.city}
                                        onChange={(e) => setData('city', e.target.value)}
                                        placeholder="Ciudad"
                                        error={errors.city}
                                    />
                                    <CheckoutInput
                                        label="Provincia*"
                                        type="text"
                                        value={data.state}
                                        onChange={(e) => setData('state', e.target.value)}
                                        placeholder="Provincia"
                                        error={errors.state}
                                    />
                                </div>
                            </div>

                            <div className="flex w-full flex-col gap-2.5">
                                <SectionHeading>Datos de Facturación</SectionHeading>

                                <CheckoutInput
                                    label="DNI / CUIT*"
                                    type="text"
                                    value={data.document_number}
                                    onChange={(e) => setData('document_number', e.target.value)}
                                    placeholder="DNI o CUIT"
                                    error={errors.document_number}
                                />
                                <Checkbox
                                    id="wants-factura-a"
                                    checked={data.wants_factura_a}
                                    onChange={(e) => {
                                        setData('wants_factura_a', e.target.checked);
                                        setData('document_type', e.target.checked ? 'CUIT' : 'DNI');
                                    }}
                                    label="Factura A"
                                />
                            </div>

                            <PrimaryButton type="submit" disabled={processing} className="w-[280px]">
                                Continuar para el Pago
                            </PrimaryButton>
                        </div>

                        <aside className="w-[424px] shrink-0 px-2.5 py-4">
                            <OrderSummary {...summary} />
                        </aside>
                    </div>
                </form>
            </div>
        </CheckoutLayout>
    );
}
