# Toko Online - E-commerce Backend API

A comprehensive e-commerce backend API built with Laravel 12, featuring modern architecture patterns, comprehensive testing, and production-ready features.

## Features

### Core Features
- **Product Management**: Complete CRUD operations with variants, images, and inventory
- **Category Management**: Hierarchical categories with nested support
- **User Authentication**: JWT-based authentication with role-based access control
- **Shopping Cart**: Session-based guest cart and persistent user cart
- **Order Management**: Complete order lifecycle with payment integration
- **Inventory Management**: Real-time stock tracking with reservation system
- **Payment Integration**: Midtrans and Xendit payment gateways
- **Shipping Integration**: RajaOngkir API for shipping cost calculation
- **Voucher System**: Flexible discount and promotion system

### Architecture Features
- **Repository Pattern**: Clean separation of data access logic
- **Service Layer**: Business logic encapsulation
- **DTOs**: Type-safe data transfer objects
- **Form Requests**: Comprehensive input validation
- **Resource Classes**: Consistent API responses
- **Exception Handling**: Centralized error handling
- **Middleware**: Request logging, JSON forcing, rate limiting
- **Traits**: Reusable model behaviors (HasSlug, Searchable, etc.)

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+ or PostgreSQL 13+
- Redis (optional, for caching and sessions)
- Node.js 18+ (for asset compilation)

## Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd backend
```

### 2. Install dependencies
```bash
composer install
npm install
```

### 3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 4. Configure environment variables
Edit `.env` file with your database and service configurations:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=toko_online
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Payment Gateways
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_IS_PRODUCTION=false

# Shipping
RAJAONGKIR_KEY=your-rajaongkir-key
RAJAONGKIR_ORIGIN_CITY_ID=501

# Frontend URL for CORS
FRONTEND_URL=http://localhost:3000
```

### 5. Database setup
```bash
php artisan migrate
php artisan db:seed
```

### 6. Storage setup
```bash
php artisan storage:link
```

### 7. Start the development server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

### Key Endpoints

#### Authentication
- `POST /auth/register` - User registration
- `POST /auth/login` - User login
- `POST /auth/logout` - User logout
- `GET /auth/me` - Get current user
- `POST /auth/refresh` - Refresh JWT token

#### Products
- `GET /products` - Get products with filtering and pagination
- `GET /products/{slug}` - Get product details
- `GET /products/featured` - Get featured products
- `GET /products/search?q={query}` - Search products
- `GET /products/{slug}/variants` - Get product variants

#### Categories
- `GET /categories` - Get all categories
- `GET /categories/{slug}` - Get category details
- `GET /categories/{slug}/products` - Get products in category

#### Cart
- `GET /cart` - Get user cart
- `POST /cart/items` - Add item to cart
- `PATCH /cart/items/{id}` - Update cart item
- `DELETE /cart/items/{id}` - Remove cart item
- `DELETE /cart/clear` - Clear cart

#### Orders
- `POST /checkout` - Process checkout
- `GET /orders` - Get user orders
- `GET /orders/{code}` - Get order details

### Response Format
All API responses follow a consistent format:

```json
{
  "data": {},
  "message": "Success message",
  "meta": {
    "pagination": "..."
  }
}
```

Error responses:
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Testing

### Run tests
```bash
php artisan test
```

### Run specific test suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Generate test coverage
```bash
php artisan test --coverage
```

## Architecture

### Directory Structure
```
app/
├── Contracts/
│   └── Repositories/          # Repository interfaces
├── DTOs/                      # Data Transfer Objects
├── Exceptions/                # Custom exceptions
├── Http/
│   ├── Controllers/Api/V1/    # API controllers
│   ├── Middleware/            # Custom middleware
│   ├── Requests/              # Form request validation
│   └── Resources/             # API resources
├── Models/                    # Eloquent models
├── Policies/                  # Authorization policies
├── Providers/                 # Service providers
├── Repositories/              # Repository implementations
├── Services/                  # Business logic services
└── Traits/                    # Reusable model traits
```

### Key Design Patterns

#### Repository Pattern
Abstracts data access logic and provides a consistent interface:
```php
interface ProductRepositoryInterface
{
    public function findBySlug(string $slug): ?Product;
    public function getFeatured(int $limit = 10): Collection;
    // ...
}
```

#### Service Layer
Encapsulates business logic and coordinates between repositories:
```php
class ProductService
{
    public function createProduct(ProductDTO $productDTO): Product
    {
        // Business logic here
    }
}
```

#### DTOs (Data Transfer Objects)
Type-safe data containers for transferring data between layers:
```php
class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        // ...
    ) {}
}
```

## Deployment

### Production Setup
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Configure production database
4. Set up Redis for caching and sessions
5. Configure queue workers
6. Set up SSL certificates
7. Configure web server (Nginx/Apache)

### Queue Workers
```bash
php artisan queue:work --daemon
```

### Scheduled Tasks
Add to crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Write comprehensive tests for new features
- Document complex business logic
- Use type hints and return types

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, email support@example.com or create an issue in the repository.