import { useState, useCallback } from 'react';
import { usePage, router } from '@inertiajs/react';
import Template from '../../Shared/components/layout';
import ProductCard from '../../Shared/components/ProductCard/ProductCard';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import TagButton from '../../Shared/components/TagButton/TagButton';
import CategorySelect from '../../Shared/components/CategorySelect/CategorySelect';
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

    // Opciones para el selector desplegable en mobile.
    const groupSelectOptions = [
        { label: 'Todos los Productos', href: '/productos', isActive: !activeGroup },
        ...groupTabs.map((tab) => ({ label: tab.label, href: tab.href, isActive: false })),
    ];
    const subcategorySelectOptions = activeGroup
        ? [
            { label: 'Todos', href: `/productos/${activeGroup.slug}`, isActive: !activeCategory },
            ...subcategories.map((cat) => ({
                label: cat.name,
                href: `/productos/${activeGroup.slug}/${cat.slug}`,
                isActive: activeCategory === cat.slug,
            })),
        ]
        : [];

    const handleSortChange = (newSort) => {
        let url = '/productos';
        if (activeGroup) {
            url = `/productos/${activeGroup.slug}`;
            if (activeCategory) url += `/${activeCategory}`;
        }

        router.get(url, { sort: newSort }, {
            preserveState: false,
        });
    };

    return (
        <Template>
            <div className="mx-auto flex max-w-360 flex-col gap-6 px-4 sm:px-8 lg:px-15">
                {/* Breadcrumb */}
                <section className="py-6">
                    <Breadcrumb items={breadcrumbItems} />
                </section>

                {/* Title + Category tabs */}
                <section className="flex flex-col items-center justify-center gap-4 p-4">
                    <h1 className="text-center text-2xl font-bold text-neutral-500">
                        {pageTitle}
                    </h1>

                    {groupTabs.length > 0 && (
                        <>
                            {/* Mobile: selector desplegable */}
                            <div className="w-full max-w-xs sm:hidden">
                                <CategorySelect options={groupSelectOptions} />
                            </div>
                            {/* Desktop: tags */}
                            <div className="hidden flex-wrap items-center justify-center gap-2.5 sm:flex">
                                {groupTabs.map((tab) => (
                                    <TagButton
                                        key={tab.slug}
                                        label={tab.label}
                                        href={tab.href}
                                    />
                                ))}
                            </div>
                        </>
                    )}

                    {subcategories.length > 0 && (
                        <>
                            {/* Mobile: selector desplegable */}
                            <div className="w-full max-w-xs sm:hidden">
                                <CategorySelect options={subcategorySelectOptions} />
                            </div>
                            {/* Desktop: tags */}
                            <div className="hidden flex-wrap items-center justify-center gap-2.5 sm:flex">
                                <TagButton
                                    label="Todos"
                                    href={`/productos/${activeGroup.slug}`}
                                    isActive={!activeCategory}
                                />
                                {subcategories.map((cat) => (
                                    <TagButton
                                        key={cat.id}
                                        label={cat.name}
                                        href={`/productos/${activeGroup.slug}/${cat.slug}`}
                                        isActive={activeCategory === cat.slug}
                                    />
                                ))}
                            </div>
                        </>
                    )}
                </section>

                {/* Sort + Product Grid */}
                <section className="flex flex-col gap-6 pb-16">
                    <div className="flex items-center justify-end">
                        <SortDropdown value={sort} onChange={handleSortChange} />
                    </div>

                    <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-10 sm:gap-y-16">
                        {allProducts.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>

                    <div ref={sentinelRef} className="h-4" />

                    {loadingMore && (
                        <div className="flex justify-center py-8">
                            <div className="h-8 w-8 animate-spin rounded-full border-2 border-oxido-300 border-t-transparent" />
                        </div>
                    )}

                    {allProducts.length === 0 && (
                        <p className="py-16 text-center text-lg text-neutral-400">
                            No se encontraron productos
                        </p>
                    )}
                </section>
            </div>
        </Template>
    );
}
