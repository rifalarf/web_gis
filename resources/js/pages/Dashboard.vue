<script setup lang="ts">
/* ── imports ─────────────────────────────────────────── */
import AppLayout from '@/layouts/AppLayout.vue'
import PlaceholderPattern from '@/components/PlaceholderPattern.vue'
import LeafletMap from '@/components/LeafletMap.vue'

import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { onMounted, ref } from 'vue'

/* ── breadcrumbs ─────────────────────────────────────── */
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Peta', href: '/' },
]

/* ── fetch GeoJSON once on mount ─────────────────────── */
const geojson = ref<GeoJSON.FeatureCollection | null>(null)

onMounted(async () => {
  const res = await fetch('/api/segments.geojson')
  geojson.value = await res.json()
})
</script>

<template>
  <Head title="Peta" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- MAP panel: replace final PlaceholderPattern -->
      <div
        class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70
               md:min-h-min dark:border-sidebar-border"
      >
        <!-- show pattern while loading -->
        <PlaceholderPattern v-if="!geojson" />

        <!-- map once data is ready -->
        <LeafletMap
        v-if="geojson"
        :geojson="geojson"
        :autoFit="true"
        :showLegend="true"
        class="absolute inset-0 rounded-b-xl"
        />


      </div>
    </div>
  </AppLayout>
</template>
