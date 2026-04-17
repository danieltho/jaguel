import { Link } from '@inertiajs/react';
import { formatPrice } from '../../utils/formatPrice';

export default function ProductCard({ product }) {
    const { name, slug, price, discount, image, category } = product;

    const hasDiscount = !!discount;
    const finalPrice = hasDiscount ? discount.new_price : price;

    return (
        <Link href={`/producto/${slug}`} className="block group">
            <article className="w-full bg-white rounded-2xl p-4 flex flex-col gap-3">
                <div className="relative aspect-square rounded-[20px] overflow-hidden bg-neutral-100">
                    <img
                        src={image || '/images/img_default.jpg'}
                        alt={name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    />
                </div>

                <div className="flex flex-col gap-1 flex-1">
                    <h3 className="text-xl font-semibold text-[#202322] leading-snug line-clamp-2">
                        {name}
                    </h3>

                    {category && (
                        <span className="inline-flex w-fit px-1.5 py-1 rounded-[10px] bg-moss-50 text-moss-300 text-base font-semibold uppercase">
                            {category}
                        </span>
                    )}

                    <div className="flex items-center gap-2 mt-auto pt-2">
                        {hasDiscount && (
                            <span className="text-2xl text-[#202322] line-through font-medium">
                                {formatPrice(price)}
                            </span>
                        )}
                        <span className="text-2xl font-semibold text-[#202322]">
                            {formatPrice(finalPrice)}
                        </span>
                        {hasDiscount && discount.percentage && (
                            <span className="px-1.5 py-1 rounded-full bg-carmesi-100 text-carmesi-300 text-base font-semibold">
                                -{discount.percentage}%
                            </span>
                        )}
                    </div>
                </div>
            </article>
        </Link>
    );
}
