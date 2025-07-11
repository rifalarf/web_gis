<script setup lang="ts">
import AppLayout       from '@/layouts/AppLayout.vue'
import SettingsLayout  from '@/layouts/settings/Layout.vue'
import HeadingSmall    from '@/components/HeadingSmall.vue'
import LayoutTabs      from '@/components/LayoutTabs.vue'
import { Head }        from '@inertiajs/vue3'
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Layout', href: '/settings/layout' }]">
    <Head title="Layout settings" />

    <SettingsLayout>
      <HeadingSmall title="Choose application layout" />
      <LayoutTabs />
    </SettingsLayout>
  </AppLayout>
</template>
