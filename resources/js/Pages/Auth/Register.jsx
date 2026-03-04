import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeSlash } from '@phosphor-icons/react';
import { useState } from 'react';
import Template from '../../Shared/components/layout';

export default function Register() {
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        firstname: '',
        lastname: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/cuenta/registro');
    };

    return (
        <Template isHomePage={false}>
            <Head title="Crear Cuenta" />

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
                        <h1 className="text-3xl font-extrabold text-neutral-500 mb-8">Crear Cuenta</h1>

                        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                            <div className="flex gap-3">
                                <div className="flex-1">
                                    <input
                                        type="text"
                                        value={data.firstname}
                                        onChange={(e) => setData('firstname', e.target.value)}
                                        placeholder="Nombre"
                                        className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                    />
                                    {errors.firstname && (
                                        <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.firstname}</p>
                                    )}
                                </div>
                                <div className="flex-1">
                                    <input
                                        type="text"
                                        value={data.lastname}
                                        onChange={(e) => setData('lastname', e.target.value)}
                                        placeholder="Apellido"
                                        className="w-full px-5 py-3.5 rounded-full bg-neutral-100 border-none text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                                    />
                                    {errors.lastname && (
                                        <p className="text-carmesi-300 text-sm mt-1 pl-4">{errors.lastname}</p>
                                    )}
                                </div>
                            </div>

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
                                Crear Cuenta
                            </button>
                        </form>

                        <p className="text-center text-neutral-400 mt-6">
                            ¿Ya tienes una cuenta?{' '}
                            <Link
                                href="/cuenta/login"
                                className="text-neutral-500 font-medium hover:underline"
                            >
                                Iniciar Sesión
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </Template>
    );
}
