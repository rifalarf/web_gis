<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">Upload GeoJSON</h1>

    <input type="file" accept=".json,.geojson" @change="onFile" />

    <div class="mt-4 flex gap-4">
      <button @click="submit('insert')" class="btn-primary">Insert</button>
      <button @click="submit('update')" class="btn-secondary">Update</button>
    </div>

    <p v-if="form.progress" class="mt-2">Uploading … {{ form.progress.percentage }} %</p>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
const form = useForm({ file: null, mode: null })

function onFile(e)  { form.file = e.target.files[0] }
function submit(m)  { form.mode = m; form.post(route('geojson.upload')) }
</script>
