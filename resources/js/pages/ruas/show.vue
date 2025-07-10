<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import LeafletMap from '@/components/LeafletMap.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import type { FeatureCollection } from 'geojson'

const props = defineProps<{
  ruas: { code: string; nm_ruas: string; kecamatan: string; panjang: string }
  geojson: FeatureCollection
  markersGeojson: FeatureCollection
  markerRows: { id: number; sta: string; lat: number; lon: number }[]
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
      <!-- info card -->
    <div class="rounded-xl border p-4 dark:border-sidebar-border">
    <h2 class="text-lg font-semibold mb-2">
        CODE: {{ props.ruas.code }}
    </h2>
    <p><strong>Nama Ruas:</strong> {{ props.ruas.nm_ruas }}</p>
    <p><strong>Panjang: </strong>{{ Number(props.ruas.panjang ?? 0).toFixed(2) }} km</p>
    <p><strong>Kecamatan:</strong> {{ props.ruas.kecamatan ?? '−' }}</p>
    </div>


      <!-- Map -->
      <div class="relative min-h-[70vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <LeafletMap
            :geojson="props.geojson"
            :points-geojson="props.markersGeojson"
            :autoFit="true"
            :showLegend="true"
            :detailPopups="true"
            class="absolute inset-0 rounded-b-xl"
        />
      </div>
      <div class="rounded-xl border border-sidebar-border/70 p-4
            dark:border-sidebar-border overflow-x-auto">
        <h3 class="mb-3 text-sm font-semibold">Daftar Titik Kerusakan</h3>

        <table class="min-w-full text-xs">
            <thead>
            <tr class="border-b dark:border-gray-600">
                <th class="py-2 text-left w-12">No</th>
                <th class="py-2 text-left">STA</th>
                <th class="py-2 text-left">Koordinat</th>
                <th class="py-2 text-left w-20">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(row, idx) in props.markerRows" :key="row.id"
                class="border-b hover:bg-gray-50 dark:hover:bg-gray-800
                        dark:border-gray-600">
                <td class="py-2">{{ idx + 1 }}</td>
                <td class="py-2">{{ row.sta }}</td>
                <td class="py-2">{{ row.lat.toFixed(6) }}, {{ row.lon.toFixed(6) }}</td>
                <td class="py-2">
                <a
                    :href="`/kerusakan/${row.id}`"
                    class="text-blue-600 underline"
                >Detail</a>
                </td>
            </tr>
            <tr v-if="props.markerRows.length === 0">
                <td colspan="4" class="py-4 text-center text-gray-500">
                Tidak ada titik kerusakan pada ruas ini.
                </td>
            </tr>
            </tbody>
        </table>
        </div>
    </div>
  </AppLayout>
</template>
