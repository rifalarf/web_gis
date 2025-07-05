<script setup lang="ts">
/* ───────────────────────── IMPORTS ───────────────────────── */
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'
import PlaceholderPattern from '@/components/PlaceholderPattern.vue'
import type { BreadcrumbItem } from '@/types'

/* ─────────────────────── PROPS & STATE ───────────────────── */
const props = defineProps<{
  ruas: { code: string; nm_ruas: string }[]
}>()

const auth = usePage().props.auth                       // starter-kit auth prop
const file = ref<File|null>(null)

/* ────────────────────── HELPERS / ACTIONS ────────────────── */
function upload(mode: 'insert' | 'update') {
  if (!file.value) { alert('Pilih file GeoJSON terlebih dahulu'); return }
  const form = new FormData()
  form.append('file', file.value)
  form.append('mode', mode)
  router.post(route('geojson.upload'), form)
}

function destroyRow(code: string) {
  if (!confirm(`Hapus ruas ${code}?`)) return
  router.delete(route('ruas.destroy', code))
}

/* ──────────────────────── BREADCRUMBS ────────────────────── */
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ruas',
        href: '/ruas',
    },
];
</script>

<template>
  <Head title="Daftar Jalan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Top 3 little cards (keep starter-kit look) -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div
          v-for="n in 3"
          :key="n"
          class="relative aspect-video overflow-hidden rounded-xl border
                 border-sidebar-border/70 dark:border-sidebar-border"
        >
          <PlaceholderPattern />
        </div>
      </div>

      <!-- Main panel -->
      <div
        class="relative flex-1 rounded-xl border border-sidebar-border/70 p-6
               dark:border-sidebar-border"
      >
        <!-- Upload controls: visible only when logged-in -->
        <div v-if="auth.user" class="mb-6 flex flex-wrap items-center gap-4">
          <input
            type="file"
            accept=".geojson,.json"
            @change="e => file = (e.target as HTMLInputElement).files![0]"
            class="file:mr-2 file:rounded-lg file:border file:border-gray-300
                   file:bg-white file:px-3 file:py-1 file:text-sm
                   hover:file:bg-gray-50 dark:file:border-gray-600
                   dark:file:bg-gray-700 dark:hover:file:bg-gray-600"
          />
          <button class="btn-primary"  @click="upload('insert')">Insert</button>
          <button class="btn-secondary" @click="upload('update')">Update</button>
        </div>

        <!-- Data table -->
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-300 dark:border-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left">No</th>
                    <!-- <th class="px-4 py-2 text-left">CODE</th> -->
                    <th class="px-4 py-2 text-left">Nama Ruas</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>

            <tbody>
              <tr
                 v-for="(r, i) in props.ruas"
                :key="r.code"
                class="border-t border-gray-200 dark:border-gray-700"
              >
                <td class="px-4 py-2">{{ i + 1 }}</td>
                <!-- <td class="px-4 py-2">{{ r.code }}</td> -->
                <td class="px-4 py-2">{{ r.nm_ruas }}</td>
                <td class="px-4 py-2 space-x-3">
                  <!-- “Detail” uses plain <a> to benefit from inertia-link prefetch -->
                  <a
                    :href="`/ruas-jalan/${r.code}`"
                    class="text-blue-600 hover:underline dark:text-blue-400"
                  >
                    Detail
                  </a>
                  <button
                    v-if="auth.user"
                    class="text-red-600 hover:underline dark:text-red-400"
                    @click="destroyRow(r.code)"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!props.ruas.length">
                <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                  Belum ada data — silakan upload GeoJSON.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
