import { ref, onMounted } from 'vue'

export type AppLayoutKind = 'header' | 'sidebar'

const key = 'layout'            // localStorage / cookie key
const layout = ref<AppLayoutKind>('header')  // default

/* ─── helper ─────────────────────────────────────────── */
function persist (value: AppLayoutKind) {
  localStorage.setItem(key, value)
  // cookie – so SSR pages render the right layout too
  document.cookie = `${key}=${value};path=/;max-age=${365*24*60*60};SameSite=Lax`
}

/* ─── init once on the client ────────────────────────── */
onMounted(() => {
  const saved = localStorage.getItem(key) as AppLayoutKind | null
  if (saved === 'sidebar' || saved === 'header') layout.value = saved
})

/* ─── exposed API ────────────────────────────────────── */
export function useLayout () {
  function setLayout (v: AppLayoutKind) {
    layout.value = v
    persist(v)
  }
  return { layout, setLayout }
}
