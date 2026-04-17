import { usePage } from '@inertiajs/react';
import { WhatsappLogo } from '@phosphor-icons/react';
import Template from '../Shared/components/layout';
import ProductCard from '../Shared/components/ProductCard/ProductCard';
import CategoryBanner from '../Shared/components/CategoryBanner/CategoryBanner';
import Newsletter from '../Shared/components/Newsletter/Newsletter';

export default function Home() {
    const { categoryGroups, featuredProducts } = usePage().props;

    return (
        <Template isHomePage={true}>
            {/* Hero + Category Banners (Bento) */}
            <section className="px-[60px] py-[10px]">
                <div className="max-w-[1320px] mx-auto flex flex-col gap-6">
                    {/* Hero Section */}
                    <div className="relative w-full h-[608px] rounded-[20px] overflow-hidden flex flex-col justify-center pl-6">
                        <img
                            src="/images/hero-home.jpg"
                            alt="Hero"
                            className="absolute inset-0 w-full h-full object-cover pointer-events-none"
                        />
                        <div className="relative z-[1] flex flex-col gap-2.5 max-w-[874px]">
                            <h1 className="text-[40px] font-semibold text-white leading-none">
                                Reconectando con la Tradición
                            </h1>
                            <p className="text-xl font-semibold text-[#f0f0f3]">
                                Todos tus productos favoritos en un solo lugar.
                            </p>
                        </div>
                        <a
                            href="/productos"
                            className="relative z-[1] mt-4 inline-flex w-fit h-10 items-center justify-center px-6 py-2.5 bg-oxido-300 border border-oxido-300 text-oxido-50 text-sm font-medium rounded-lg hover:opacity-90 transition-opacity"
                        >
                            Ver Nuestros Productos
                        </a>
                    </div>

                    {/* Category Banners */}
                    <div className="flex gap-6">
                        {categoryGroups?.map((group) => (
                            <CategoryBanner key={group.id} group={group} />
                        ))}
                    </div>
                </div>
            </section>

            {/* Section Title */}
            <section className="py-[30px]">
                <div className="flex flex-col items-center">
                    <h2 className="text-[32px] font-medium text-neutral-500 text-center">
                        Productos Destacados
                    </h2>
                </div>
            </section>

            {/* Featured Products */}
            <section className="px-[60px] py-8">
                <div className="max-w-[1320px] mx-auto flex flex-col items-center gap-8">
                    <div className="grid grid-cols-4 gap-6 w-full">
                        {featuredProducts?.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                    <a
                        href="/productos"
                        className="inline-flex h-10 items-center justify-center px-6 py-2.5 bg-oxido-300 border border-oxido-300 text-oxido-50 text-sm font-medium rounded-lg hover:opacity-90 transition-opacity"
                    >
                        Ver Todo
                    </a>
                </div>
            </section>

            {/* Custom / Personalization Banner */}
            <section className="px-[60px] py-8">
                <div className="max-w-[1320px] mx-auto relative h-[655px] rounded-[20px] overflow-hidden flex flex-col items-end justify-between p-[50px]">
                    <img
                        src="/images/custom-banner.jpg"
                        alt="Tu toque personal"
                        className="absolute inset-0 w-full h-full object-cover pointer-events-none"
                    />
                    <div className="absolute inset-0 bg-black/20 rounded-[20px]" />
                    <div className="relative z-[1] flex flex-col justify-between h-full w-full">
                        <div className="flex flex-col gap-2.5 max-w-[387px]">
                            <h2 className="text-[40px] font-semibold text-neutral-50 leading-tight">
                                Tu toque personal en nuestra historia
                            </h2>
                            <p className="text-xl font-semibold text-neutral-50">
                                Personalización en productos seleccionados
                            </p>
                        </div>
                        <a
                            href="#"
                            className="inline-flex w-fit h-9 items-center gap-2.5 px-4 py-2.5 bg-oxido-300 border border-oxido-300 text-oxido-50 text-sm font-medium rounded-lg hover:opacity-90 transition-opacity"
                        >
                            <WhatsappLogo size={24} />
                            Más Información
                        </a>
                    </div>
                </div>
            </section>

            {/* Newsletter */}
            <Newsletter />
        </Template>
    );
}
