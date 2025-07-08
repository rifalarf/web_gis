<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PlaceholderPattern from '@/components/PlaceholderPattern.vue'
import type { BreadcrumbItem } from '@/types'
import { ref } from 'vue'

const props = defineProps<{
  markers: {
    id: number
    sta: string | null
    nm_ruas: string
    lat: number
    lon: number
  }[]
}>()

const auth = usePage().props.auth
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Peta', href: '/' },
  { title: 'Titik Kerusakan', href: '/kerusakan' },
]

function destroy(id: number) {
  if (!confirm('Hapus marker ini?')) return
  router.delete(route('kerusakan.destroy', id))
}
</script>

<template>
  <Head title="Titik Kerusakan" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- top placeholder cards stay for visual parity -->
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

      <!-- table card -->
      <div
        class="relative flex-1 rounded-xl border border-sidebar-border/70 p-6
               dark:border-sidebar-border"
      >
        <!-- Add button -->
        <div v-if="auth.user" class="mb-4">
          <a
            href="/kerusakan/create"
            class="btn-primary inline-block"
          >
            + Tambah Marker
          </a>
        </div>

        <!-- table -->
        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-300 dark:border-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-3 py-2 text-left">No</th>
                <th class="px-3 py-2 text-left">STA</th>
                <th class="px-3 py-2 text-left">Nama Ruas</th>
                <th class="px-3 py-2 text-left">Koordinat</th>
                <th class="px-3 py-2 text-left">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(m, i) in props.markers"
                :key="m.id"
                class="border-t border-gray-200 dark:border-gray-700"
              >
                <td class="px-3 py-2">{{ i + 1 }}</td>
                <td class="px-3 py-2">{{ m.sta ?? '−' }}</td>
                <td class="px-3 py-2">{{ m.nm_ruas }}</td>
                <td class="px-3 py-2">
                  {{ m.lat.toFixed(6) }}, {{ m.lon.toFixed(6) }}
                </td>
                <td class="px-3 py-2 space-x-3">
                  <a
                    :href="`/kerusakan/${m.id}`"
                    class="text-blue-600 hover:underline dark:text-blue-400"
                  >
                    Detail
                  </a>
                  <!-- show CRUD only for logged-in user -->
                  <template v-if="auth.user">
                    <a
                      :href="`/kerusakan/${m.id}/edit`"
                      class="text-yellow-600 hover:underline dark:text-yellow-400"
                    >
                      Edit
                    </a>
                    <button
                      class="text-red-600 hover:underline dark:text-red-400"
                      @click="destroy(m.id)"
                    >
                      Delete
                    </button>
                  </template>
                </td>
              </tr>

              <tr v-if="!props.markers.length">
                <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">
                  Belum ada data.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
