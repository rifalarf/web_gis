<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import * as L from 'leaflet'
import type { FeatureCollection, Feature } from 'geojson'

/* ── props ─────────────────────────────────────────────── */
const props = defineProps<{
  geojson: FeatureCollection
  autoFit?: boolean
  showLegend?: boolean
}>()

/* ── colour palette ────────────────────────────────────── */
const colour = {
  baik:         '#22c55e',
  sedang:       '#eab308',
  rusak_ringan: '#f97316',
  rusak_berat:  '#ef4444',
  default:      '#9ca3af',
} as const
type KondisiKey = keyof typeof colour

/* ── DOM ref ───────────────────────────────────────────── */
const mapEl = ref<HTMLElement | null>(null)

/* ── init once mounted ─────────────────────────────────── */
onMounted(() => {
  /* 1. Map & basemap layers */
  const osm  = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  })
  const esri = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
    {
      attribution:
        'Tiles &copy; Esri — Source: Esri, DeLorme, NAVTEQ, USGS, etc.',
    },
  )

  const map = L.map(mapEl.value!, { layers: [osm] }).setView([-7.2, 107.9], 10)

  /* 2. Basemap switcher (top-right) */
  L.control.layers(
    { OpenStreetMap: osm, Esri: esri },
    {},
    { position: 'topright', collapsed: false },
  ).addTo(map)

  /* 3. GeoJSON layer with style + pop-ups */
  const lineLayer = L.geoJSON(undefined, {
    style: (feat?: Feature) => {
      const k = (feat?.properties as any)?.kondisi as KondisiKey | undefined
      return { color: colour[k ?? 'default'], weight: 3 }
    },
    onEachFeature: (feat?: Feature, layer?: L.Layer) => {
      if (!feat || !layer) return
      const p = feat.properties as any
      layer.bindPopup(`
        <div class="space-y-1 text-sm">
          <div><strong>CODE:</strong> ${p.code}</div>
          <div><strong>Nama Ruas:</strong> ${p.nm_ruas}</div>
          <div><strong>Kondisi:</strong> ${p.kondisi ?? '−'}</div>
          <a href="/ruas-jalan/${p.code}" class="text-blue-600 underline">Detail</a>
        </div>
      `)
    },
  }).addTo(map)

  /* 4. Reactive load / refresh */
  watch(
    () => props.geojson,
    (data) => {
      if (!data) return
      lineLayer.clearLayers().addData(data as any)

      if (props.autoFit && lineLayer.getBounds().isValid()) {
        map.fitBounds(lineLayer.getBounds(), { padding: [20, 20] })
      }
    },
    { immediate: true },
  )

/* 5. Legend control (bottom-left) */
if (props.showLegend) {
  /* ── build the inner HTML FIRST ── */
  const legendHtml = Object.entries(colour)
    .filter(([k]) => k !== 'default')
    .map(
      ([k, c]) => `
        <div class="flex items-center gap-2 mb-1 last:mb-0">
          <span style="background:${c};width:24px;height:10px;border-radius:2px;display:inline-block"></span>
          ${k.replace('_', ' ').replace(/\b\\w/g, (l) => l.toUpperCase())}
        </div>`,
    )
    .join('')

  /* ── extend L.Control AFTER legendHtml exists ── */
  const Legend = L.Control.extend({
    options: { position: 'bottomleft' },
    onAdd() {
      const div = L.DomUtil.create(
        'div',
        'rounded-lg bg-white/80 p-2 text-xs shadow backdrop-blur dark:bg-gray-800/80',
      )
      div.innerHTML = legendHtml
      return div
    },
  })

  new Legend().addTo(map)
}


})
</script>

<template>
  <div ref="mapEl" class="relative h-full w-full" />
</template>

<style scoped>

.leaflet-container,
:host {                /* Vue 3 “host” selector */
  position: absolute;
  inset: 0;
  height: 100%;
  width: 100%;
  z-index: 0;
}
</style>
