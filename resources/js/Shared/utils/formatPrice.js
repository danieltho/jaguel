export function formatPrice(cents, locale = 'es-AR', currency = 'ARS') {
    if (cents == null) return '';
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    }).format(cents / 100);
}
