<script setup lang="ts">
import { Head }       from '@inertiajs/vue3'
import AppLayout      from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import HeadingSmall   from '@/components/HeadingSmall.vue'
import { useLayout }  from '@/composables/useLayout'
import LayoutTabs     from '@/components/LayoutTabs.vue'

const { layout, setLayout } = useLayout()
</script>

<template>
  <AppLayout :breadcrumbs="[{ title:'Layout', href:'/settings/layout' }]">
    <Head title="Layout settings"/>

    <SettingsLayout>
      <HeadingSmall title="Choose application layout" description="" />
      <LayoutTabs />
    </SettingsLayout>
  </AppLayout>
</template>
