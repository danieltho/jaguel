import { Link } from '@inertiajs/react';
import { formatPrice } from '../../utils/formatPrice';

export default function ProductCard({ product }) {
    const { name, slug, price, discount, image, category } = product;

    const hasDiscount = !!discount;
    const finalPrice = hasDiscount ? discount.new_price : price;

    return (
        <Link href={`/producto/${slug}`} className="block group">
            <article className="w-full rounded-2xl p-4 flex flex-col gap-3">
                <div className="relative aspect-square rounded-3xl overflow-hidden bg-neutral-100">
                    <img
                        src={image || '/images/img_default.jpg'}
                        alt={name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    />
                </div>

                <div className="flex flex-col gap-2 items-start w-full">
                    <h3 className="text-lg font-semibold text-neutral-500 w-full line-clamp-2">
                        {name}
                    </h3>

                    <div className="flex items-center gap-2 w-full">
                        {hasDiscount && (
                            <span className="text-base text-neutral-500 line-through font-medium">
                                {formatPrice(price)}
                            </span>
                        )}
                        <span className="text-2xl font-bold text-neutral-500">
                            {formatPrice(finalPrice)}
                        </span>
                        {hasDiscount && discount.percentage && (
                            <span className="inline-flex items-center justify-center px-1.5 py-1 rounded-full bg-carmesi-100 text-carmesi-300 text-sm font-bold">
                                -{discount.percentage}%
                            </span>
                        )}
                    </div>

                    {category && (
                        <span className="inline-flex h-7.75 items-center justify-center px-2.5 py-1 rounded-[10px] bg-moss-50 text-moss-300 text-sm font-bold uppercase">
                            {category}
                        </span>
                    )}
                </div>
            </article>
        </Link>
    );
}
