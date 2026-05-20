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
        <section className="px-4 py-10 sm:px-8 lg:px-15 lg:py-12">
            <div className="mx-auto flex w-full max-w-330 flex-col items-center justify-between gap-8 md:flex-row md:px-6">
                <div className="flex w-full max-w-142.25 flex-col gap-2.5 text-center md:text-left">
                    <h2 className="text-2xl font-semibold text-oxido-300 sm:text-3xl lg:text-[32px]">
                        Suscríbete para enterarte de todo
                    </h2>
                    <p className="text-base font-normal text-[#383838] sm:text-lg lg:text-[20px]">
                        Ingrese su correo electrónico a continuación para recibir actualizaciones diarias.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="flex w-full flex-col gap-3 sm:flex-row sm:gap-6 md:w-auto">
                    <label className="flex h-14 w-full items-center gap-4 rounded-lg border border-neutral-300 bg-neutral-100 px-4 py-3 focus-within:border-oxido-300 transition-colors sm:w-auto sm:flex-1 md:w-106">
                        <User size={24} className="shrink-0 text-neutral-500" />
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="ejemplo@correo.com"
                            required
                            className="flex-1 min-w-0 bg-transparent text-xs text-neutral-500 placeholder:text-neutral-300 outline-none"
                        />
                    </label>
                    <button
                        type="submit"
                        className="h-14 w-full cursor-pointer rounded-lg border border-oxido-300 bg-oxido-300 px-8 py-2.5 text-xs font-medium text-oxido-50 transition-opacity hover:opacity-90 sm:w-50 sm:shrink-0"
                    >
                        Suscribirse
                    </button>
                </form>
            </div>
        </section>
    );
}
