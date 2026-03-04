import { usePage } from '@inertiajs/react';

export const useCategories = () => {
    const { navigation } = usePage().props;

    return {
        categories: navigation || [],
        loading: false,
        error: null,
    };
};
