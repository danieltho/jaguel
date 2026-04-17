import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Minus, Plus, ArrowLeft } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import ProductCard from '../../Shared/components/ProductCard/ProductCard';
import { formatPrice } from '../../Shared/utils/formatPrice';

function StarRating({ rating = 4.5 }) {
    const fullStars = Math.floor(rating);
    const hasHalf = rating % 1 >= 0.5;

    return (
        <div className="flex items-center gap-3">
            <div className="flex gap-0.5">
                {[...Array(5)].map((_, i) => (
                    <svg key={i} width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M10 1.5l2.47 5.01 5.53.8-4 3.9.94 5.49L10 14.01 5.06 16.7l.94-5.49-4-3.9 5.53-.8L10 1.5z"
                            fill={i < fullStars ? '#FFC633' : (i === fullStars && hasHalf ? '#FFC633' : '#E5E5E5')}
                            stroke="#FFC633"
                            strokeWidth="0.5"
                        />
                    </svg>
                ))}
            </div>
            <span className="text-base text-neutral-500">
                {rating}/<span className="text-neutral-400">5</span>
            </span>
        </div>
    );
}

function QuantitySelector({ quantity, onChange }) {
    return (
        <div className="flex items-center justify-between bg-neutral-100 rounded-full px-5 py-3 w-[147px]">
            <button
                onClick={() => onChange(Math.max(1, quantity - 1))}
                className="text-neutral-500 hover:text-neutral-400 transition-colors"
            >
                <Minus size={20} weight="bold" />
            </button>
            <span className="text-base font-medium text-neutral-500">{quantity}</span>
            <button
                onClick={() => onChange(quantity + 1)}
                className="text-neutral-500 hover:text-neutral-400 transition-colors"
            >
                <Plus size={20} weight="bold" />
            </button>
        </div>
    );
}

function ProductTabs({ product }) {
    const [activeTab, setActiveTab] = useState('details');

    const tabs = [
        { key: 'details', label: 'Product Details' },
        { key: 'reviews', label: 'Rating & Reviews' },
        { key: 'faqs', label: 'FAQs' },
    ];

    return (
        <div>
            <div className="flex border-b border-neutral-100">
                {tabs.map((tab) => (
                    <button
                        key={tab.key}
                        onClick={() => setActiveTab(tab.key)}
                        className={`flex-1 text-center py-3 text-xl transition-colors ${
                            activeTab === tab.key
                                ? 'text-neutral-500 font-semibold border-b-2 border-neutral-500'
                                : 'text-neutral-400'
                        }`}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            <div className="py-8 px-6">
                {activeTab === 'details' && (
                    <div className="flex flex-col gap-6">
                        {product.description && (
                            <div>
                                <div
                                    className="text-base text-neutral-500 leading-relaxed prose prose-sm max-w-none"
                                    dangerouslySetInnerHTML={{ __html: product.description }}
                                />
                            </div>
                        )}

                        <div>
                            <h3 className="text-2xl font-extrabold text-neutral-500 mb-4">
                                Información Adicional
                            </h3>
                            <div className="flex flex-col gap-4 text-base text-neutral-500">
                                {product.dimensions.weight && (
                                    <div className="flex items-center gap-16">
                                        <span className="uppercase">PESO</span>
                                        <span>{product.dimensions.weight / 1000} kg</span>
                                    </div>
                                )}
                                {(product.dimensions.length || product.dimensions.width || product.dimensions.height) && (
                                    <div className="flex items-center gap-16">
                                        <span className="uppercase">DIMENSIONES</span>
                                        <span>
                                            {[product.dimensions.length, product.dimensions.width, product.dimensions.height]
                                                .filter(Boolean)
                                                .join(' × ')}{' '}
                                            cm
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {activeTab === 'reviews' && (
                    <p className="text-neutral-400 text-center py-8">
                        Aún no hay reseñas para este producto.
                    </p>
                )}

                {activeTab === 'faqs' && (
                    <p className="text-neutral-400 text-center py-8">
                        No hay preguntas frecuentes disponibles.
                    </p>
                )}
            </div>
        </div>
    );
}

export default function Show({ product, relatedProducts }) {
    const [quantity, setQuantity] = useState(1);
    const [selectedVariant, setSelectedVariant] = useState(null);
    const [activeImageIndex, setActiveImageIndex] = useState(0);

    const hasDiscount = !!product.discount;
    const finalPrice = hasDiscount ? product.discount.new_price : product.price;

    const breadcrumbItems = [
        { label: 'Home', href: '/' },
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

    const colors = [...new Map(
        product.variants
            .filter((v) => v.color)
            .map((v) => [v.color.id, v.color])
    ).values()];

    const sizes = [...new Map(
        product.variants
            .filter((v) => v.size)
            .map((v) => [v.size.id, v.size])
    ).values()];

    const handleAddToCart = () => {
        router.post('/carrito/agregar', {
            product_id: product.id,
            variant_id: selectedVariant?.id || null,
            quantity,
        }, {
            preserveScroll: true,
        });
    };

    const mainImage = product.images?.[activeImageIndex]?.url || product.images?.[0]?.url;

    return (
        <Template>
            <Head title={product.name} />

            <div className="max-w-[1080px] mx-auto px-4 py-6">
                {/* Breadcrumb */}
                <Breadcrumb items={breadcrumbItems} />

                {/* Back button */}
                <button
                    onClick={() => window.history.back()}
                    className="mt-3 mb-4 text-neutral-500 hover:text-neutral-400 transition-colors"
                >
                    <ArrowLeft size={24} />
                </button>

                {/* Product Main Section */}
                <div className="flex gap-6 mb-12">
                    {/* Images */}
                    <div className="flex gap-4 shrink-0">
                        {/* Thumbnails */}
                        {product.images.length > 1 && (
                            <div className="flex flex-col gap-3">
                                {product.images.map((img, idx) => (
                                    <button
                                        key={img.id}
                                        onClick={() => setActiveImageIndex(idx)}
                                        className={`w-[100px] h-[120px] rounded-2xl overflow-hidden border-2 transition-colors ${
                                            activeImageIndex === idx ? 'border-neutral-500' : 'border-transparent'
                                        }`}
                                    >
                                        <img
                                            src={img.thumb}
                                            alt=""
                                            className="w-full h-full object-cover"
                                        />
                                    </button>
                                ))}
                            </div>
                        )}

                        {/* Main Image */}
                        <div className="w-[383px] h-[530px] rounded-2xl overflow-hidden bg-neutral-100">
                            <img
                                src={mainImage || '/images/img_default.jpg'}
                                alt={product.name}
                                className="w-full h-full object-cover"
                            />
                        </div>
                    </div>

                    {/* Product Info */}
                    <div className="flex flex-col gap-3 flex-1">
                        <h1 className="text-4xl font-extrabold text-neutral-500 leading-tight">
                            {product.name}
                        </h1>

                        <StarRating />

                        {product.description && (
                            <div
                                className="text-base text-neutral-400 leading-relaxed prose prose-sm max-w-none"
                                dangerouslySetInnerHTML={{ __html: product.description }}
                            />
                        )}

                        {/* Price */}
                        <div className="flex items-center gap-3">
                            <span className="text-3xl font-bold text-neutral-500">
                                {formatPrice(finalPrice)}
                            </span>
                            {hasDiscount && (
                                <>
                                    <span className="text-3xl font-bold text-neutral-300 line-through">
                                        {formatPrice(product.price)}
                                    </span>
                                    {product.discount.percentage && (
                                        <span className="px-3.5 py-1.5 rounded-full bg-red-100 text-red-500 text-base font-medium">
                                            -{product.discount.percentage}%
                                        </span>
                                    )}
                                </>
                            )}
                        </div>

                        {/* Variants — Colors */}
                        {colors.length > 0 && (
                            <div className="flex flex-col gap-2 mt-2">
                                <span className="text-sm text-neutral-400">Color</span>
                                <div className="flex gap-2">
                                    {colors.map((color) => (
                                        <button
                                            key={color.id}
                                            onClick={() => {
                                                const variant = product.variants.find(
                                                    (v) => v.color?.id === color.id
                                                );
                                                setSelectedVariant(variant);
                                            }}
                                            className={`w-10 h-10 rounded-full border-2 transition-colors ${
                                                selectedVariant?.color?.id === color.id
                                                    ? 'border-neutral-500'
                                                    : 'border-neutral-100'
                                            }`}
                                            style={{ backgroundColor: color.hex || '#ccc' }}
                                            title={color.name}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Variants — Sizes */}
                        {sizes.length > 0 && (
                            <div className="flex flex-col gap-2 mt-2">
                                <span className="text-sm text-neutral-400">Talle</span>
                                <div className="flex gap-2">
                                    {sizes.map((size) => (
                                        <button
                                            key={size.id}
                                            onClick={() => {
                                                const variant = product.variants.find(
                                                    (v) => v.size?.id === size.id &&
                                                        (!selectedVariant?.color || v.color?.id === selectedVariant.color.id)
                                                );
                                                setSelectedVariant(variant);
                                            }}
                                            className={`px-4 py-2 rounded-full border text-sm font-medium transition-colors ${
                                                selectedVariant?.size?.id === size.id
                                                    ? 'border-neutral-500 bg-neutral-500 text-white'
                                                    : 'border-neutral-300 text-neutral-500 hover:border-neutral-400'
                                            }`}
                                        >
                                            {size.name}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Divider */}
                        <hr className="border-neutral-100 mt-2" />

                        {/* Quantity + Add to Cart */}
                        <div className="flex items-center gap-4">
                            <QuantitySelector quantity={quantity} onChange={setQuantity} />
                            <button
                                onClick={handleAddToCart}
                                className="flex-1 h-[52px] bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors"
                            >
                                Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>

                {/* Tabs */}
                <ProductTabs product={product} />

                {/* Related Products */}
                {relatedProducts.length > 0 && (
                    <div className="mt-12 mb-8">
                        <h2 className="text-3xl font-extrabold text-neutral-500 mb-6">
                            Productos Relacionados
                        </h2>
                        <div className="grid grid-cols-4 gap-4">
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
