import { useRouter } from 'vue-router'

export function checkRole(approvedRole) {
  const router = useRouter()
  const role = sessionStorage.getItem('role')

  console.log(role)

  if (role === null || role !== approvedRole) {
    alert('akses ditolak!')
    router.back()
  }
}

export function redirectLogin() {
  const router = useRouter()
  const role = sessionStorage.getItem('role')

  if (role !== null) {
    router.back()
  }
}
