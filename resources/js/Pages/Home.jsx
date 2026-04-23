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
            
                {/* Hero Section */}
                <section className="relative -mt-25 flex h-152 w-full flex-col justify-center gap-6 overflow-hidden px-15 pt-25">
                    <img
                        src="/images/hero-home.jpg"
                        alt=""
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-0 size-full object-cover"
                    />
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-0 bg-linear-to-b from-black/10 from-38% to-black/60"
                    />
                    <div className="relative flex w-md flex-col gap-2.5">
                        <h1 className="text-[32px] font-bold text-white">
                            Tienda de Mates y Cuchillería Artesanal
                        </h1>
                        <p className="text-lg font-semibold text-neutral-50">
                            Descubre nuestra colección premium de mates, cuchillos y accesorios en un solo lugar.
                        </p>
                    </div>
                    <a
                        href="/productos"
                        className="relative inline-flex h-10 w-fit items-center justify-center rounded-lg border border-oxido-300 bg-oxido-300 px-6 py-2.5 text-xs font-medium text-oxido-50 transition-opacity hover:opacity-90"
                    >
                        Explorar Catálogo
                    </a>
                </section>

                {/* Categories */}
                <section className="">
                    <div className="mx-auto flex items-center">
                        {categoryGroups?.map((group) => (
                            <CategoryBanner key={group.id} group={group} />
                        ))}
                    </div>
                </section>
            
                {/* Productos Destacados */}
                <section className="pt-12">
                    <div className="mx-auto flex flex-col items-center gap-4">
                        <h2 className="text-center text-2xl font-bold text-carmesi-300">
                            Productos Destacados
                        </h2>
                        <div className="flex w-full items-start gap-6">
                            {featuredProducts?.map((product) => (
                                <div key={product.id} className="flex-1 min-w-0">
                                    <ProductCard product={product} />
                                </div>
                            ))}
                        </div>
                        <a
                            href="/productos"
                            className="inline-flex h-10 items-center justify-center rounded-lg border border-oxido-300 bg-oxido-300 px-6 py-2.5 text-xs font-medium text-oxido-50 transition-opacity hover:opacity-90"
                        >
                            Descubre la Colección
                        </a>
                    </div>
                </section>

                {/* Custom / Personalization Banner */}
                <section className="pt-12">
                    <div className="relative mx-auto flex h-163.75 flex-col justify-between overflow-hidden p-12.5">
                        <img
                            src="/images/custom-banner.jpg"
                            alt=""
                            aria-hidden="true"
                            className="pointer-events-none absolute inset-0 size-full object-cover"
                        />
                        <div
                            aria-hidden="true"
                            className="pointer-events-none absolute inset-0 bg-black/20"
                        />
                        <div className="relative flex max-w-96.75 flex-col gap-2.5 text-neutral-50">
                            <h2 className="text-[32px] font-bold">
                                Tu toque personal en nuestra historia
                            </h2>
                            <p className="text-lg font-semibold">
                                Personalización en productos seleccionados
                            </p>
                        </div>
                        <a
                            href="#"
                            className="relative inline-flex h-9 w-50.75 items-center justify-center gap-2.5 rounded-lg border border-oxido-300 bg-oxido-300 px-4 py-2.5 text-sm font-medium text-oxido-50 transition-opacity hover:opacity-90"
                        >
                            <WhatsappLogo size={24} />
                            Más Información
                        </a>
                    </div>
                </section>

                {/* Newsletter */}
                <Newsletter />
            
        </Template>
    );
}
