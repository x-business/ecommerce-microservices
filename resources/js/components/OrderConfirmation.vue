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

    <!-- Order Confirmation -->
    <div v-if="!loading && !error && order" class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header bg-success text-white text-center">
            <h2><i class="fas fa-check-circle"></i> Order Confirmed!</h2>
          </div>
          <div class="card-body">
            <div class="text-center mb-4">
              <h3>Thank you for your order!</h3>
              <p class="lead">Your order has been successfully placed and a confirmation email has been sent to your email address.</p>
            </div>

            <div class="row">
              <div class="col-md-6">
                <h4>Order Details</h4>
                <p><strong>Order Number:</strong> {{ order.order_number }}</p>
                <p><strong>Order Date:</strong> {{ formatDate(order.created_at) }}</p>
                <p><strong>Status:</strong> <span class="badge bg-warning">{{ order.status }}</span></p>
                <p><strong>Total Amount:</strong> <span class="text-primary fw-bold">${{ order.total_amount }}</span></p>
              </div>
              <div class="col-md-6">
                <h4>Customer Information</h4>
                <p><strong>Name:</strong> {{ order.customer_name }}</p>
                <p><strong>Email:</strong> {{ order.customer_email }}</p>
                <p><strong>Payment Method:</strong> {{ order.payment_method || 'Not specified' }}</p>
              </div>
            </div>

            <hr>

            <h4>Shipping Address</h4>
            <p>{{ order.shipping_address }}</p>

            <div v-if="order.billing_address">
              <h4>Billing Address</h4>
              <p>{{ order.billing_address }}</p>
            </div>

            <div v-if="order.notes">
              <h4>Order Notes</h4>
              <p>{{ order.notes }}</p>
            </div>

            <hr>

            <h4>Order Items</h4>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in order.order_items" :key="item.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <img :src="item.product.image_url" class="me-2" style="width: 50px; height: 50px; object-fit: cover;" :alt="item.product.name">
                        <div>
                          <strong>{{ item.product.name }}</strong>
                          <br>
                          <small class="text-muted">{{ item.product.description }}</small>
                        </div>
                      </div>
                    </td>
                    <td>{{ item.product.sku }}</td>
                    <td>{{ item.quantity }}</td>
                    <td>${{ item.unit_price }}</td>
                    <td>${{ item.total_price }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="table-primary">
                    <th colspan="4">Total</th>
                    <th>${{ order.total_amount }}</th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="text-center mt-4">
              <router-link to="/" class="btn btn-primary btn-lg me-3">
                Continue Shopping
              </router-link>
              <button class="btn btn-outline-secondary btn-lg" @click="printOrder">
                Print Order
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'OrderConfirmation',
  props: {
    orderNumber: String
  },
  data() {
    return {
      order: null,
      loading: true,
      error: null
    }
  },
  async mounted() {
    await this.loadOrder()
  },
  methods: {
    async loadOrder() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get(`/checkout/orders/${this.orderNumber}`)
        
        if (response.data.success) {
          this.order = response.data.data
        } else {
          this.error = response.data.message || 'Order not found'
        }
      } catch (err) {
        if (err.response?.status === 404) {
          this.error = 'Order not found'
        } else {
          this.error = 'Failed to load order: ' + (err.response?.data?.message || err.message)
        }
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    printOrder() {
      window.print()
    }
  }
}
</script>

<style scoped>
@media print {
  .btn {
    display: none !important;
  }
}
</style>
