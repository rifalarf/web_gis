<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import LeafletMap from '@/components/LeafletMap.vue'
import { Head } from '@inertiajs/vue3'
import type { Feature, FeatureCollection } from 'geojson'
import type { BreadcrumbItem } from '@/types'

/* ── props from controller ───────────────────────────── */
const props = defineProps<{
  marker: Feature
  lines: FeatureCollection
  info: { sta: string|null; nama_ruas: string; image: string|null }
}>()

const prop = (props.marker.properties ?? {}) as {
  id?: number
  ruas_code?: string
}

/* ── breadcrumb trail ────────────────────────────────── */
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Peta',             href: '/' },
  { title: 'Titik Kerusakan',  href: '/kerusakan' },
  { title: `ID #${prop.id ?? props.marker.id}`, href: '#' },
]

/* ── wrap single pin as FeatureCollection ────────────── */
const pinGJ: FeatureCollection = {
  type: 'FeatureCollection',
  features: [props.marker],
}
</script>

<template>
  <Head title="Detail Kerusakan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-col gap-4 rounded-xl p-4 lg:flex-row">

      <!-- MAP PANEL -->
      <div class="relative h-[60vh] flex-1 rounded-xl border
                  dark:border-sidebar-border lg:h-auto">
        <LeafletMap
          :geojson="props.lines"
          :points-geojson="pinGJ"
          :followPoints="true"
          :showLegend="false"
          :detailPopups="true"
        />
      </div>

      <!-- INFO CARD -->
      <div class="w-full max-w-sm rounded-xl border p-6
                  dark:border-sidebar-border lg:w-80">
        <div class="space-y-3 text-sm">
          <div><strong>Nama Ruas:</strong> {{ props.info.nama_ruas }}</div>
          <div><strong>STA:</strong> {{ props.info.sta ?? '−' }}</div>
          <a
            :href="`/ruas-jalan/${prop.ruas_code ?? ''}`"
            class="text-blue-600 underline"
          >Lihat Ruas</a>

          <img
            v-if="props.info.image"
            :src="props.info.image"
            class="mt-3 rounded max-h-60"
          />
        </div>
      </div>

    </div>
  </AppLayout>
</template>
