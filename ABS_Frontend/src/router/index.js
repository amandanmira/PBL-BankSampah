import { createRouter, createWebHistory } from 'vue-router'
import Register from "../components/RegisterNasabah.vue"

const routes = [
  {
    path: "/register",
    name: "register",
    component: Register
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: routes,
})

export default router
