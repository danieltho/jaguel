import { useState } from 'react';
import { User } from '@phosphor-icons/react';

export default function Newsletter() {
    const [email, setEmail] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        // TODO: integrate with backend
        setEmail('');
    };

    return (
        <section className="bg-neutral-50 py-20 px-[60px]">
            <div className="max-w-[1320px] mx-auto flex flex-col md:flex-row items-center justify-between px-6 gap-8">
                <div className="max-w-[569px]">
                    <h2 className="text-[32px] font-semibold text-oxido-300 leading-tight">
                        Suscríbete para enterarte de todo
                    </h2>
                    <p className="text-xl font-normal text-[#383838] mt-2">
                        Ingrese su correo electrónico a continuación para recibir actualizaciones diarias.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="flex gap-6">
                    <div className="relative">
                        <User size={24} className="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-300" />
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="ejemplo@correo.com"
                            className="h-14 w-[424px] pl-12 pr-4 rounded-lg bg-neutral-100 border border-neutral-300 text-sm text-neutral-500 placeholder:text-neutral-300 outline-none focus:border-oxido-300 transition-colors"
                            required
                        />
                    </div>
                    <button
                        type="submit"
                        className="h-14 w-[200px] bg-oxido-300 text-oxido-50 text-sm font-medium rounded-lg hover:opacity-90 transition-opacity cursor-pointer"
                    >
                        Suscribirse
                    </button>
                </form>
            </div>
        </section>
    );
}
