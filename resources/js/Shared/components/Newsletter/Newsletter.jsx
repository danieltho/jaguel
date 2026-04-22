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
        <section className="px-15 py-12">
            <div className="mx-auto flex max-w-330 flex-col items-center justify-between gap-8 px-6 md:flex-row">
                <div className="flex max-w-142.25 flex-col gap-2.5">
                    <h2 className="text-[32px] font-semibold text-oxido-300">
                        Suscríbete para enterarte de todo
                    </h2>
                    <p className="text-[20px] font-normal text-[#383838]">
                        Ingrese su correo electrónico a continuación para recibir actualizaciones diarias.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="flex gap-6">
                    <label className="flex h-14 w-106 items-center gap-4 rounded-lg border border-neutral-300 bg-neutral-100 px-4 py-3 focus-within:border-oxido-300 transition-colors">
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
                        className="h-14 w-50 cursor-pointer rounded-lg border border-oxido-300 bg-oxido-300 px-8 py-2.5 text-xs font-medium text-oxido-50 transition-opacity hover:opacity-90"
                    >
                        Suscribirse
                    </button>
                </form>
            </div>
        </section>
    );
}
