import { createRouter, createWebHistory } from 'vue-router'

// Auth
import Register from "@/pages/RegisterNasabah.vue"
import Login from '@/pages/Login.vue'

const routes = [
  {
    path: '/register-nasabah',
    name: 'register-nasabah',
    component: Register,
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: routes,
})

export default router
