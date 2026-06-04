import { useRouter } from 'vue-router'

export function checkRole(approvedRole) {
  const router = useRouter()
  const role = sessionStorage.getItem('role') || localStorage.getItem('role')

  if (role === null) {
    // alert('Silakan login terlebih dahulu!')
    const currentPath = window.location.pathname + window.location.search
    router.push(`/login?redirect=${encodeURIComponent(currentPath)}`)
  } else if (role !== approvedRole) {
    alert('Akses ditolak!')
    router.back()
  }
}

export function redirectLogin() {
  const router = useRouter()
  const role = sessionStorage.getItem('role') || localStorage.getItem('role')

  if (role !== null) {
    router.back()
  }
}
