# E-Commerce Order Management API

A scalable REST API for an e-commerce order management system built with Laravel 12, featuring product inventory tracking, order processing, and role-based authentication.

## Features

### Product & Inventory Management
- Product CRUD with variants (size, color, etc.)
- Real-time inventory tracking
- Low stock alerts (queue job)
- Bulk product import via CSV
- Product search with full-text search

### Order Processing
- Create orders with multiple items
- Order status workflow: Pending → Processing → Shipped → Delivered → Cancelled
- Inventory deduction on order confirmation
- Order rollback on cancellation (restore inventory)
- Invoice generation (PDF)
- Email notifications for order updates

### Authentication & Authorization
- JWT authentication with refresh tokens
- Role-based access: Admin, Vendor, Customer
- Admin: Full access
- Vendor: Manage own products and orders
- Customer: Place orders, view order history

## Project Architecture

### Directory Structure
```
app/
├── Actions/          # Complex business logic actions
├── Events/           # Event classes for decoupled operations
├── Http/
│   ├── Controllers/  # API controllers
│   ├── Middleware/   # Custom middleware
│   └── Requests/     # Form request validation
├── Jobs/             # Queue jobs for async processing
├── Listeners/        # Event listeners
├── Models/           # Eloquent models
├── Providers/        # Service providers
├── Repositories/     # Data access layer
└── Services/         # Business logic services

database/
├── factories/        # Model factories for testing
├── migrations/       # Database migrations
└── seeders/          # Database seeders

tests/                # PHPUnit tests
├── Feature/          # Feature tests
└── Unit/             # Unit tests
```

### Design Patterns Used
- **Repository Pattern**: Abstracts data access logic
- **Service Layer**: Contains business logic
- **Action Classes**: For complex operations like order creation
- **Observer Pattern**: Events and listeners for notifications
- **Strategy Pattern**: Different behaviors based on user roles

### Key Components

#### Models
- `User`: Authentication and role management
- `Product`: Product information
- `ProductVariant`: Product variations (size, color, etc.)
- `Inventory`: Stock tracking
- `Order`: Order management
- `OrderItem`: Individual order items
- `Invoice`: PDF invoice generation

#### Services
- `OrderService`: Order business logic
- `ProductService`: Product management
- `InventoryService`: Stock management

#### Actions
- `CreateOrderAction`: Handles order creation with inventory checks

#### Jobs
- `SendOrderEmail`: Async email notifications
- `GenerateInvoicePdf`: PDF generation
- `LowStockNotification`: Inventory alerts
- `BulkImportProducts`: CSV import

## Technical Stack

- Laravel 12 with PHP 8.2+
- JWT Authentication (tymon/jwt-auth)
- PDF Generation (barryvdh/laravel-dompdf)
- Queue Jobs for async operations
- Repository Pattern for data access
- Service classes for business logic
- Actions/Commands for complex operations
- Events & Listeners for decoupled logic
- Database transactions for data integrity
- API versioning (v1)

## Prerequisites

Before setting up the project, ensure you have the following installed on your system:

- **PHP 8.2 or higher** - [Download PHP](https://www.php.net/downloads)
- **Composer** - PHP dependency manager [Get Composer](https://getcomposer.org/)
- **Node.js and npm** - For frontend assets [Download Node.js](https://nodejs.org/)
- **SQLite** (for local development) or **MySQL** (for production)
- **Git** - Version control system

## Performance & Scalability

- Query optimization (N+1 prevention, eager loading)
- Database indexing on searchable fields
- Response pagination for large datasets

### Database Sharding Strategy

For high-scale deployments, implement horizontal database sharding:

1. **User Sharding**: Shard users by region or user ID range
2. **Product Sharding**: Shard products by vendor ID
3. **Order Sharding**: Shard orders by creation date or order ID
4. **Inventory Sharding**: Shard inventory by product variant ID

Implementation approach:
- Use Laravel's database sharding features
- Implement shard key routing in repositories
- Use read replicas for reporting queries
- Implement cross-shard transactions carefully

## Local Setup

Follow these steps to set up the project locally:

### 1. Clone the Repository
```bash
git clone https://github.com/mhbabu/e-commerce-order-management-api
cd e-commerce-order-management-api
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```

Edit the `.env` file to configure your environment settings (see Environment Variables section below).

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Generate JWT Secret
```bash
php artisan jwt:secret
```

### 6. Database Setup
For local development, the project uses SQLite by default. The database file is already created at `database/database.sqlite`.

If you prefer MySQL, update the `DB_CONNECTION` in `.env` and create the database.

Run migrations:
```bash
php artisan migrate
```

Optional: Seed the database with sample data:
```bash
php artisan db:seed
```

### 7. Install Frontend Dependencies
```bash
npm install
npm run build
```

### 8. Generate API Documentation
```bash
php artisan l5-swagger:generate
```

### 9. Start the Application
Start the Laravel development server:
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### 10. Start Queue Worker (for background jobs)
In a separate terminal:
```bash
php artisan queue:work
```

### Alternative: Use Composer Scripts
You can also use the predefined composer scripts:
```bash
composer run setup  # Installs dependencies and sets up the project
composer run dev    # Starts server, queue worker, and Vite dev server concurrently
```

## Environment Variables

Copy `.env.example` to `.env` and configure the following key variables:

```env
APP_NAME=ECommerceAPI
APP_ENV=local
APP_KEY=  # Generated by php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
# For local development (SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# For production (MySQL)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=ecommerce
# DB_USERNAME=root
# DB_PASSWORD=

# JWT Authentication
JWT_SECRET=  # Generated by php artisan jwt:secret
JWT_TTL=1440
JWT_REFRESH_TTL=20160

# Mail Configuration (for order notifications)
MAIL_MAILER=log  # Use 'log' for local development
# MAIL_MAILER=smtp
# MAIL_HOST=mailhog
# MAIL_PORT=1025
# MAIL_USERNAME=null
# MAIL_PASSWORD=null
# MAIL_ENCRYPTION=null
# MAIL_FROM_ADDRESS="hello@example.com"
# MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=database

# Cache & Session
CACHE_STORE=database
SESSION_DRIVER=database
```

## API Authentication

Use JWT tokens for authentication:

1. Register/Login to get token
2. Include token in Authorization header: `Bearer {token}`
3. Refresh token before expiry

## Testing

Run the test suite:
```bash
php artisan test
```

Run specific test files:
```bash
php artisan test tests/Feature/AuthTest.php
```

## API Documentation

This project uses Swagger/OpenAPI for API documentation. The documentation is automatically generated from controller annotations.

### Generating API Documentation
After making changes to API endpoints or annotations, regenerate the documentation:
```bash
php artisan l5-swagger:generate
```

### Accessing Swagger UI
Once the application is running, access the interactive API documentation at:
```
http://localhost:8000/api/documentation
```

The Swagger UI provides:
- Interactive API testing
- Detailed endpoint descriptions
- Request/response examples
- Authentication setup

### API Versioning
All endpoints are prefixed with `/api/v1/`.

### Authentication
The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:
```
Authorization: Bearer {your-jwt-token}
```

## API Documentation

### Authentication
- `POST /api/v1/register` - Register user
- `POST /api/v1/login` - Login user
- `POST /api/v1/refresh` - Refresh token
- `POST /api/v1/logout` - Logout user
- `GET /api/v1/me` - Get current user

### Products (Vendor/Admin)
- `GET /api/v1/products` - List products
- `POST /api/v1/products` - Create product
- `GET /api/v1/products/{id}` - Get product
- `PUT /api/v1/products/{id}` - Update product
- `DELETE /api/v1/products/{id}` - Delete product
- `POST /api/v1/products/bulk-import` - Bulk import products from CSV

### Orders
- `GET /api/v1/orders` - List orders
- `POST /api/v1/orders` - Create order
- `GET /api/v1/orders/{id}` - Get order
- `PATCH /api/v1/orders/{id}/status` - Update order status
- `POST /api/v1/orders/{id}/cancel` - Cancel order

## Postman Collection

A Postman collection is included for testing the API endpoints. Import the collection file:

```
POSTMAN COLLECTION/E-commerce-Order-Management-API.postman_collection.json
```

The collection includes:
- Pre-configured requests for all endpoints
- Environment variables for base URL and authentication tokens
- Example request bodies and parameters
- Tests for validating responses

### Using the Postman Collection
1. Import the collection into Postman
2. Set up environment variables:
   - `base_url`: `http://localhost:8000`
   - `token`: (will be set after login)
3. Run the authentication requests first to obtain a token
4. Use the token for authenticated requests

## Bulk Product Import

The API supports bulk importing products from CSV files. This feature allows vendors and admins to upload multiple products at once.

### CSV Format Requirements

The CSV file must contain the following columns (headers are required):

- `name` (required): Product name
- `description` (optional): Product description
- `sku` (required): Unique product SKU
- `base_price` (required): Base price (numeric)
- `category` (optional): Product category (defaults to "General")
- `color` (optional): Product color variant
- `size` (optional): Product size variant
- `storage` (optional): Storage capacity variant
- `variant_sku` (optional): Variant-specific SKU (defaults to product SKU + "-VAR")
- `quantity` (optional): Initial inventory quantity (defaults to 0)
- `low_stock_threshold` (optional): Low stock alert threshold (defaults to 10)
- `price_modifier` (optional): Price adjustment for variant (defaults to 0)

### Sample CSV File

A demo CSV file is provided: [`demo-products.csv`](demo-products.csv)

This file contains sample products across different categories (Electronics, Footwear) with various variants.

### Bulk Import API Usage

**Endpoint:** `POST /api/v1/products/bulk-import`

**Authentication:** Required (Vendor/Admin role)

**Content-Type:** `multipart/form-data`

**Parameters:**
- `file`: CSV file (max 10MB)

**Example using curl:**
```bash
curl -X POST "http://localhost:8000/api/v1/products/bulk-import" \
  -H "Authorization: Bearer {your-jwt-token}" \
  -F "file=@demo-products.csv"
```

**Response:**
```json
{
  "message": "Bulk import has been queued for processing. You will receive a notification when completed.",
  "total_rows": 11
}
```

### Validation Rules

- File must be a valid CSV format
- Required headers: `name`, `sku`, `base_price`
- Each row must have the same number of columns as the header
- Product SKUs must be unique per vendor
- Numeric fields (`base_price`, `quantity`, `low_stock_threshold`, `price_modifier`) must contain valid numbers
- File size limit: 10MB

### Processing

- Import is processed asynchronously using queue jobs
- Invalid rows are logged and skipped
- Successful imports create products with variants and inventory
- Check application logs for detailed error information

## Caching Strategy

- Cache frequently accessed products
- Cache user permissions
- Cache inventory counts
- Use Redis for session and cache storage

## Queue Configuration

- Use database queue for simplicity
- Separate queues for emails, PDFs, notifications
- Monitor queue performance

## API Rate Limiting

Implemented via middleware (configurable in routes).

## Your Information

- Name: Mahadi Hassan (Babu)
- Email: mahadihassan.cse@gmail.com
- GitHub: https://github.com/mhbabu

## License

MIT License
