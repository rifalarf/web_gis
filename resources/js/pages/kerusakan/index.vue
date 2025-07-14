<script setup lang="ts">
/* ────────────────── imports ────────────────── */
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import AppLayout            from '@/layouts/AppLayout.vue'
import PlaceholderPattern   from '@/components/PlaceholderPattern.vue'
import type { BreadcrumbItem } from '@/types'
import { ref, computed }    from 'vue'

import EasyDataTable        from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import Button from '@/components/ui/button/Button.vue'

import { Eye, Pencil, Trash2, Search } from 'lucide-vue-next'

/* ────────────────── props ───────────────────── */
const props = defineProps<{
  markers: {
    id: number
    sta: string | null
    nm_ruas: string
    lat: number
    lon: number
  }[]
}>()

/* ────────────────── misc state ──────────────── */
const auth        = usePage().props.auth
const breadcrumbs = [{ title:'Dashboard',           href:'/' }, { title: 'Titik Kerusakan', href: '/kerusakan' }] as BreadcrumbItem[]

/* ────────────────── table spec ──────────────── */
const headers = [
  { text: 'No',        value: 'no',       width: 60,  sortable: true },
  { text: 'STA',       value: 'sta',                     sortable: true },
  { text: 'Nama Ruas', value: 'nm_ruas',                 sortable: true },
  { text: 'Koordinat', value: 'coord',                   sortable: true },
  { text: 'Aksi',      value: 'action',   width: 110, sortable: false },
]

const rows = computed(() =>
  props.markers.map((m, idx) => ({
    id:      m.id,
    no:      idx + 1,
    sta:     m.sta ?? '–',
    nm_ruas: m.nm_ruas,
    coord:   `${m.lat.toFixed(6)}, ${m.lon.toFixed(6)}`,
  })),
)

const globalSearch = ref('')

function destroy (id: number) {
  if (confirm('Hapus marker ini?')) {
    router.delete(route('kerusakan.destroy', id))
  }
}
</script>

<template>
  <Head title="Titik Kerusakan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4 rounded-xl">

      <!-- Decorative placeholders -->
      <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-2">
        <div
          v-for="n in 2"
          :key="n"
          class="relative h-24 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <PlaceholderPattern />
        </div>
      </div> -->

      <!-- Table + Controls Section -->
      <div class="relative rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">

        <!-- Top controls: search + add button -->
        <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 flex-wrap">

          <!-- Search input -->
          <div class="relative w-full sm:w-1/2 lg:w-1/3">
            <input
                v-model="globalSearch"
                placeholder="Search..."
                class="rounded-lg border px-4 py-2 pl-10 shadow-sm
                    placeholder-gray-400 dark:bg-neutral-800 dark:border-neutral-600 dark:text-white"
            />
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>

          <!-- Add button -->
          <div v-if="auth.user" class="w-full sm:w-auto">
            <Button as="a" href="/kerusakan/create" class="w-full sm:w-auto text-center">
            + Tambah
            </Button>

          </div>
        </div>

        <!-- EasyDataTable -->
        <!-- @ts-expect-error -->
        <EasyDataTable
          class="w-full"
          :headers="headers"
          :items="rows"
          :search-value="globalSearch"
          :rows-per-page="10"
          :sort-by="'no'"
          alternating
          header-text-direction="left"
          body-row-class="border-t border-gray-200 dark:border-gray-700"
          table-class="border-collapse text-base"
          :table-style="{
                fontSize: '40px',
                lineHeight: '3',
            }"
          style="
            --easy-table-body-row-height: 50px;
            --easy-table-header-height: 50px;
            --easy-table-footer-height: 50px;
          "
        >
          <!-- Action buttons slot -->
          <!-- @vue-ignore -->
          <template #item-action="{ id }">
            <div class="flex gap-2 text-neutral-600 dark:text-neutral-300">
              <Link :href="`/kerusakan/${id}`" class="hover:text-blue-600">
                <Eye :size="20" />
              </Link>
              <Link
                v-if="auth.user"
                :href="`/kerusakan/${id}/edit`"
                class="hover:text-yellow-500"
              >
                <Pencil :size="20" />
              </Link>
              <button
                v-if="auth.user"
                @click="destroy(id)"
                class="hover:text-red-600"
              >
                <Trash2 :size="20" />
              </button>
            </div>
          </template>
        </EasyDataTable>
      </div>
    </div>
  </AppLayout>
</template>


<style scoped>
::v-deep(.vue3-easy-data-table__body td),
::v-deep(.vue3-easy-data-table__header .header-text),
::v-deep(.vue3-easy-data-table__footer),
::v-deep(.vue3-easy-data-table__footer *) {
  font-size: 1rem;       /* Tailwind's text-base */
  line-height: 1.5rem;   /* Tailwind's leading-6 */
}
</style>


