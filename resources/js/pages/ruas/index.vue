<script setup lang="ts">
/* ───── imports ───── */
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import AppLayout          from '@/layouts/AppLayout.vue'
import PlaceholderPattern from '@/components/PlaceholderPattern.vue'
import type { BreadcrumbItem } from '@/types'

import EasyDataTable      from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'

import { Eye, Trash2, Search } from 'lucide-vue-next'

/* ───── props ───── */
const props = defineProps<{
  ruas: {
    code   : string
    nm_ruas: string
    panjang: number   //  ← make sure the controller sends this!
  }[]
}>()

/* ───── misc state ───── */
const auth        = usePage().props.auth
const breadcrumbs = [{ title:'Ruas', href:'/ruas-jalan' }] as BreadcrumbItem[]

/* ───── upload helpers (unchanged) ───── */
const file = ref<File|null>(null)
function upload (mode:'insert'|'update'){
  if(!file.value){ alert('Pilih file GeoJSON terlebih dahulu'); return }
  const form = new FormData()
  form.append('file', file.value)
  form.append('mode', mode)
  router.post(route('geojson.upload'), form)
}
function destroyRow(code:string){
  if(confirm(`Hapus ruas ${code}?`))
    router.delete(route('ruas.destroy', code))
}

/* ───── Easy-Data-Table spec ───── */
const headers = [
  { text:'No',          value:'no',        width:60,  sortable:true  },
  { text:'Nama Ruas',   value:'nm_ruas',               sortable:true  },
  { text:'Panjang',value:'panjang',               sortable:true  },
  { text:'Aksi',        value:'action',    width:110, sortable:false },
]

const rows = computed(() =>
  props.ruas.map((r, i) => ({
    no: i + 1,
    nm_ruas: r.nm_ruas,
    panjang: `${r.panjang.toFixed(2)} km`,
    code: r.code,
  }))
)


const search = ref('')
</script>

<template>
  <Head title="Daftar Jalan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-1 flex-col gap-4 p-4 rounded-xl">

      <!-- decorative placeholders -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-2">
        <div v-for="n in 2" :key="n"
             class="relative h-24 overflow-hidden rounded-xl border
                    border-sidebar-border/70 dark:border-sidebar-border">
          <PlaceholderPattern/>
        </div>
      </div>

      <!-- main panel -->
      <div class="relative rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">

        <!-- Top controls: Upload + Search -->
<!-- Top controls: Search left, Upload right -->
<div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 flex-wrap">

  <!-- Search bar on the left -->
  <div class="relative w-full sm:w-1/2 lg:w-1/3">
    <input
      v-model="search"
      placeholder="Search..."
      class="rounded-lg border px-4 py-2 pl-10 shadow-sm
             placeholder-gray-400 dark:bg-neutral-800 dark:border-neutral-600 dark:text-white"
    />
    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
  </div>

  <!-- Upload controls on the right -->
  <div v-if="auth.user" class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
    <input
      type="file"
      accept=".geojson,.json"
      @change="e => file = (e.target as HTMLInputElement).files![0]"
      class="file:mr-2 file:rounded-lg file:border file:border-gray-300
             file:bg-white file:px-3 file:py-1 file:text-sm hover:file:bg-gray-50
             dark:file:border-gray-600 dark:file:bg-gray-700 dark:hover:file:bg-gray-600"
    />
    <div class="flex gap-2">
      <button class="btn-primary"   @click="upload('insert')">Insert</button>
      <button class="btn-secondary" @click="upload('update')">Update</button>
    </div>
  </div>
</div>



        <!-- @ts-expect-error -->
        <EasyDataTable
          class="w-full"
          :headers="headers"
          :items="rows"
          :search-value="search"
          :rows-per-page="10"
          :sort-by="'no'"
          alternating
          header-text-direction="left"
          body-row-class="border-t border-gray-200 dark:border-gray-700"
          table-class="border-collapse text-base"
          style="
            --easy-table-body-row-height   : 50px;
            --easy-table-header-height : 50px;
            --easy-table-footer-height : 50px;
          "
        >
          <!-- action buttons -->
          <!-- @vue-ignore -->
          <template #item-action="{ code }">
            <div class="flex gap-2 text-neutral-600 dark:text-neutral-300">
              <Link :href="`/ruas-jalan/${code}`" class="hover:text-blue-600">
                <Eye :size="20"/>
              </Link>
              <button v-if="auth.user" @click="destroyRow(code)" class="hover:text-red-600">
                <Trash2 :size="20"/>
              </button>
            </div>
          </template>
        </EasyDataTable>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Upscale everything inside the table */
::v-deep(.vue3-easy-data-table__body td),
::v-deep(.vue3-easy-data-table__header .header-text),
::v-deep(.vue3-easy-data-table__footer),
::v-deep(.vue3-easy-data-table__footer *) {
  font-size  : 1rem;   /* Tailwind text-base */
  line-height: 1.5rem; /* Tailwind leading-6 */
}
</style>
