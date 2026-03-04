import { useState, useCallback } from 'react';
import { usePage, router } from '@inertiajs/react';
import Template from '../../Shared/components/layout';
import ProductCard from '../../Shared/components/ProductCard/ProductCard';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import TagButton from '../../Shared/components/TagButton/TagButton';
import SortDropdown from '../../Shared/components/SortDropdown/SortDropdown';
import { useInfinity } from '../../Shared/hook/useInfinity';

export default function ProductsIndex() {
    const { products, sort, activeGroup, activeCategory, categoryGroups } = usePage().props;

    const [allProducts, setAllProducts] = useState(products.data);
    const [nextPageUrl, setNextPageUrl] = useState(products.next_page_url);
    const [loadingMore, setLoadingMore] = useState(false);

    const hasMore = !!nextPageUrl;

    const loadMore = useCallback(() => {
        if (loadingMore || !nextPageUrl) return;
        setLoadingMore(true);

        router.get(nextPageUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            only: ['products'],
            onSuccess: (page) => {
                const newProducts = page.props.products;
                setAllProducts((prev) => [...prev, ...newProducts.data]);
                setNextPageUrl(newProducts.next_page_url);
                setLoadingMore(false);
            },
            onError: () => setLoadingMore(false),
        });
    }, [nextPageUrl, loadingMore]);

    const sentinelRef = useInfinity({
        hasMore,
        loading: loadingMore,
        onLoadMore: loadMore,
    });

    // Build breadcrumb items
    const breadcrumbItems = [{ label: 'Inicio', href: '/' }];
    if (activeGroup) {
        breadcrumbItems.push({ label: 'Todos los Productos', href: '/productos' });
        breadcrumbItems.push({ label: activeGroup.name });
    } else {
        breadcrumbItems.push({ label: 'Todos los Productos' });
    }

    // Page title
    const pageTitle = activeGroup?.name || 'Todos los Productos';

    // Subcategories (from active group or all groups)
    const subcategories = activeGroup?.categories || [];
    const groupTabs = !activeGroup && categoryGroups
        ? categoryGroups.map((g) => ({
            label: g.name,
            slug: g.slug,
            href: `/productos/${g.slug}`,
        }))
        : [];

    const handleSortChange = (newSort) => {
        const url = activeGroup
            ? `/productos/${activeGroup.slug}`
            : '/productos';
        const params = { sort: newSort };
        if (activeCategory) params.category = activeCategory;

        router.get(url, params, {
            preserveState: false,
        });
    };

    return (
        <Template>
            {/* Breadcrumb */}
            <section className="px-[60px] pt-[24px] pb-[16px]">
                <Breadcrumb items={breadcrumbItems} />
            </section>

            {/* Title + Subcategories */}
            <section className="py-[30px]">
                <div className="flex flex-col items-center gap-4">
                    <h1 className="text-[32px] font-medium text-neutral-500 text-center">
                        {pageTitle}
                    </h1>

                    {/* Group tabs (when showing all products) */}
                    {groupTabs.length > 0 && (
                        <div className="flex gap-2.5">
                            {groupTabs.map((tab) => (
                                <TagButton
                                    key={tab.slug}
                                    label={tab.label}
                                    href={tab.href}
                                />
                            ))}
                        </div>
                    )}

                    {/* Subcategory tabs (when filtering by group) */}
                    {subcategories.length > 0 && (
                        <div className="flex gap-2.5">
                            <TagButton
                                label="Todos"
                                href={`/productos/${activeGroup.slug}`}
                                isActive={!activeCategory}
                            />
                            {subcategories.map((cat) => (
                                <TagButton
                                    key={cat.id}
                                    label={cat.name}
                                    href={`/productos/${activeGroup.slug}?category=${cat.slug}`}
                                    isActive={activeCategory === cat.slug}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {/* Sort + Product Grid */}
            <section className="px-[60px] pb-16">
                <div className="max-w-[1320px] mx-auto">
                    {/* Sort */}
                    <div className="flex justify-end mb-6">
                        <SortDropdown value={sort} onChange={handleSortChange} />
                    </div>

                    {/* Product Grid */}
                    <div className="grid grid-cols-4 gap-6">
                        {allProducts.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>

                    {/* Infinite scroll sentinel */}
                    <div ref={sentinelRef} className="h-4" />

                    {loadingMore && (
                        <div className="flex justify-center py-8">
                            <div className="w-8 h-8 border-2 border-oxido-300 border-t-transparent rounded-full animate-spin" />
                        </div>
                    )}

                    {!hasMore && allProducts.length > 0 && (
                        <p className="text-center text-neutral-400 text-sm py-8">
                            No hay más productos para mostrar
                        </p>
                    )}

                    {allProducts.length === 0 && (
                        <p className="text-center text-neutral-400 text-lg py-16">
                            No se encontraron productos
                        </p>
                    )}
                </div>
            </section>
        </Template>
    );
}
