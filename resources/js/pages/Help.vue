<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard } from '@/routes';

const t = useTranslations();

const breadcrumbs = [
    { title: t('My Profile'), href: dashboard().url },
    { title: t('Help'), href: '/help' },
];
</script>

<template>
    <Head :title="t('Help')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-3xl px-4 py-8">
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h1 class="text-2xl font-bold text-foreground">
                    {{ t('Help') }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ t('Help intro') }}
                </p>

                <div class="mt-6 space-y-6">
                    <section>
                        <h2 class="text-lg font-bold text-foreground">
                            {{ t('How it works') }}
                        </h2>
                        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-foreground">
                            <li>{{ t('Help step 1') }}</li>
                            <li>{{ t('Help step 2') }}</li>
                            <li>{{ t('Help step 3') }}</li>
                            <li>{{ t('Help step 4') }}</li>
                        </ol>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold text-foreground">
                            {{ t('Messaging') }}
                        </h2>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-foreground">
                            <li>{{ t('Help messaging 1') }}</li>
                            <li>{{ t('Help messaging 2') }}</li>
                            <li>{{ t('Help messaging 3') }}</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold text-foreground">
                            {{ t('Confirm & handover') }}
                        </h2>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-foreground">
                            <li>
                                <span class="font-bold">{{ t('Confirm match') }}:</span>
                                {{ t('Help confirm 1') }}
                            </li>
                            <li>
                                <span class="font-bold">{{ t('Handed over') }}:</span>
                                {{ t('Help handover 1') }}
                            </li>
                            <li>
                                <span class="font-bold">{{ t('Reject') }}:</span>
                                {{ t('Help reject 1') }}
                            </li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold text-foreground">
                            {{ t('Disc activity') }}
                        </h2>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-foreground">
                            <li>{{ t('Help activity 1') }}</li>
                            <li>{{ t('Help activity 2') }}</li>
                            <li>{{ t('Help activity 3') }}</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold text-foreground">
                            {{ t('Tips') }}
                        </h2>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-foreground">
                            <li>{{ t('Help tip 1') }}</li>
                            <li>{{ t('Help tip 2') }}</li>
                            <li>{{ t('Help tip 3') }}</li>
                        </ul>
                    </section>

                    <section class="rounded-lg border border-border bg-background/50 p-4">
                        <p class="text-sm text-foreground">
                            {{ t('Need help?') }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ t('Help contact') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link
                                href="/messages"
                                class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-4 text-sm font-bold text-primary-foreground transition-opacity hover:opacity-90"
                            >
                                {{ t('Messages') }}
                            </Link>
                            <Link
                                href="/dashboard"
                                class="inline-flex h-9 items-center justify-center rounded-lg border border-input bg-muted/50 px-4 text-sm font-bold text-foreground transition-colors hover:bg-muted"
                            >
                                {{ t('My Profile') }}
                            </Link>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

