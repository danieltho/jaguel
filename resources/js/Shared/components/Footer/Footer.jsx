import { Link } from '@inertiajs/react';
import { InstagramLogo, WhatsappLogo } from '@phosphor-icons/react';
import { useCategoryContext } from '../../context/CategoryContext';

export default function Footer() {
    const { categories, baseUrl } = useCategoryContext();

    return (
        <footer className="bg-moss-200 text-white">
            <div className="mx-auto flex max-w-360 flex-col items-center gap-4 px-15 py-12">
                <div className="flex w-full flex-col items-start justify-between gap-10 md:flex-row md:gap-6">
                    <div className="flex flex-col gap-6 self-stretch md:justify-between">
                        <p className="font-extrabold tracking-wider text-[32px] leading-none">
                            EL JAGÜEL
                        </p>
                        <div className="flex gap-3">
                            <a
                                href="#"
                                aria-label="WhatsApp"
                                className="flex size-8.75 items-center justify-center rounded-full border border-white transition-colors hover:bg-white/10"
                            >
                                <WhatsappLogo size={20} />
                            </a>
                            <a
                                href="#"
                                aria-label="Instagram"
                                className="flex size-8.75 items-center justify-center rounded-full border border-white transition-colors hover:bg-white/10"
                            >
                                <InstagramLogo size={20} />
                            </a>
                        </div>
                    </div>

                    <div className="flex flex-col gap-4 text-base font-medium text-neutral-50">
                        <div>
                            <p>Calle 37 N° 1242</p>
                            <p>Miramar, Buenos Aires</p>
                        </div>
                        <div className="flex flex-col gap-1">
                            <p>+54 9 223 312-3981</p>
                            <p>eljaguelcriollo@gmail.com</p>
                        </div>
                    </div>

                    <div className="hidden items-start gap-12 text-sm lg:flex">
                        <div className="flex w-18.25 flex-col gap-4">
                            <Link href="/" className="font-medium">
                                Inicio
                            </Link>
                            <div className="flex flex-col gap-2.5 font-normal opacity-70">
                                <a href="/nosotros">Nosotros</a>
                                <Link href="/carrito">Carrito</Link>
                            </div>
                        </div>

                        {categories.length > 0 && (
                            <div className="flex flex-col gap-4">
                                <p className="font-medium">Categoría</p>
                                <div className="flex flex-col gap-2.5 font-normal opacity-70">
                                    {categories.map((cat) => (
                                        <Link
                                            key={cat.id}
                                            href={`${baseUrl}${cat.path}`}
                                        >
                                            {cat.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <p className="w-full text-center text-xs">
                    &copy; 2026 El Jaguel. All rights reserved
                </p>
            </div>
        </footer>
    );
}
