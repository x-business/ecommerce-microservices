# E-Commerce Microservices System

A comprehensive e-commerce system built with Laravel and Vue.js, featuring three microservices: Catalog, Checkout, and Email services. The system includes Docker containerization, AWS infrastructure templates, and comprehensive testing.

## 🏗️ Architecture

### Microservices

1. **Catalog Service** - Product management and listing
   - Product listing with filtering and pagination
   - Product details and categories
   - Search functionality

2. **Checkout Service** - Order processing
   - Order creation and management
   - Stock validation and updates
   - Order status tracking

3. **Email Service** - Notification system
   - Order confirmation emails
   - Custom email notifications
   - Email templates

### Tech Stack

- **Backend**: PHP 8.1, Laravel 10
- **Frontend**: Vue.js 3, Bootstrap 5
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Containerization**: Docker & Docker Compose
- **Cloud**: AWS (EC2, RDS, CloudFormation, SAM)
- **Testing**: PHPUnit, Laravel Testing

## 🚀 Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 14+
- Docker & Docker Compose
- MySQL 8.0+
- Redis

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/x-business/ecommerce-microservices.git
   cd ecommerce-microservices
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

## 🐳 Docker Setup

### Using Docker Compose

1. **Run the Docker setup script**
   ```bash
   ./docker-setup.sh
   ```

   Or manually:

2. **Start all services**
   ```bash
   docker-compose up -d
   ```

3. **Run migrations and seeders**
   ```bash
   docker-compose exec app php artisan migrate --force
   docker-compose exec app php artisan db:seed --force
   ```

### Services

- **Application**: `http://localhost:8080`
- **MySQL**: `localhost:3306`
- **Redis**: `localhost:6379`
- **MailHog**: `http://localhost:8025`

### Docker Commands

```bash
# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# Access application container
docker-compose exec app bash
```

## ☁️ AWS Deployment

### CloudFormation Deployment

1. **Deploy database infrastructure**
   ```bash
   ./deploy-aws.sh dev us-east-1
   ```

2. **Update application configuration**
   - Update `.env` with RDS endpoint
   - Configure security groups

3. **Deploy application stack**
   ```bash
   aws cloudformation deploy \
     --template-file aws/cloudformation/application.yml \
     --stack-name ecommerce-dev-application \
     --parameter-overrides Environment=dev \
     --capabilities CAPABILITY_IAM
   ```

### SAM Serverless Deployment

1. **Deploy serverless API**
   ```bash
   ./deploy-sam.sh dev us-east-1
   ```

2. **Configure Lambda functions**
   - Update environment variables
   - Set up API Gateway

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test tests/Feature/CatalogServiceTest.php
```

### Test Structure

- **Feature Tests**: API endpoint testing
- **Unit Tests**: Individual component testing
- **Factory Tests**: Data generation testing

### Test Coverage

The test suite covers:
- ✅ Catalog service endpoints
- ✅ Checkout service functionality
- ✅ Email service operations
- ✅ Database operations
- ✅ Validation rules
- ✅ Error handling

## 📚 API Documentation

### Catalog Service

#### Get Products
```http
GET /api/catalog/products
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 10)
- `category` - Filter by category
- `search` - Search term
- `min_price` - Minimum price
- `max_price` - Maximum price

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [...],
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  },
  "message": "Products retrieved successfully"
}
```

#### Get Product Details
```http
GET /api/catalog/products/{id}
```

#### Get Categories
```http
GET /api/catalog/categories
```

### Checkout Service

#### Create Order
```http
POST /api/checkout/orders
```

**Request Body:**
```json
{
  "customer_email": "customer@example.com",
  "customer_name": "John Doe",
  "shipping_address": "123 Main St, City, State",
  "billing_address": "123 Main St, City, State",
  "payment_method": "credit_card",
  "notes": "Special instructions",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

#### Get Order
```http
GET /api/checkout/orders/{orderNumber}
```

#### Update Order Status
```http
PATCH /api/checkout/orders/{orderNumber}/status
```

**Request Body:**
```json
{
  "status": "processing"
}
```

### Email Service

#### Send Order Confirmation
```http
POST /api/email/order-confirmation
```

**Request Body:**
```json
{
  "order_id": 1
}
```

#### Send Custom Notification
```http
POST /api/email/custom-notification
```

**Request Body:**
```json
{
  "to": "recipient@example.com",
  "subject": "Subject",
  "message": "Message content"
}
```

## 🗄️ Database Schema

### Products Table
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price
- `sku` - Stock keeping unit
- `stock_quantity` - Available quantity
- `image_url` - Product image URL
- `category` - Product category
- `is_active` - Active status
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Orders Table
- `id` - Primary key
- `order_number` - Unique order identifier
- `customer_email` - Customer email
- `customer_name` - Customer name
- `total_amount` - Order total
- `status` - Order status
- `shipping_address` - Shipping address
- `billing_address` - Billing address
- `payment_method` - Payment method
- `notes` - Order notes
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Order Items Table
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `quantity` - Item quantity
- `unit_price` - Price per unit
- `total_price` - Total price for item
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

## 🔧 Configuration

### Environment Variables

```env
# Application
APP_NAME="E-Commerce Store"
APP_ENV=local
APP_KEY=base64:YourAppKeyHere
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@ecommerce.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🚀 Deployment

### Production Checklist

- [ ] Update environment variables
- [ ] Configure SSL certificates
- [ ] Set up database backups
- [ ] Configure monitoring
- [ ] Set up log aggregation
- [ ] Configure CDN
- [ ] Set up error tracking
- [ ] Configure security headers

### Performance Optimization

- Enable Redis caching
- Configure database indexes
- Optimize images
- Enable compression
- Use CDN for static assets
- Implement database query optimization

## 🔒 Security

### Security Measures

- CSRF protection enabled
- SQL injection prevention
- XSS protection
- Input validation
- Rate limiting
- Secure headers
- Environment variable protection

### Security Best Practices

- Use HTTPS in production
- Regular security updates
- Database access control
- API rate limiting
- Input sanitization
- Secure file uploads

## 📊 Monitoring

### Logging

- Application logs in `storage/logs/`
- Error tracking with Laravel Telescope
- Performance monitoring
- Database query logging

### Health Checks

- Database connectivity
- Redis connectivity
- Email service status
- API endpoint health

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Run the test suite
6. Submit a pull request

### Development Guidelines

- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation
- Use meaningful commit messages
- Follow semantic versioning

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue on GitHub
- Check the documentation
- Review the test cases
- Contact the development team

## 🗺️ Roadmap

### Planned Features

- [ ] User authentication
- [ ] Payment integration
- [ ] Inventory management
- [ ] Analytics dashboard
- [ ] Mobile app
- [ ] Multi-language support
- [ ] Advanced search
- [ ] Recommendation engine

### Performance Improvements

- [ ] Database optimization
- [ ] Caching strategies
- [ ] CDN integration
- [ ] Load balancing
- [ ] Auto-scaling

---

**Built with ❤️ using Laravel and Vue.js**