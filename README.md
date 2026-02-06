# Order Payment API

## Project Overview

A Laravel-based RESTful API for managing orders and payments with extensible payment gateway architecture. The system implements clean code principles and allows seamless integration of new payment providers.

---

## Features

- **Order Management**: Create, update, delete, and view orders with status filtering
- **Payment Processing**: Simulate payment processing with multiple gateway support
- **JWT Authentication**: Secure API endpoints with JSON Web Tokens
- **Extensible Architecture**: Easy integration of new payment gateways using Strategy Pattern
- **Validation**: Comprehensive input validation with meaningful error messages
- **Pagination**: Efficient data retrieval for list endpoints
- **RESTful Design**: Industry-standard API structure

---

## Tech Stack

- **Framework**: Laravel 9.0
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: MySQL
- **PHP Version**: 8.0

---

## Installation

### Prerequisites
- PHP >= 8.0
- Composer
- MySQL
- Git

### Setup Instructions

**1. Clone the repository**
```
git clone https://github.com/proganas/extendable-order-payment-api
cd extendable-order-payment
```

**2. Install dependencies**
```
composer install
```

**3. Environment configuration**
```
cp .env.example .env
```

**4. Configure database in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=extendable_order_payment
DB_USERNAME=root
DB_PASSWORD=
```

**5. Generate application key**
```
php artisan key:generate
```

**6. Generate JWT secret**
```
php artisan jwt:secret
```

**7. Run migrations and seeders**
```
php artisan migrate:fresh --seed
```

**8. Start development server**
```
php artisan serve
```

The API will be available at: `http://127.0.0.1:8000/api`

---

## API Documentation

### Base URL
```
http://127.0.0.1:8000/api
```

### Authentication
All endpoints (except register/login) require JWT token:
```
Authorization: Bearer {token}
```

### Available Endpoints

#### Authentication
- `POST /register` - Register
- `POST /login` - Login
- `POST /logout` - Logout

#### Orders
- `GET /orders` - Get all orders (supports `?status=pending|confirmed|cancelled`)
- `POST /orders` - Create new order
- `PUT /orders/{id}` - Update order
- `POST /orders/{id}/confirm` - Confirm order
- `POST /orders/{id}/cancel` - Cancel order
- `DELETE /orders/{id}` - Delete order

#### Payments
- `POST /orders/{id}/pay` - Process payment
- `GET /payments` - Get all user payments
- `GET /orders/{id}/payments` - Get order payments

### Postman Collection
Import the file `Extendable Order Payment API.postman_collection.json` into Postman for complete API documentation with examples.

---

## Payment Gateway Extensibility

### Architecture Overview

The system uses **Strategy Pattern** combined with **Factory Pattern** for payment gateway management, ensuring:
- **Loose coupling**: Payment logic separated from business logic
- **Easy extension**: Add new gateways without modifying existing code
- **Testability**: Each gateway can be tested independently

### Current Implementation

**Directory Structure:**
```
app/
├── Services/
│   └── Payments/
│       ├── PaymentService.php 
│       └── Gateways/
│           ├── PaymentGatewayInterface.php
│           ├── PaymentGatewayFactory.php
│           ├── PaypalGateway.php
│           └── StripeGateway.php
```

### Adding a New Payment Gateway

Follow these steps to add a new payment provider (e.g., "Fawry"):

#### Step 1: Create Gateway Class

Create file: `app/Services/Payments/Gateways/FawryGateway.php`

```php
<?php

namespace App\Services\Payments\Gateways;

class FawryGateway implements PaymentGatewayInterface
{
    public function process(float $amount): array
    {
        return [
            'status' => 'successful',
            'transaction_id' => uniqid('fawry_'),
        ];
    }
}
```

#### Step 2: Register in Factory

Edit: `app/Services/Payments/Gateways/PaymentGatewayFactory.php`

```php
public static function make(PaymentGateway $gateway): PaymentGatewayInterface
{
    return match ($gateway->code) {
        'paypal' => new PaypalGateway(),
        'stripe' => new StripeGateway(),
        'fawry' => new FawryGateway(),
        default => throw new \Exception('Unsupported payment gateway'),
    };
}
```

#### Step 3: Add to Database

Create a seeder or run directly:

```php
use App\Models\PaymentGateway;

PaymentGateway::create([
    'name' => 'Fawry',
    'code' => 'fawry',
    'is_active' => true,
]);
```

#### Step 4: Use Gateway Configuration

Modify gateway class to accept configuration:

```php
class FawryGateway implements PaymentGatewayInterface
{    
    public function process(float $amount): array
    {
        return [
            'status' => 'successful',
            'transaction_id' => uniqid('fawry_'),
        ];
    }
}
```

Update factory to pass configuration:

```php
public static function make(PaymentGateway $gateway): PaymentGatewayInterface
{
    $config = json_decode($gateway->configuration, true) ?? [];
    
    return match ($gateway->code) {
        'paypal' => new PaypalGateway($config),
        'stripe' => new StripeGateway($config),
        'fawry' => new FawryGateway($config),
        default => throw new \Exception('Unsupported payment gateway'),
    };
}
```

### Gateway Interface Contract

All payment gateways must implement:

```php
interface PaymentGatewayInterface
{
    public function process(float $amount): array;
}
```

### Benefits of This Architecture

✅ **Zero modification** to existing code when adding new gateways  
✅ **Configuration-driven** via database or .env  
✅ **Easy testing** - mock gateway in tests  
✅ **Maintainable** - each gateway is isolated  
✅ **Scalable** - add unlimited payment providers

---

## Business Rules

### Orders
- Orders are created with `pending` status
- Only `pending` orders can be updated
- Only `pending` orders can be confirmed or cancelled
- Only `pending` orders without payments can be deleted
- Total amount is calculated automatically: `price × quantity`

### Payments
- Payments can only be processed for `confirmed` orders
- Payment gateway must be active (`is_active = true`)
- Each payment is linked to a specific order
- Payment status can be: `pending`, `successful`, or `failed`

---

## Database Schema

### Tables

**users**
- id, name, email, password, timestamps

**orders**
- id, user_id, name, price, quantity, status, total_amount, timestamps

**payment_gateways**
- id, code, name, is_active, configuration (JSON), timestamps

**payments**
- id, order_id, payment_gateway_id, status, amount, transaction_id, gateway_response (JSON), timestamps

### Relationships
- User → hasMany → Orders
- Order → belongsTo → User
- Order → hasMany → Payments
- Payment → belongsTo → Order
- Payment → belongsTo → PaymentGateway
