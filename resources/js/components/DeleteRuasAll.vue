<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button }   from '@/components/ui/button'
import {
  Dialog, DialogClose, DialogContent,
  DialogDescription, DialogFooter, DialogHeader,
  DialogTitle, DialogTrigger
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { router } from '@inertiajs/vue3'

const passwordInput = ref<HTMLInputElement|null>(null)

const form = useForm({ password:'' })

function purgeAll (e:Event) {
  e.preventDefault()
  form.delete(route('ruas.purge'), {
    preserveScroll:true,
    onSuccess: () => router.visit('/'),
    onError  : () => passwordInput.value?.focus(),
    onFinish : () => form.reset(),
  })
}
function closeModal () {
  form.reset()
  form.clearErrors()
}
</script>

<template>
  <div class="space-y-6">
    <HeadingSmall
      title="Hapus semua ruas"
      description="Menghapus seluruh ruas dan segmen — tindakan ini permanen."
    />

    <div
      class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4
             dark:border-red-200/10 dark:bg-red-700/10"
    >
      <div class="text-red-600 dark:text-red-100 space-y-0.5">
        <p class="font-medium">Warning</p>
        <p class="text-sm">Proses tidak dapat dibatalkan.</p>
      </div>

      <Dialog>
        <DialogTrigger as-child>
          <Button variant="destructive">Hapus semua ruas</Button>
        </DialogTrigger>

        <DialogContent>
          <form @submit="purgeAll" class="space-y-6">
            <DialogHeader class="space-y-3">
              <DialogTitle>Anda yakin?</DialogTitle>
              <DialogDescription>
                Seluruh ruas dan data terkait akan terhapus permanen.
                Masukkan password untuk konfirmasi.
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2">
              <Label for="password" class="sr-only">Password</Label>
              <Input
                id="password"
                type="password"
                placeholder="Password"
                v-model="form.password"
                ref="passwordInput"
              />
              <InputError :message="form.errors.password" />
            </div>

            <DialogFooter class="gap-2">
              <DialogClose as-child>
                <Button variant="secondary" @click="closeModal">
                  Batal
                </Button>
              </DialogClose>

              <Button
                type="submit"
                variant="destructive"
                :disabled="form.processing"
              >
                Ya, hapus
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
