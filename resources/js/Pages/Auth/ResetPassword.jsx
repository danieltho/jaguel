import { Head, useForm } from '@inertiajs/react';
import Template from '../../Shared/components/layout';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/cuenta/restablecer');
    };

    return (
        <Template isHomePage={false}>
            <Head title="Restablecer Contraseña" />

            <div className="min-h-[calc(100vh-80px)] flex items-center justify-center px-4">
                <div className="w-full max-w-md">
                    <h1 className="text-3xl font-extrabold text-neutral-500 mb-8">
                        Restablecer Contraseña
                    </h1>

                    <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                        <div>
                            <input
                                type="email"
                                value={data.email}
                                readOnly
                                className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-400 focus:outline-none"
                            />
                        </div>

                        <div>
                            <input
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Nueva Contraseña"
                                className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                            />
                            {errors.password && (
                                <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.password}</p>
                            )}
                        </div>

                        <div>
                            <input
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="Confirmar Contraseña"
                                className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3.5 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors disabled:opacity-50 mt-2"
                        >
                            Restablecer Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </Template>
    );
}
