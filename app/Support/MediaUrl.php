<?php

namespace App\Support;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaUrl
{
    /**
     * URL de la primera conversión ya generada, con fallback al original.
     * Las conversiones se generan en cola: durante unos segundos tras subir
     * una imagen (o durante un regenerate) pueden no existir todavía.
     */
    public static function resolve(?Media $media, string ...$conversions): string
    {
        if (! $media) {
            return '';
        }

        foreach ($conversions as $conversion) {
            if ($media->hasGeneratedConversion($conversion)) {
                return $media->getUrl($conversion);
            }
        }

        return $media->getUrl();
    }

    public static function firstFor(?HasMedia $model, string $collection, string ...$conversions): string
    {
        return self::resolve($model?->getFirstMedia($collection), ...$conversions);
    }
}
