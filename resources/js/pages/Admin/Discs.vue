<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard } from '@/routes';

const t = useTranslations();

type DiscRow = {
    id: number;
    status: 'lost' | 'found';
    occurredAt: string | null;
    manufacturer: string | null;
    modelName: string | null;
    plasticType: string | null;
    backText: string | null;
    conditionEstimate: string | null;
    active: boolean;
    matchLifecycle: string | null;
    colors: string[];
    owner: {
        id: number | null;
        name: string | null;
        username: string | null;
        email: string | null;
    };
    createdAt: string | null;
};

type Filters = {
    q: string | null;
    status: string | null;
    active: string | null;
    lifecycle: string | null;
    sort: string;
    dir: string;
};

const props = defineProps<{
    filters: Filters;
    discs: {
        data: DiscRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta?: { total?: number; from?: number | null; to?: number | null };
        total?: number;
        from?: number | null;
        to?: number | null;
    };
}>();

const breadcrumbs = computed(() => [
    { title: t('My Profile'), href: dashboard().url },
    { title: t('Admin'), href: '/admin/discs' },
]);

const form = reactive({
    q: props.filters.q ?? '',
    status: props.filters.status ?? '',
    active: props.filters.active ?? '',
    lifecycle: props.filters.lifecycle ?? '',
    sort: props.filters.sort ?? 'created_at',
    dir: props.filters.dir ?? 'desc',
});

const pageFrom = computed(() => props.discs.meta?.from ?? props.discs.from ?? 0);
const pageTo = computed(() => props.discs.meta?.to ?? props.discs.to ?? 0);
const pageTotal = computed(() => props.discs.meta?.total ?? props.discs.total ?? 0);

function submitFilters(): void {
    router.get(
        '/admin/discs',
        {
            q: form.q || undefined,
            status: form.status || undefined,
            active: form.active || undefined,
            lifecycle: form.lifecycle || undefined,
            sort: form.sort || undefined,
            dir: form.dir || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function clearFilters(): void {
    form.q = '';
    form.status = '';
    form.active = '';
    form.lifecycle = '';
    form.sort = 'created_at';
    form.dir = 'desc';
    submitFilters();
}

function updateDisc(discId: number, payload: Record<string, unknown>): void {
    if (!window.confirm(t('Are you sure?'))) return;

    router.patch(`/admin/discs/${discId}`, payload, {
        preserveScroll: true,
        preserveState: true,
    });
}

function discLabel(disc: DiscRow): string {
    const parts = [disc.plasticType, disc.modelName, disc.manufacturer].filter(Boolean);
    return parts.length ? String(parts.join(' ')) : `#${disc.id}`;
}

function ownerLabel(disc: DiscRow): string {
    return disc.owner.username || disc.owner.name || disc.owner.email || '—';
}
</script>

<template>
    <Head :title="t('Admin') + ' • ' + t('All discs')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-6xl px-4 py-8">
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">
                            {{ t('All discs') }}
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ t('Admin can search and edit disc status') }}
                        </p>
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ pageFrom }}–{{ pageTo }}
                        / {{ pageTotal }}
                    </div>
                </div>

                <form class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-6" @submit.prevent="submitFilters">
                    <input
                        v-model="form.q"
                        type="text"
                        class="md:col-span-2 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm"
                        :placeholder="t('Search')"
                    />

                    <select v-model="form.status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
                        <option value="">{{ t('Status') }}</option>
                        <option value="lost">{{ t('Lost') }}</option>
                        <option value="found">{{ t('Found') }}</option>
                    </select>

                    <select v-model="form.active" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
                        <option value="">{{ t('Active') }}</option>
                        <option value="1">{{ t('Active') }}</option>
                        <option value="0">{{ t('Inactive') }}</option>
                    </select>

                    <select v-model="form.lifecycle" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
                        <option value="">{{ t('Match lifecycle') }}</option>
                        <option value="confirmed">{{ t('Confirmed') }}</option>
                        <option value="handed_over">{{ t('Handed over') }}</option>
                    </select>

                    <div class="flex gap-2 md:col-span-1">
                        <button type="submit" class="h-10 flex-1 rounded-lg bg-primary px-4 text-sm font-bold text-primary-foreground hover:opacity-90">
                            {{ t('Search') }}
                        </button>
                        <button type="button" class="h-10 rounded-lg border border-input bg-muted/50 px-4 text-sm font-bold text-foreground hover:bg-muted" @click="clearFilters">
                            {{ t('Clear') }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 overflow-x-auto rounded-xl border border-border">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/30 text-xs font-bold uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">{{ t('Disc Name') }}</th>
                                <th class="px-4 py-3">{{ t('Username') }}</th>
                                <th class="px-4 py-3">{{ t('Status') }}</th>
                                <th class="px-4 py-3">{{ t('Active') }}</th>
                                <th class="px-4 py-3">{{ t('Match lifecycle') }}</th>
                                <th class="px-4 py-3">{{ t('Reported') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="disc in props.discs.data" :key="disc.id" class="hover:bg-muted/20">
                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                    {{ disc.id }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-foreground">
                                        {{ discLabel(disc) }}
                                    </div>
                                    <div class="mt-1 text-xs text-muted-foreground">
                                        {{ disc.colors.join(', ') || '—' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-foreground">
                                        {{ ownerLabel(disc) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        class="h-9 w-full rounded-lg border border-input bg-background px-2 text-sm"
                                        :value="disc.status"
                                        @change="updateDisc(disc.id, { status: ($event.target as HTMLSelectElement).value })"
                                    >
                                        <option value="lost">{{ t('Lost') }}</option>
                                        <option value="found">{{ t('Found') }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        class="h-9 w-full rounded-lg border border-input bg-background px-2 text-sm"
                                        :value="disc.active ? '1' : '0'"
                                        @change="updateDisc(disc.id, { active: ($event.target as HTMLSelectElement).value === '1' })"
                                    >
                                        <option value="1">{{ t('Active') }}</option>
                                        <option value="0">{{ t('Inactive') }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        class="h-9 w-full rounded-lg border border-input bg-background px-2 text-sm"
                                        :value="disc.matchLifecycle ?? ''"
                                        @change="updateDisc(disc.id, { matchLifecycle: ($event.target as HTMLSelectElement).value || null })"
                                    >
                                        <option value="">{{ t('None') }}</option>
                                        <option value="confirmed">{{ t('Confirmed') }}</option>
                                        <option value="handed_over">{{ t('Handed over') }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">
                                    {{ disc.createdAt ?? '—' }}
                                </td>
                            </tr>
                            <tr v-if="props.discs.data.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-muted-foreground">
                                    {{ t('No results found.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a
                        v-for="link in props.discs.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-input bg-muted/50 px-3 text-sm font-bold text-foreground transition-colors hover:bg-muted"
                        :class="[
                            !link.url ? 'pointer-events-none opacity-50' : '',
                            link.active ? 'bg-primary/10 text-primary border-primary/20' : '',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

