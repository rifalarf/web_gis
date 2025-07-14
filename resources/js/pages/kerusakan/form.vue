<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import LeafletMap from '@/components/LeafletMap.vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import { ref, watch } from 'vue'
import type { BreadcrumbItem } from '@/types'
import type { FeatureCollection } from 'geojson'
import { onMounted } from 'vue'
import exifr from 'exifr'

const geojson  = ref<FeatureCollection | null>(null)
const pointsGJ = ref<FeatureCollection | null>(null)

/* ── props from controller ───────────────────────────── */
const props = defineProps<{
  mode: 'create' | 'edit'
  marker?: {
    id: number
    ruas_code: string
    sta: string | null
    lat: number
    lon: number
    image: string | null
  }
  ruasOptions: { value: string; label: string }[]
}>()

/* ── form state via inertia useForm ──────────────────── */
const form = useForm({
  ruas_code: props.marker?.ruas_code ?? '',
  sta:       props.marker?.sta       ?? '',
  lat:       props.marker?.lat       ?? null,
  lon:       props.marker?.lon       ?? null,
  photo:     null as File | null,
})

/* image preview */
const preview = ref<string | null>(props.marker?.image ?? null)
watch(() => form.photo, f => {
   preview.value = f ? URL.createObjectURL(f) : props.marker?.image ?? null
  if (f) extractGps(f)                        // ② autopick after preview
 })

 /* ------------ ② GPS extractor ------------ */
    async function extractGps (file: File) {
    try {
        // second arg cast to satisfy TS
        const { latitude, longitude } = await (exifr as any).gps(file, true)
        if (latitude && longitude) {
        form.lat = +latitude.toFixed(6)
        form.lon = +longitude.toFixed(6)
        placePin(form.lat, form.lon)
        }
    } catch {
        /* silently ignore: no GPS or unreadable EXIF */
    }
    }

/* map config */
const pinGJ = ref<FeatureCollection>({ type:'FeatureCollection', features: [] })

function placePin(lat: number, lon: number) {
  form.lat = lat
  form.lon = lon
  pinGJ.value = {
    type: 'FeatureCollection',
    features: [{
      type: 'Feature',
      geometry: { type:'Point', coordinates:[lon, lat] },
      properties: { id:0, nm_ruas:'', sta:'', ruas_code:'' }
    }],
  }
}

watch([() => form.lat, () => form.lon], ([lat, lon]) => {
  if (lat !== null && lon !== null && !isNaN(lat) && !isNaN(lon)) {
    placePin(lat, lon)
  }
})


/* initialise pin when editing */
if (props.mode === 'edit' && props.marker?.lat) {
  placePin(props.marker.lat, props.marker.lon)
}

/* submit */
function submit() {
  if (props.mode === 'create') {
    form.post(route('kerusakan.store'), { forceFormData: true })
  } else {
    form.transform(data => ({
      ...data,
      _method: 'PUT',          // ← add spoofing field
    })).post(
      route('kerusakan.update', props.marker!.id),
      { forceFormData: true }
    )
  }
}



/* breadcrumbs */
const breadcrumbs: BreadcrumbItem[] = [
  { title:'Dashboard', href:'/' },
  { title:'Titik Kerusakan', href:'/kerusakan' },
  { title: props.mode === 'create' ? 'Tambah' : `Edit #${props.marker?.id}`, href:'#' },
]

function ruasValue(o: { value: string; label: string }) {
  return o.value
}

const lines = ref<FeatureCollection>()

onMounted(async () => {
  lines.value = await fetch('/api/segments.geojson').then(r => r.json())
})
</script>

<template>
  <Head :title="props.mode === 'create' ? 'Tambah Marker' : 'Edit Marker'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-4 flex flex-col gap-6 lg:flex-row">

        <!-- form card -->
        <div class="w-full lg:w-1/3">
          <div class="rounded-xl border p-6 dark:border-sidebar-border">
            <!-- Nama Ruas -->
            <label class="block mb-2 font-medium">Nama Ruas</label>
            <v-select
              :options="props.ruasOptions"
              v-model="form.ruas_code"
              :reduce="ruasValue"
              placeholder="Pilih ruas…"
            />

            <!-- STA -->
            <label class="block mb-2 font-medium">STA</label>
            <input v-model="form.sta" type="text" class="input mb-4 w-full">

            <!-- Photo -->
            <input
              type="file"
              accept="image/*"
              @change="e => {
                const files = (e.target as HTMLInputElement).files
                form.photo = files ? files[0] : null
              }"
            />

            <img v-if="preview" :src="preview" class="max-h-40 rounded mb-4" />

            <!-- Koordinat Form -->
            <label class="block mb-1 text-sm">Lat</label>
            <input
              v-model.number="form.lat"
              type="number"
              step="0.000001"
              class="w-full rounded border p-2 mb-4"
            />

            <label class="block mb-1 text-sm">Lon</label>
            <input
              v-model.number="form.lon"
              type="number"
              step="0.000001"
              class="w-full rounded border p-2 mb-4"
            />

            <!-- Submit -->
            <button class="btn-primary" @click.prevent="submit">
              {{ props.mode === 'create' ? 'Simpan' : 'Perbarui' }}
            </button>
          </div>
        </div>

        <!-- map panel -->
        <div class="w-full lg:w-2/3">
          <div class="relative h-[300px] sm:h-[400px] lg:h-[600px] rounded-xl border dark:border-sidebar-border">
            <LeafletMap
              v-if="lines"
              :geojson="lines"
              :points-geojson="pinGJ"
              :followPoints="true"
              :formMode="true"
              :showLegend="false"
              :detailPopups="true"
              class="absolute inset-0 rounded-xl"
            />
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>


