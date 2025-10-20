import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import App from './App.vue'
import ProductList from './components/ProductList.vue'
import ProductDetail from './components/ProductDetail.vue'
import Checkout from './components/Checkout.vue'
import OrderConfirmation from './components/OrderConfirmation.vue'

// Configure axios
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Router configuration
const routes = [
  { path: '/', component: ProductList },
  { path: '/product/:id', component: ProductDetail, props: true },
  { path: '/checkout', component: Checkout },
  { path: '/order-confirmation/:orderNumber', component: OrderConfirmation, props: true }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Create Vue app
const app = createApp(App)
app.use(router)
app.mount('#app')