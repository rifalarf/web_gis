<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import LeafletMap from '@/components/LeafletMap.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import type { FeatureCollection } from 'geojson'

const props = defineProps<{
  ruas: { code: string; nm_ruas: string }
  geojson: FeatureCollection
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Peta',        href: '/' },
  { title: 'Daftar Jalan',href: '/ruas-jalan' },
  { title: props.ruas.code, href: `/ruas-jalan/${props.ruas.code}` },
]
</script>

<template>
  <Head :title="`Ruas ${props.ruas.code}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-col gap-4 rounded-xl p-4">
      <!-- Info card -->
      <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
        <h2 class="text-lg font-semibold mb-2">Ruas {{ props.ruas.code }}</h2>
        <p><strong>Nama Ruas:</strong> {{ props.ruas.nm_ruas }}</p>
      </div>

      <!-- Map -->
      <div class="relative min-h-[70vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <LeafletMap
            :geojson="props.geojson"
            :autoFit="true"
            :showLegend="true"
            :detailPopups="true"
            class="absolute inset-0 rounded-b-xl"
        />

      </div>
    </div>
  </AppLayout>
</template>
