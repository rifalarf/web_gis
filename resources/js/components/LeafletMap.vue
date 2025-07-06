<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import * as L from 'leaflet'
import type { FeatureCollection, Feature } from 'geojson'

/* ─── props ────────────────────────────────────────────── */
const props = defineProps<{
  geojson: FeatureCollection
  autoFit?: boolean
  showLegend?: boolean
}>()

/* ─── colour map ───────────────────────────────────────── */
const colour = {
  baik:         '#22c55e',
  sedang:       '#eab308',
  rusak_ringan: '#f97316',
  rusak_berat:  '#ef4444',
  default:      '#9ca3af',
} as const

type KondisiKey = keyof typeof colour   //  'baik' | 'sedang' | ...

/* ─── DOM ref + Leaflet init ───────────────────────────── */
const mapEl = ref<HTMLElement | null>(null)

onMounted(() => {
  const map = L.map(mapEl.value!).setView([-7.2, 107.9], 10)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  }).addTo(map)

  /* create layer **once** with style + pop-up callbacks */
  const layer = L.geoJSON(undefined, {
    style: (feat?: Feature) => {
      const k = (feat?.properties as any)?.kondisi as KondisiKey | undefined
      return { color: colour[k ?? 'default'], weight: 3 }
    },
    onEachFeature: (feat?: Feature, l?: L.Layer) => {
      if (!feat || !l) return
      const p = feat.properties as any
      l.bindPopup(`
        <div class="space-y-1 text-sm">
          <div><strong>CODE:</strong> ${p.code}</div>
          <div><strong>Nama Ruas:</strong> ${p.nm_ruas}</div>
          <div><strong>STA:</strong> ${p.sta}</div>
          <div><strong>Kondisi:</strong> ${p.kondisi ?? '−'}</div>
          <a href="/ruas-jalan/${p.code}" class="text-blue-600 underline">Detail</a>
        </div>
      `)
    },
  }).addTo(map)

  /* reactively refresh data */
  watch(
    () => props.geojson,
    (data) => {
      if (!data) return
      layer.clearLayers().addData(data as any)

      if (props.autoFit && layer.getBounds().isValid()) {
        map.fitBounds(layer.getBounds(), { padding: [20, 20] })
      }
    },
    { immediate: true },
  )
})
</script>

<template>
  <div ref="mapEl" class="h-full w-full">
    <!-- legend -->
    <div
      v-if="showLegend"
      class="absolute bottom-4 left-4 rounded-lg bg-white/80 p-2 text-xs shadow
             backdrop-blur dark:bg-gray-800/80"
    >
      <LegendRow label="Baik"          :color="colour.baik" />
      <LegendRow label="Sedang"        :color="colour.sedang" />
      <LegendRow label="Rusak Ringan"  :color="colour.rusak_ringan" />
      <LegendRow label="Rusak Berat"   :color="colour.rusak_berat" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default {
  components: {
    LegendRow: defineComponent({
      props: { label: String, color: String },
      template: `
        <div class="flex items-center gap-2">
          <span class="block h-3 w-6 rounded" :style="{ background: color }"></span>
          <span>{{ label }}</span>
        </div>
      `,
    }),
  },
}
</script>

<style scoped>
.leaflet-container { height: 100%; width: 100%; }
</style>
