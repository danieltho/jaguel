export function formatPrice(value, locale = 'es-AR', currency = 'ARS') {
    if (value == null) return '';
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    }).format(value);
}
