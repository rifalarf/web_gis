<script setup lang="ts">
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(ArcElement, Tooltip, Legend)

defineProps<{
  title : string
  labels: string[]
  data  : number[]
  colors: string[]
}>()
</script>

<template>
  <div
    class="flex flex-col items-center justify-center gap-2
         rounded-xl border p-6 dark:border-sidebar-border
         w-full h-40 w-[340px] h-[380px]"
  >
    <h3 class="mb-2 text-sm font-semibold text-center">{{ title }}</h3>

    <!-- chart; 320 × 320 px, responsive OFF so it will never re-size -->
    <Pie
      :data="{
        labels,
        datasets:[{ data, backgroundColor: colors }]
      }"
      :options="{ responsive:false, maintainAspectRatio:false }"
      :width="320"
      :height="320"
    />
  </div>
</template>
