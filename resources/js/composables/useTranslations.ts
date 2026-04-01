import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Returns a translation function that looks up the current locale's strings.
 * Falls back to the key if the translation is missing.
 */
export function useTranslations(): (key: string, params?: Record<string, string | number>) => string {
    const page = usePage();
    const translations = computed(() => (page.props.translations as Record<string, string>) ?? {});

    return (key: string, params?: Record<string, string | number>): string => {
        let value = translations.value[key] ?? key;

        if (params) {
            for (const [k, v] of Object.entries(params)) {
                const s = String(v);
                value = value.replaceAll(`:${k}`, s).replaceAll(`{${k}}`, s);
            }
        }

        return value;
    };
}
