<!--  resources/js/pages/ruas/GeojsonForm.vue  -->
<script setup lang="ts">
/* ─── imports ───────────────────────────────────────────── */
import { Head, useForm, router }      from '@inertiajs/vue3'
import AppLayout                      from '@/layouts/AppLayout.vue'
import Button                         from '@/components/ui/button/Button.vue'
import {
  Dialog, DialogTrigger, DialogContent,
  DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose,
}                                     from '@/components/ui/dialog'
import { ref, onMounted, watch }      from 'vue'
import * as L                         from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { useToast }                   from 'vue-toastification'
import type { FeatureCollection }     from 'geojson'
import type { BreadcrumbItem }        from '@/types'

const props  = defineProps<{ mode:'insert'|'update' }>()
const toast  = useToast()

/* ─── colour palette (same as main map) ─────────────────── */
const colour = {
  baik:         '#22c55e',
  sedang:       '#eab308',
  rusak_ringan: '#f97316',
  rusak_berat:  '#ef4444',
  default:      '#9ca3af',
} as const
type KondisiKey = keyof typeof colour

/* ─── inertia form + file ref ───────────────────────────── */
const form = useForm({ files:[] as File[], mode:props.mode })
const files = ref<FileList|null>(null)

/* ─── leaflet refs ──────────────────────────────────────── */
const mapEl       = ref<HTMLElement|null>(null)
let   map        : L.Map
let   baseLay    : L.GeoJSON
let   previewLay : L.GeoJSON|null = null

/* highlight helpers */
const groupLayers:Record<string,L.Path[]> = {}
let hovered:string|null = null
function toggleGroup(key:string,on:boolean){
  (groupLayers[key] ?? []).forEach(p=>p.setStyle({ weight:on?6:3 }))
}

/* popup HTML (code + nama ruas only) */
function popupHtml(p:any){
  return `<div class="space-y-1 text-sm">
            <div><strong>CODE:</strong> ${p.code}</div>
            <div><strong>Nama Ruas:</strong> ${p.nm_ruas ?? '−'}</div>
          </div>`
}

/* ── init map with existing data ───────────────────────── */
async function initMap(){
  const gj:FeatureCollection =
        await fetch('/api/segments.geojson').then(r=>r.json())

  map = L.map(mapEl.value!, { zoomControl:false })
         .setView([-7.3,107.91],10)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'&copy; OpenStreetMap', maxZoom:19,
  }).addTo(map)

  baseLay = L.geoJSON(gj,{
    style:(f?:any)=>{
      const k = (f?.properties as any)?.kondisi as KondisiKey|undefined
      return { color:colour[k ?? 'default'], weight:3, lineCap:'round' }
    },
    onEachFeature:(f,l)=>{
      const p = f.properties as any
      const key = p.code
      groupLayers[key] ??= []
      groupLayers[key].push(l as L.Path)

      l.bindPopup(popupHtml(p))
      l.on('mouseover',()=>{ if(hovered!==key) toggleGroup(key,true) })
      l.on('mouseout', ()=>{ if(hovered!==key) toggleGroup(key,false)})
      l.on('click',   ()=>{
        if(hovered) toggleGroup(hovered,false)
        hovered = key
        toggleGroup(key,true)
      })
      l.on('popupclose',()=>{ toggleGroup(key,false); hovered=null })
    },
  }).addTo(map)

  map.fitBounds(baseLay.getBounds(),{ padding:[20,20] })
}

/* recolour existing lines → white when their CODE is present */
function recolourBase(codes:Set<string>){
  baseLay.setStyle((f?:any)=>{
    const p = f?.properties as any
    if (codes.has(p.code)) return { color:'#ffffff', weight:4 }
    const k = p?.kondisi as KondisiKey|undefined
    return { color:colour[k ?? 'default'], weight:3 }
  })
}

/* file-input handler */
async function onFile(e:Event){
  const f = (e.target as HTMLInputElement).files ?? null
  files.value = f
  form.files  = f ? Array.from(f) : []

  if (previewLay){ previewLay.remove(); previewLay=null }
  recolourBase(new Set())

  if(!f || f.length === 0) return

  try{
    const allFeatures: any[] = []
    for (const file of Array.from(f)) {
        const gj: FeatureCollection = JSON.parse(await file.text())
        allFeatures.push(...gj.features)
    }

    const combinedGJ: FeatureCollection = { type: 'FeatureCollection', features: allFeatures }

    previewLay = L.geoJSON(combinedGJ,{
      style:{ color:'#3388ff', weight:4, dashArray:'4 2' },
      onEachFeature:(f,l)=>l.bindPopup(popupHtml(f.properties)),
    }).addTo(map)

    const codes = new Set(
      allFeatures.map(f=>(f.properties as any).CODE).filter(Boolean),
    )
    recolourBase(codes)

    const grp = L.featureGroup([ baseLay, previewLay ])
    map.fitBounds(grp.getBounds(),{ padding:[20,20] })
  }catch{ toast.error('Satu atau lebih file GeoJSON tidak valid') }
}

/* ── upload after confirmation ─────────────────────────── */
function reallyUpload(){
  if(!files.value || files.value.length === 0){ toast.error('Pilih file GeoJSON dulu'); return }
  form.post(route('geojson.upload'),{
    onSuccess:()=>{
      toast.success(`GeoJSON ${props.mode==='insert' ? 'ditambah' : 'di-update'}`)
      /* soft-navigate back to index so toast survives */
      router.visit('/ruas-jalan', { replace:true })
    },
    onError: ()=> toast.error('Upload gagal'),
  })
}

/* breadcrumbs */
const breadcrumbs:BreadcrumbItem[]=[
  { title:'Dashboard',    href:'/' },
  { title:'Daftar Jalan', href:'/ruas-jalan' },
  { title: props.mode==='insert' ? 'Tambah Ruas Jalan' : 'Update Ruas Jalan', href:'#' },
]

onMounted(initMap)
watch(files,v=>{ if(!v) recolourBase(new Set()) })
</script>

<template>
  <Head :title="breadcrumbs[2].title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-12">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 sm:px-6
                  lg:flex-row lg:gap-8">

        <!-- ◀ upload card -->
        <div class="w-full lg:w-1/3">
          <div class="rounded-xl border p-6 dark:border-sidebar-border">
            <h2 class="mb-4 text-lg font-semibold">
              {{ breadcrumbs[2].title }}
            </h2>
            <label class="block mb-1 font-medium">Geojson</label>
            <input
              type="file"
              accept=".geojson,.json"
              @change="onFile"
              multiple
              class="file:mr-2 file:rounded-lg file:border file:bg-white file:px-3 file:py-1
                     mb-2 block w-full rounded border px-3 py-2 text-sm
                     dark:border-neutral-600 dark:bg-neutral-800"
            />
            <div v-if="files && files.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
              {{ files.length }} file dipilih.
            </div>

            <!-- confirmation dialog -->
            <Dialog>
              <DialogTrigger as-child>
                <Button class="w-full">
                  {{ breadcrumbs[2].title }}
                </Button>
              </DialogTrigger>

              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Konfirmasi</DialogTitle>
                  <DialogDescription>
                    Yakin ingin
                    {{ props.mode==='insert' ? 'menambah' : 'memperbarui' }}
                    data ruas jalan?
                  </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                  <DialogClose as-child>
                    <Button variant="secondary">Batal</Button>
                  </DialogClose>
                  <Button @click="reallyUpload">Ya</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>
        </div>

        <!-- ▶ preview map -->
        <div class="w-full lg:w-2/3">
          <div
            ref="mapEl"
            class="relative z-0 h-[300px] sm:h-[400px] lg:h-[600px]
                   rounded-xl border dark:border-sidebar-border"
          />
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* make dashed preview crisp on hi-dpi */
::v-deep .leaflet-interactive[stroke-dasharray]{
  stroke-dasharray:4 2!important;
}
/* keep tile layer under everything */
::v-deep(.leaflet-tile-pane){ z-index:0; }
</style>
