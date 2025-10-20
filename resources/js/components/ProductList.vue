<template>
  <div>
    <div class="row mb-4">
      <div class="col-md-6">
        <h1>Products</h1>
      </div>
      <div class="col-md-6 text-end">
        <router-link to="/checkout" class="btn btn-primary" v-if="cartItems.length > 0">
          Checkout ({{ cartItems.length }} items)
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
      <div class="col-md-4">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Search products..." 
          v-model="searchTerm"
          @input="filterProducts"
        >
      </div>
      <div class="col-md-3">
        <select class="form-control" v-model="selectedCategory" @change="filterProducts">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <input 
          type="number" 
          class="form-control" 
          placeholder="Min Price" 
          v-model="minPrice"
          @input="filterProducts"
        >
      </div>
      <div class="col-md-2">
        <input 
          type="number" 
          class="form-control" 
          placeholder="Max Price" 
          v-model="maxPrice"
          @input="filterProducts"
        >
      </div>
      <div class="col-md-1">
        <button class="btn btn-outline-secondary" @click="clearFilters">Clear</button>
      </div>
    </div>

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

    <!-- Products Grid -->
    <div v-if="!loading && !error" class="row">
      <div v-for="product in products" :key="product.id" class="col-md-4 mb-4">
        <div class="card h-100">
          <img :src="product.image_url" class="card-img-top" :alt="product.name" style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ product.name }}</h5>
            <p class="card-text">{{ product.description }}</p>
            <div class="mt-auto">
              <p class="card-text">
                <strong>${{ product.price }}</strong>
                <small class="text-muted d-block">SKU: {{ product.sku }}</small>
                <small class="text-muted d-block">Stock: {{ product.stock_quantity }}</small>
              </p>
              <div class="d-flex justify-content-between align-items-center">
                <router-link :to="`/product/${product.id}`" class="btn btn-outline-primary btn-sm">
                  View Details
                </router-link>
                <button 
                  class="btn btn-primary btn-sm" 
                  @click="addToCart(product)"
                  :disabled="product.stock_quantity === 0"
                >
                  {{ product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <nav v-if="pagination && pagination.last_page > 1" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
          <button class="page-link" @click="loadPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url">
            Previous
          </button>
        </li>
        <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
          <button class="page-link" @click="loadPage(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
          <button class="page-link" @click="loadPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url">
            Next
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ProductList',
  props: {
    cartItems: Array
  },
  emits: ['add-to-cart'],
  data() {
    return {
      products: [],
      categories: [],
      loading: true,
      error: null,
      pagination: null,
      searchTerm: '',
      selectedCategory: '',
      minPrice: '',
      maxPrice: ''
    }
  },
  computed: {
    visiblePages() {
      if (!this.pagination) return []
      const current = this.pagination.current_page
      const last = this.pagination.last_page
      const pages = []
      
      for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
      }
      return pages
    }
  },
  async mounted() {
    await Promise.all([
      this.loadProducts(),
      this.loadCategories()
    ])
  },
  methods: {
    async loadProducts(page = 1) {
      this.loading = true
      this.error = null
      
      try {
        const params = {
          page,
          per_page: 9
        }
        
        if (this.searchTerm) params.search = this.searchTerm
        if (this.selectedCategory) params.category = this.selectedCategory
        if (this.minPrice) params.min_price = this.minPrice
        if (this.maxPrice) params.max_price = this.maxPrice
        
        const response = await axios.get('/catalog/products', { params })
        
        if (response.data.success) {
          this.products = response.data.data.data
          this.pagination = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            prev_page_url: response.data.data.prev_page_url,
            next_page_url: response.data.data.next_page_url
          }
        } else {
          this.error = response.data.message || 'Failed to load products'
        }
      } catch (err) {
        this.error = 'Failed to load products: ' + (err.response?.data?.message || err.message)
      } finally {
        this.loading = false
      }
    },
    async loadCategories() {
      try {
        const response = await axios.get('/catalog/categories')
        if (response.data.success) {
          this.categories = response.data.data
        }
      } catch (err) {
        console.error('Failed to load categories:', err)
      }
    },
    filterProducts() {
      this.loadProducts(1)
    },
    clearFilters() {
      this.searchTerm = ''
      this.selectedCategory = ''
      this.minPrice = ''
      this.maxPrice = ''
      this.loadProducts(1)
    },
    loadPage(page) {
      this.loadProducts(page)
    },
    addToCart(product) {
      this.$emit('add-to-cart', product)
    }
  }
}
</script>
