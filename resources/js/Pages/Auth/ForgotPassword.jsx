import { Head, Link, useForm, usePage } from '@inertiajs/react';
import Template from '../../Shared/components/layout';

export default function ForgotPassword() {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/cuenta/recuperar');
    };

    return (
        <Template isHomePage={false}>
            <Head title="Recuperar Contraseña" />

            <div className="min-h-[calc(100vh-80px)] flex items-center justify-center px-4">
                <div className="w-full max-w-md">
                    <h1 className="text-3xl font-extrabold text-neutral-500 mb-3">
                        ¿Olvidaste tu contraseña?
                    </h1>
                    <p className="text-neutral-400 mb-8">
                        Ingresá tu email y te enviaremos un enlace para restablecer tu contraseña.
                    </p>

                    {flash?.success && (
                        <div className="bg-green-50 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
                            {flash.success}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                        <div>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="Email"
                                className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                            />
                            {errors.email && (
                                <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.email}</p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3.5 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors disabled:opacity-50"
                        >
                            Enviar Enlace
                        </button>
                    </form>

                    <p className="text-center text-neutral-400 mt-6">
                        <Link href="/cuenta/login" className="text-neutral-500 font-medium hover:underline">
                            Volver a Iniciar Sesión
                        </Link>
                    </p>
                </div>
            </div>
        </Template>
    );
}
