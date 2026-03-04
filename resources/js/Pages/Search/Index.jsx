import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { MagnifyingGlass } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import ProductCard from '../../Shared/components/ProductCard/ProductCard';

export default function SearchIndex({ products, query }) {
    const [searchTerm, setSearchTerm] = useState(query || '');

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchTerm.trim().length >= 2) {
            router.get('/buscar', { q: searchTerm.trim() }, { preserveState: true });
        }
    };

    const breadcrumbItems = [
        { label: 'Home', href: '/' },
        { label: 'Buscar' },
    ];

    const productList = products?.data || products || [];
    const totalResults = products?.total ?? productList.length;

    return (
        <Template isHomePage={false}>
            <Head title={query ? `Buscar: ${query}` : 'Buscar'} />

            <div className="max-w-[1080px] mx-auto px-4 py-6">
                <Breadcrumb items={breadcrumbItems} />

                {/* Search Input */}
                <form onSubmit={handleSearch} className="mt-6 mb-8">
                    <div className="relative max-w-2xl mx-auto">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            placeholder="Buscar productos..."
                            autoFocus
                            className="w-full px-6 py-4 pl-14 rounded-full bg-neutral-100 border-none text-lg text-neutral-500 placeholder:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-neutral-300"
                        />
                        <MagnifyingGlass
                            size={24}
                            className="absolute left-5 top-1/2 -translate-y-1/2 text-neutral-400"
                        />
                    </div>
                </form>

                {/* Results */}
                {query && (
                    <p className="text-neutral-400 mb-6 text-center">
                        {totalResults} resultado{totalResults !== 1 ? 's' : ''} para &quot;{query}&quot;
                    </p>
                )}

                {productList.length > 0 ? (
                    <div className="grid grid-cols-4 gap-4">
                        {productList.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                ) : query ? (
                    <div className="text-center py-16">
                        <p className="text-xl font-semibold text-neutral-500 mb-2">
                            No encontramos resultados
                        </p>
                        <p className="text-neutral-400">
                            Probá con otros términos de búsqueda.
                        </p>
                    </div>
                ) : null}
            </div>
        </Template>
    );
}
