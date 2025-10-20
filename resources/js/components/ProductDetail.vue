<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- Product Details -->
    <div v-if="!loading && !error && product" class="row">
      <div class="col-md-6">
        <img :src="product.image_url" class="img-fluid rounded" :alt="product.name">
      </div>
      <div class="col-md-6">
        <h1>{{ product.name }}</h1>
        <p class="lead">{{ product.description }}</p>
        
        <div class="mb-3">
          <h3 class="text-primary">${{ product.price }}</h3>
          <p><strong>SKU:</strong> {{ product.sku }}</p>
          <p><strong>Category:</strong> {{ product.category }}</p>
          <p><strong>Stock:</strong> {{ product.stock_quantity }} units</p>
        </div>

        <div class="mb-3">
          <label for="quantity" class="form-label">Quantity:</label>
          <div class="input-group" style="width: 150px;">
            <button class="btn btn-outline-secondary" type="button" @click="decreaseQuantity">-</button>
            <input type="number" class="form-control text-center" v-model="quantity" min="1" :max="product.stock_quantity">
            <button class="btn btn-outline-secondary" type="button" @click="increaseQuantity">+</button>
          </div>
        </div>

        <div class="d-grid gap-2 d-md-flex">
          <button 
            class="btn btn-primary btn-lg" 
            @click="addToCart"
            :disabled="product.stock_quantity === 0"
          >
            {{ product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart' }}
          </button>
          <router-link to="/" class="btn btn-outline-secondary btn-lg">
            Back to Products
          </router-link>
        </div>
      </div>
    </div>

    <!-- Back to Products -->
    <div v-if="!loading && !error" class="mt-4">
      <router-link to="/" class="btn btn-outline-primary">
        ← Back to Products
      </router-link>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ProductDetail',
  props: {
    id: String,
    cartItems: Array
  },
  emits: ['add-to-cart'],
  data() {
    return {
      product: null,
      loading: true,
      error: null,
      quantity: 1
    }
  },
  async mounted() {
    await this.loadProduct()
  },
  methods: {
    async loadProduct() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get(`/catalog/products/${this.id}`)
        
        if (response.data.success) {
          this.product = response.data.data
        } else {
          this.error = response.data.message || 'Product not found'
        }
      } catch (err) {
        if (err.response?.status === 404) {
          this.error = 'Product not found'
        } else {
          this.error = 'Failed to load product: ' + (err.response?.data?.message || err.message)
        }
      } finally {
        this.loading = false
      }
    },
    increaseQuantity() {
      if (this.quantity < this.product.stock_quantity) {
        this.quantity++
      }
    },
    decreaseQuantity() {
      if (this.quantity > 1) {
        this.quantity--
      }
    },
    addToCart() {
      if (this.product && this.quantity > 0) {
        this.$emit('add-to-cart', this.product, this.quantity)
        this.quantity = 1
      }
    }
  }
}
</script>
