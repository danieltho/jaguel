import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeSlash } from '@phosphor-icons/react';
import { useState } from 'react';
import Template from '../../Shared/components/layout';

export default function Login() {
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/cuenta/login');
    };

    return (
        <Template isHomePage={false}>
            <Head title="Iniciar Sesión" />

            <div className="min-h-[calc(100vh-80px)] flex">
                {/* Left — Image */}
                <div className="hidden lg:block w-1/2 bg-neutral-100">
                    <div className="w-full h-full bg-moss-50 flex items-center justify-center">
                        <span className="text-6xl font-extrabold text-moss-200 opacity-50">EL JAGÜEL</span>
                    </div>
                </div>

                {/* Right — Form */}
                <div className="w-full lg:w-1/2 flex items-center justify-center px-8">
                    <div className="w-full max-w-md">
                        <h1 className="text-3xl font-extrabold text-neutral-500 mb-8">Inicia sesión</h1>

                        <form onSubmit={handleSubmit} className="flex flex-col gap-5">
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

                            <div className="relative">
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Contraseña"
                                    className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300 pr-12"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-4 top-1/2 -translate-y-1/2 text-neutral-400"
                                >
                                    {showPassword ? <EyeSlash size={20} /> : <Eye size={20} />}
                                </button>
                                {errors.password && (
                                    <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.password}</p>
                                )}
                            </div>

                            <div className="text-right">
                                <Link
                                    href="/cuenta/recuperar"
                                    className="text-sm text-neutral-400 hover:text-neutral-500 transition-colors"
                                >
                                    ¿Olvidaste tu contraseña?
                                </Link>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3.5 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors disabled:opacity-50"
                            >
                                Iniciar Sesión
                            </button>
                        </form>

                        <p className="text-center text-neutral-400 mt-6">
                            ¿Aún no tienes una cuenta?{' '}
                            <Link
                                href="/cuenta/registro"
                                className="text-neutral-500 font-medium hover:underline"
                            >
                                Crea una cuenta
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </Template>
    );
}
