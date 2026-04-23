import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    Minus,
    Plus,
    CaretLeft,
    CaretDown,
    ShoppingCart,
    MapPin,
    CreditCard,
    ArrowsClockwise,
    X,
} from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import ProductCard from '../../Shared/components/ProductCard/ProductCard';
import ImageGallery from '../../Shared/components/ImageGallery/ImageGallery';
import { formatPrice } from '../../Shared/utils/formatPrice';

function InfoItem({ icon, title, description }) {
    return (
        <div className="flex gap-4 items-center px-2.5 py-2 rounded-2xl flex-1 min-w-0">
            <div className="shrink-0 text-neutral-500">{icon}</div>
            <div className="flex flex-col gap-[5px] text-neutral-500 leading-normal">
                <p className="text-sm font-semibold">{title}</p>
                <p className="text-xs font-normal">{description}</p>
            </div>
        </div>
    );
}

function QuantityStepper({ quantity, onChange }) {
    return (
        <div className="flex gap-4 items-center">
            <button
                type="button"
                onClick={() => onChange(Math.max(1, quantity - 1))}
                aria-label="Restar"
                className="size-[41px] bg-moss-300 rounded-md flex items-center justify-center cursor-pointer hover:opacity-90 transition-opacity"
            >
                <Minus size={20} weight="bold" className="text-oxido-50" />
            </button>
            <div className="w-[112px] h-[41px] bg-oxido-50 rounded-md flex items-center justify-center">
                <span className="text-xs font-medium text-neutral-500">{quantity}</span>
            </div>
            <button
                type="button"
                onClick={() => onChange(quantity + 1)}
                aria-label="Sumar"
                className="size-[41px] bg-moss-300 rounded-md flex items-center justify-center cursor-pointer hover:opacity-90 transition-opacity"
            >
                <Plus size={20} weight="bold" className="text-oxido-50" />
            </button>
        </div>
    );
}

export default function Show({ product, relatedProducts, initialVariantSku }) {
    const [quantity, setQuantity] = useState(1);
    const [selectedVariant, setSelectedVariant] = useState(() => {
        if (!product.variants?.length) return null;
        if (initialVariantSku) {
            const match = product.variants.find((v) => v.sku === initialVariantSku);
            if (match) return match;
        }
        return product.variants[0];
    });

    useEffect(() => {
        if (!selectedVariant?.sku || typeof window === 'undefined') return;
        const desiredPath = `/producto/${product.slug}/${selectedVariant.sku}`;
        if (window.location.pathname !== desiredPath) {
            const url = new URL(window.location.href);
            url.pathname = desiredPath;
            window.history.replaceState({}, '', url.toString());
        }
    }, [selectedVariant?.sku, product.slug]);
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [personalization, setPersonalization] = useState('NO');
    const [personalizationOpen, setPersonalizationOpen] = useState(false);

    const display = selectedVariant
        ? (() => {
            const sold = selectedVariant.price_sold ?? product.price;
            const sales = selectedVariant.price_sales;
            const onPromo = sales != null && sales > 0 && sales < sold;
            return {
                sku: selectedVariant.sku || product.sku,
                originalPrice: sold,
                finalPrice: onPromo ? sales : sold,
                hasDiscount: onPromo,
                discountPercentage: onPromo
                    ? Math.round(((sold - sales) / sold) * 100)
                    : null,
            };
        })()
        : {
            sku: product.sku,
            originalPrice: product.price,
            finalPrice: product.discount?.new_price ?? product.price,
            hasDiscount: !!product.discount,
            discountPercentage: product.discount?.percentage ?? null,
        };

    const sizes = [...new Map(
        product.variants
            .filter((v) => v.size)
            .map((v) => {
                const inStock = product.variants.some(
                    (x) => x.size?.id === v.size.id && (x.stock ?? 0) > 0
                );
                return [v.size.id, { ...v.size, inStock }];
            })
    ).values()];

    const colors = [...new Map(
        product.variants
            .filter((v) => v.color)
            .map((v) => {
                const inStock = product.variants.some(
                    (x) => x.color?.id === v.color.id && (x.stock ?? 0) > 0
                );
                return [v.color.id, { ...v.color, inStock }];
            })
    ).values()];

    const hasComposite = sizes.length > 0 || colors.length > 0;

    const breadcrumbItems = [
        { label: 'Inicio', href: '/' },
        { label: 'Todos los Productos', href: '/productos' },
        ...(product.category?.group
            ? [{ label: product.category.group.name, href: `/productos/${product.category.group.slug}` }]
            : []),
        ...(product.category
            ? [{
                label: product.category.name,
                href: product.category.group
                    ? `/productos/${product.category.group.slug}?category=${product.category.slug}`
                    : null,
            }]
            : []),
        { label: product.name },
    ];

    const handleAddToCart = () => {
        router.post('/carrito/agregar', {
            product_id: product.id,
            variant_id: selectedVariant?.id || null,
            quantity,
            personalization: hasComposite ? null : personalization,
        }, {
            preserveScroll: true,
        });
    };

    return (
        <Template>
            <Head title={product.name} />

            <div className="bg-neutral-50 pb-8">
                {/* Breadcrumb */}
                <div className="px-[60px] py-6">
                    <Breadcrumb items={breadcrumbItems} />
                </div>

                {/* Product Main Section */}
                <div className="flex gap-[123px] items-start justify-center pb-[30px]">
                    {/* Image Panel */}
                    <div className="w-[721px] flex flex-col gap-2.5 items-center pl-[60px]">
                        <div className="w-full">
                            <ImageGallery
                                images={product.images}
                                alt={product.name}
                                activeIndex={activeImageIndex}
                                onIndexChange={setActiveImageIndex}
                            />
                        </div>

                        {product.images.length > 1 && (
                            <div className="flex gap-6 items-center">
                                {product.images.slice(0, 3).map((img, idx) => (
                                    <button
                                        key={img.id}
                                        type="button"
                                        onClick={() => setActiveImageIndex(idx)}
                                        className={`w-[88px] h-[106px] rounded-[10px] overflow-hidden bg-oxido-50 transition-all ${
                                            activeImageIndex === idx
                                                ? 'ring-2 ring-moss-300'
                                                : 'hover:opacity-80'
                                        }`}
                                    >
                                        <img
                                            src={img.thumb || img.url}
                                            alt=""
                                            className="w-full h-full object-cover"
                                        />
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Info Panel */}
                    <div className="w-[536px] flex flex-col gap-6">
                        {/* Volver */}
                        <button
                            type="button"
                            onClick={() => window.history.back()}
                            className="h-9 px-4 py-2.5 rounded-lg border border-oxido-300 text-oxido-300 flex items-center gap-2.5 w-fit cursor-pointer hover:bg-oxido-50 transition-colors"
                        >
                            <CaretLeft size={20} weight="bold" />
                            <span className="text-sm font-medium">Volver</span>
                        </button>

                        {/* Categoría + SKU */}
                        <div className="flex gap-6 items-center">
                            {product.category?.group && (
                                <span className="bg-moss-50 h-[31px] px-1.5 py-1 rounded-[10px] text-base font-semibold text-moss-300 uppercase flex items-center">
                                    {product.category.group.name}
                                </span>
                            )}
                            {product.category && (
                                <span className="bg-moss-50 h-[31px] px-1.5 py-1 rounded-[10px] text-base font-semibold text-carmesi-300 uppercase flex items-center">
                                    {product.category.name}
                                </span>
                            )}
                            {display.sku && (
                                <p className="flex-1 text-sm text-neutral-500">
                                    SKU: {display.sku}
                                </p>
                            )}
                        </div>

                        {/* Title + Description */}
                        <div className="flex flex-col gap-2.5 text-neutral-500 w-full">
                            <h1
                                className={
                                    hasComposite
                                        ? 'text-[32px] font-semibold leading-normal'
                                        : 'text-2xl font-bold leading-normal'
                                }
                            >
                                {product.name}
                            </h1>

                            {product.description && (
                                <div
                                    className="text-sm leading-normal [&_strong]:font-semibold [&_b]:font-semibold [&_p]:mb-0"
                                    dangerouslySetInnerHTML={{ __html: product.description }}
                                />
                            )}
                        </div>

                        {/* Price + Subtax */}
                        <div className="flex flex-col gap-2 justify-center">
                            <div className="flex items-center gap-2">
                                {display.hasDiscount && (
                                    <span
                                        className={`font-normal line-through text-neutral-500 ${
                                            hasComposite ? 'text-[32px]' : 'text-lg'
                                        }`}
                                    >
                                        {formatPrice(display.originalPrice)}
                                    </span>
                                )}
                                <span
                                    className={`font-bold text-neutral-500 ${
                                        hasComposite ? 'text-[40px]' : 'text-[32px]'
                                    }`}
                                >
                                    {formatPrice(display.finalPrice)}
                                </span>
                                {display.hasDiscount && display.discountPercentage && (
                                    <span className="bg-carmesi-100 h-[31px] px-1.5 py-1 rounded-full text-sm font-bold text-carmesi-300 flex items-center">
                                        -{display.discountPercentage}%
                                    </span>
                                )}
                            </div>
                            {product.price_without_tax > 0 && (
                                <p className="text-xs text-neutral-500">
                                    (precio sin impuestos nacionales: {formatPrice(product.price_without_tax)})
                                </p>
                            )}
                        </div>

                        {/* Variants — Talle & Color (compuesto) */}
                        {hasComposite && (
                            <div className="flex gap-8 items-start">
                                {sizes.length > 0 && (
                                    <div className="flex flex-col gap-4">
                                        <p className="text-sm font-bold text-neutral-500">Talle</p>
                                        <div className="flex gap-2.5 items-center flex-wrap">
                                            {sizes.map((size) => {
                                                const isSelected = selectedVariant?.size?.id === size.id;
                                                const base = 'size-8 rounded-md text-xs font-medium flex items-center justify-center cursor-pointer';
                                                const style = !size.inStock
                                                    ? 'bg-neutral-200 text-neutral-300 line-through'
                                                    : isSelected
                                                        ? 'bg-oxido-50 border-2 border-moss-300 text-neutral-500'
                                                        : 'bg-oxido-50 text-neutral-500 hover:border-2 hover:border-moss-300';
                                                return (
                                                    <button
                                                        key={size.id}
                                                        type="button"
                                                        title={!size.inStock ? 'Sin stock' : undefined}
                                                        onClick={() => {
                                                            const exact = product.variants.find(
                                                                (v) => v.size?.id === size.id &&
                                                                    (!selectedVariant?.color || v.color?.id === selectedVariant.color.id)
                                                            );
                                                            const fallback = product.variants.find((v) => v.size?.id === size.id);
                                                            setSelectedVariant(exact || fallback);
                                                        }}
                                                        className={`${base} ${style}`}
                                                    >
                                                        {size.name}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                {colors.length > 0 && (
                                    <div className="flex flex-col gap-4">
                                        <p className="text-sm font-bold text-neutral-500">Color</p>
                                        <div className="flex gap-2.5 items-center flex-wrap">
                                            {colors.map((color) => {
                                                const isSelected = selectedVariant?.color?.id === color.id;
                                                const border = isSelected ? 'ring-2 ring-[#E3E9E2]' : '';
                                                return (
                                                    <button
                                                        key={color.id}
                                                        type="button"
                                                        title={!color.inStock ? `${color.name} - sin stock` : color.name}
                                                        onClick={() => {
                                                            const exact = product.variants.find(
                                                                (v) => v.color?.id === color.id &&
                                                                    (!selectedVariant?.size || v.size?.id === selectedVariant.size.id)
                                                            );
                                                            const fallback = product.variants.find((v) => v.color?.id === color.id);
                                                            setSelectedVariant(exact || fallback);
                                                        }}
                                                        aria-label={color.name}
                                                        className={`size-8 rounded-md relative cursor-pointer ${border}`}
                                                        style={{ backgroundColor: color.hex || '#000' }}
                                                    >
                                                        {!color.inStock && (
                                                            <span className="absolute inset-0 flex items-center justify-center text-white">
                                                                <X size={20} weight="bold" />
                                                            </span>
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Action row — layout differs by product type */}
                        {hasComposite ? (
                            <div className="flex gap-6 items-center">
                                <QuantityStepper quantity={quantity} onChange={setQuantity} />
                                {(() => {
                                    const outOfStock = selectedVariant != null && (selectedVariant.stock ?? 0) <= 0;
                                    return (
                                        <button
                                            type="button"
                                            onClick={handleAddToCart}
                                            disabled={outOfStock}
                                            className={`flex items-center gap-2.5 h-[41px] px-6 py-2.5 bg-oxido-300 border border-oxido-300 rounded-lg transition-opacity ${
                                                outOfStock ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:opacity-90'
                                            }`}
                                        >
                                            <ShoppingCart size={24} className="text-oxido-50" />
                                            <span className="text-xs font-medium text-oxido-50">
                                                {outOfStock ? 'Sin stock' : 'Añadir al Carrito'}
                                            </span>
                                        </button>
                                    );
                                })()}
                            </div>
                        ) : (
                            <>
                                <QuantityStepper quantity={quantity} onChange={setQuantity} />

                                <div className="flex flex-col gap-2.5">
                                    <label className="text-xs text-neutral-500">Personalización</label>
                                    <div className="relative w-[252px]">
                                        <button
                                            type="button"
                                            onClick={() => setPersonalizationOpen((v) => !v)}
                                            className="bg-oxido-50 h-11 w-full px-4 rounded-lg flex items-center justify-between text-xs font-medium text-neutral-500 cursor-pointer"
                                        >
                                            <span>{personalization}</span>
                                            <CaretDown size={20} />
                                        </button>
                                        {personalizationOpen && (
                                            <ul className="absolute z-10 mt-1 w-full bg-oxido-50 rounded-lg shadow-[0_4px_20px_0_rgba(214,216,224,0.25)] overflow-hidden">
                                                {['SI', 'NO'].map((opt) => (
                                                    <li key={opt}>
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setPersonalization(opt);
                                                                setPersonalizationOpen(false);
                                                            }}
                                                            className="w-full h-11 px-4 text-left text-xs font-medium text-neutral-400 hover:bg-moss-50 transition-colors"
                                                        >
                                                            {opt}
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={handleAddToCart}
                                    className="w-[252px] h-[41px] bg-oxido-300 border border-oxido-300 rounded-lg flex items-center justify-center text-xs font-medium text-oxido-50 cursor-pointer hover:opacity-90 transition-opacity"
                                >
                                    Añadir al Carrito
                                </button>
                            </>
                        )}
                    </div>
                </div>

                {/* Info Bar */}
                <div className="px-[60px] py-[30px] flex gap-[60px] items-center justify-center flex-wrap">
                    <InfoItem
                        icon={<MapPin size={24} />}
                        title="Retiro en punto o envío a domicilio"
                        description="Retira en nuestro punto sin cargo o envío a domicilio gratis a partir de $80.000."
                    />
                    <InfoItem
                        icon={<CreditCard size={24} />}
                        title="Mercado Pago o Transferencia Bancaria"
                        description="Paga con Mercado Pago como más te guste o transfiere con dinero en cuenta."
                    />
                    <InfoItem
                        icon={<ArrowsClockwise size={24} />}
                        title="Cambios y Devoluciones"
                        description="Realiza cambios sin cargo."
                    />
                </div>

                {/* Related Products */}
                {relatedProducts.length > 0 && (
                    <div className="px-[60px] py-[30px] flex flex-col items-center gap-4">
                        <div className="self-start pt-[30px] px-4">
                            <h2 className="text-2xl font-medium text-neutral-500">Productos Relacionados</h2>
                        </div>
                        <div className="grid grid-cols-4 gap-6 w-full">
                            {relatedProducts.map((p) => (
                                <ProductCard key={p.id} product={p} />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </Template>
    );
}
