<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground shadow-sm shadow-black/20"
    >
        <AppLogoIcon class="size-5 text-sidebar-primary-foreground" />
    </div>
    <div class="ml-2 grid flex-1 text-left">
        <span class="truncate text-sm font-semibold leading-tight text-white"
            >Event Visuals</span
        >
        <span class="truncate text-[11px] font-medium uppercase tracking-[0.18em] text-emerald-100/60"
            >Assessment</span
        >
    </div>
</template>
