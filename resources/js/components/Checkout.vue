<template>
  <div>
    <h1>Checkout</h1>
    
    <!-- Cart Items -->
    <div v-if="cartItems.length > 0" class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3>Order Summary</h3>
          </div>
          <div class="card-body">
            <div v-for="item in cartItems" :key="item.id" class="row mb-3 pb-3 border-bottom">
              <div class="col-md-2">
                <img :src="item.image_url" class="img-fluid rounded" :alt="item.name">
              </div>
              <div class="col-md-6">
                <h5>{{ item.name }}</h5>
                <p class="text-muted">${{ item.price }} each</p>
              </div>
              <div class="col-md-2">
                <label class="form-label">Quantity:</label>
                <div class="input-group">
                  <button class="btn btn-outline-secondary btn-sm" @click="updateQuantity(item.id, item.quantity - 1)">-</button>
                  <input type="number" class="form-control form-control-sm text-center" v-model="item.quantity" min="1" @change="validateQuantity(item)">
                  <button class="btn btn-outline-secondary btn-sm" @click="updateQuantity(item.id, item.quantity + 1)">+</button>
                </div>
              </div>
              <div class="col-md-2 text-end">
                <p class="fw-bold">${{ (item.price * item.quantity).toFixed(2) }}</p>
                <button class="btn btn-outline-danger btn-sm" @click="removeFromCart(item.id)">Remove</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="card mt-4">
          <div class="card-header">
            <h3>Customer Information</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="submitOrder">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="customer_name" class="form-label">Full Name *</label>
                  <input type="text" class="form-control" id="customer_name" v-model="orderForm.customer_name" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="customer_email" class="form-label">Email *</label>
                  <input type="email" class="form-control" id="customer_email" v-model="orderForm.customer_email" required>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="shipping_address" class="form-label">Shipping Address *</label>
                <textarea class="form-control" id="shipping_address" rows="3" v-model="orderForm.shipping_address" required></textarea>
              </div>
              
              <div class="mb-3">
                <label for="billing_address" class="form-label">Billing Address</label>
                <textarea class="form-control" id="billing_address" rows="3" v-model="orderForm.billing_address"></textarea>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="payment_method" class="form-label">Payment Method</label>
                  <select class="form-control" id="payment_method" v-model="orderForm.payment_method">
                    <option value="">Select Payment Method</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="bank_transfer">Bank Transfer</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="notes" class="form-label">Order Notes</label>
                  <textarea class="form-control" id="notes" rows="2" v-model="orderForm.notes"></textarea>
                </div>
              </div>
              
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  Place Order
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Order Total -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3>Order Total</h3>
          </div>
          <div class="card-body">
            <div v-for="item in cartItems" :key="item.id" class="d-flex justify-content-between mb-2">
              <span>{{ item.name }} x{{ item.quantity }}</span>
              <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
              <strong>Total:</strong>
              <strong class="text-primary">${{ totalAmount.toFixed(2) }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty Cart -->
    <div v-else class="text-center">
      <h3>Your cart is empty</h3>
      <p>Add some products to your cart to proceed with checkout.</p>
      <router-link to="/" class="btn btn-primary">Continue Shopping</router-link>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="alert alert-danger mt-3">
      {{ error }}
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Checkout',
  props: {
    cartItems: Array
  },
  emits: ['remove-from-cart', 'clear-cart'],
  data() {
    return {
      loading: false,
      error: null,
      orderForm: {
        customer_name: '',
        customer_email: '',
        shipping_address: '',
        billing_address: '',
        payment_method: '',
        notes: ''
      }
    }
  },
  computed: {
    totalAmount() {
      return this.cartItems.reduce((total, item) => total + (item.price * item.quantity), 0)
    }
  },
  methods: {
    updateQuantity(productId, newQuantity) {
      const item = this.cartItems.find(item => item.id === productId)
      if (item && newQuantity > 0) {
        item.quantity = newQuantity
      }
    },
    validateQuantity(item) {
      if (item.quantity < 1) {
        item.quantity = 1
      }
    },
    removeFromCart(productId) {
      this.$emit('remove-from-cart', productId)
    },
    async submitOrder() {
      this.loading = true
      this.error = null
      
      try {
        const orderData = {
          ...this.orderForm,
          items: this.cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity
          }))
        }
        
        const response = await axios.post('/checkout/orders', orderData)
        
        if (response.data.success) {
          this.$emit('clear-cart')
          this.$router.push(`/order-confirmation/${response.data.data.order_number}`)
        } else {
          this.error = response.data.message || 'Failed to place order'
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to place order: ' + err.message
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
