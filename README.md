# Toko Online - E-commerce Platform

Platform e-commerce modern dengan Laravel backend dan Next.js frontend.

## Features

- 🛍️ **Product Management**: Katalog produk dengan varian dan kategori
- 🛒 **Shopping Cart**: Keranjang belanja untuk guest dan user
- 👤 **User Authentication**: Login/register dengan JWT
- 📦 **Order Management**: Sistem pemesanan lengkap
- 💳 **Payment Integration**: Midtrans dan Xendit
- 🚚 **Shipping Integration**: RajaOngkir API
- 🎫 **Voucher System**: Sistem diskon dan promo
- 📱 **Responsive Design**: Mobile-first design

## Tech Stack

### Backend
- Laravel 12
- PHP 8.2+
- SQLite/MySQL
- JWT Authentication
- Repository Pattern

### Frontend
- Next.js 15
- React 19
- TypeScript
- Tailwind CSS
- Zustand (State Management)
- React Query (Data Fetching)

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm/yarn

### Installation

1. **Clone repository**
```bash
git clone <repository-url>
cd toko-online
```

2. **Install dependencies**
```bash
npm run install:all
```

3. **Setup backend**
```bash
npm run setup:backend
```

4. **Start development servers**
```bash
npm run dev
```

Aplikasi akan berjalan di:
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000

### Manual Setup (Alternative)

#### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

#### Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

## API Documentation

API tersedia di `http://localhost:8000/api/v1`

### Key Endpoints

#### Authentication
- `POST /auth/register` - User registration
- `POST /auth/login` - User login
- `GET /auth/me` - Get current user

#### Products
- `GET /products` - Get products with filters
- `GET /products/{slug}` - Get product details
- `GET /products/featured` - Get featured products

#### Cart
- `GET /cart` - Get user cart
- `POST /cart/items` - Add item to cart
- `PATCH /cart/items/{id}` - Update cart item

#### Orders
- `POST /checkout` - Process checkout
- `GET /orders` - Get user orders

## Environment Variables

### Backend (.env)
```env
APP_NAME="Toko Online"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
JWT_SECRET=your-jwt-secret
FRONTEND_URL=http://localhost:3000
```

### Frontend (.env.local)
```env
NEXT_PUBLIC_APP_NAME=Toko Online
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

## Development

### Backend Commands
```bash
cd backend
php artisan migrate:fresh --seed  # Reset database
php artisan test                  # Run tests
php artisan queue:work            # Process queues
```

### Frontend Commands
```bash
cd frontend
npm run build     # Build for production
npm run lint      # Run ESLint
npm run type-check # TypeScript check
```

## Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## Deployment

### Production Setup
1. Set environment variables for production
2. Build frontend: `cd frontend && npm run build`
3. Configure web server (Nginx/Apache)
4. Setup SSL certificates
5. Configure queue workers
6. Setup scheduled tasks

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## License

This project is licensed under the MIT License.