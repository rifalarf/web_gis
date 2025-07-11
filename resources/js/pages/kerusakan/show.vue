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
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-4 flex flex-col gap-4 lg:flex-row">

        <!-- MAP PANEL -->
        <div class="w-full lg:w-3/4">
          <div class="relative h-[300px] sm:h-[400px] lg:h-[600px] w-full rounded-xl border dark:border-sidebar-border">
            <LeafletMap
              :geojson="props.lines"
              :points-geojson="pinGJ"
              :followPoints="true"
              :showLegend="false"
              :detailPopups="true"
            />
          </div>
        </div>

        <!-- INFO CARD -->
        <div class="w-full lg:w-1/4">
          <div class="rounded-xl border p-6 dark:border-sidebar-border">
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
                class="mt-3 rounded max-h-60 w-full object-cover"
              />
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

