import { InstagramLogo, WhatsappLogo } from '@phosphor-icons/react';
import { useCategoryContext } from '../../context/CategoryContext';

export default function Footer() {
    const { categories } = useCategoryContext();

    return (
        <footer className="bg-moss-200 overflow-hidden">
            <div className="max-w-[1440px] mx-auto pt-[72px] px-[60px]">
                <div className="flex justify-between">
                    {/* Contact info */}
                    <div className="flex gap-[104px]">
                        <div className="text-neutral-50">
                            <p className="text-xl font-medium">
                                Calle 37 N° 1242
                            </p>
                            <p className="text-xl font-medium">
                                Miramar, Buenos Aires
                            </p>
                            <div className="mt-4 flex flex-col gap-2.5">
                                <p className="text-xl font-medium">+54 (226) 352 4989</p>
                                <p className="text-base font-medium">eljaguel.miramar@gmail.com</p>
                            </div>
                        </div>
                    </div>

                    {/* Navigation */}
                    <div className="flex gap-[151px]">
                        <div className="flex flex-col gap-6">
                            <h3 className="text-lg font-semibold text-white">Inicio</h3>
                            <div className="flex flex-col gap-2.5">
                                <a href="/" className="text-base font-medium text-white opacity-65 hover:opacity-100 transition-opacity">
                                    Nosotros
                                </a>
                                <a href="#" className="text-base font-medium text-white opacity-65 hover:opacity-100 transition-opacity">
                                    Carrito
                                </a>
                            </div>
                        </div>

                        <div className="flex flex-col gap-6">
                            <h3 className="text-lg font-semibold text-white">Categoría</h3>
                            <div className="grid grid-cols-2 gap-x-[60px] gap-y-2.5">
                                {categories.map((cat) => (
                                    <a
                                        key={cat.id}
                                        href={cat.path}
                                        className="text-base font-medium text-white opacity-65 hover:opacity-100 transition-opacity"
                                    >
                                        {cat.name}
                                    </a>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Bottom bar */}
                <div className="flex items-center justify-between mt-12 pb-6">
                    <div className="flex gap-4">
                        <a href="#" className="w-[35px] h-[35px] rounded-full border border-white flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                            <InstagramLogo size={20} />
                        </a>
                        <a href="#" className="w-[35px] h-[35px] rounded-full border border-white flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                            <WhatsappLogo size={20} />
                        </a>
                    </div>
                    <p className="text-sm text-white">
                        A product of <span className="underline">Ophelia Studio</span>
                    </p>
                    <p className="text-sm text-white">
                        &copy; 2026 El Jagüel. All rights reserved.
                    </p>
                </div>

                {/* Wordmark */}
                <div className="mt-4 mb-[-20px]">
                    <p className="text-[120px] font-semibold text-white/20 leading-none tracking-wider text-center">
                        EL JAGÜEL
                    </p>
                </div>
            </div>
        </footer>
    );
}
