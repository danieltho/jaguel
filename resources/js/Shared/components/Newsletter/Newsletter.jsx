import { useState } from 'react';

export default function Newsletter() {
    const [email, setEmail] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        // TODO: integrate with backend
        setEmail('');
    };

    return (
        <section className="bg-neutral-50 py-20 px-[60px]">
            <div className="max-w-[1320px] mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <h2 className="text-[32px] font-medium text-neutral-500 leading-tight">
                        Suscríbete para enterarte de todo
                    </h2>
                    <p className="text-sm text-neutral-400 mt-2">
                        Recibí novedades, ofertas exclusivas y más.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="flex gap-6">
                    <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="Tu correo electrónico"
                        className="h-14 w-[424px] px-4 rounded-lg bg-neutral-100 border border-neutral-300 text-sm text-neutral-500 placeholder:text-neutral-300 outline-none focus:border-oxido-300 transition-colors"
                        required
                    />
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
