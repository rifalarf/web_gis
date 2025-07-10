<script setup lang="ts">
import { computed, h } from 'vue'
import { useLayout } from '@/composables/useLayout'

import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout  from '@/layouts/app/AppSidebarLayout.vue'

import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { layout } = useLayout()

/* pick the component reactively */
const ActiveLayout = computed(() =>
  layout.value === 'sidebar' ? AppSidebarLayout : AppHeaderLayout
)
</script>

<template>
  <component :is="ActiveLayout" :breadcrumbs="breadcrumbs">
    <slot />
  </component>
</template>
