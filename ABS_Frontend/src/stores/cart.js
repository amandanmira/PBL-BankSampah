import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),
  getters: {
    totalItems: (state) => state.items.length,
    totalWeight: (state) => state.items.reduce((acc, item) => acc + (item.quantity || 0), 0),
    totalPrice: (state) => {
      return state.items.reduce((acc, item) => {
        const pricePerKg = item.item_sampah.harga_jual * (1 - (parseFloat(item.item_sampah.diskon) || 0))
        return acc + (pricePerKg * item.quantity)
      }, 0)
    },
  },
  actions: {
    addItem(sampah, quantity) {
      const existingItem = this.items.find((i) => Number(i.sampah_id) === Number(sampah.sampah_id))
      if (existingItem) {
        existingItem.quantity += quantity
      } else {
        this.items.push({
          ...sampah,
          quantity: quantity,
        })
      }
    },
    removeItem(sampahId) {
      this.items = this.items.filter((i) => i.sampah_id !== sampahId)
    },
    updateQuantity(sampahId, quantity) {
      const item = this.items.find((i) => i.sampah_id === sampahId)
      if (item) {
        item.quantity = quantity
      }
    },
    clearCart() {
      this.items = []
    },
  },
})
