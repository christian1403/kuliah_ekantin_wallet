# Laravel E-Wallet Starter Kit

## Overview
This starter kit provides a comprehensive role-based Laravel application for an e-wallet system with three main user roles: Admin, Kasir (Cashier), and Mahasiswa (Student).

## Role-Based Architecture

### 1. Admin Role
**Purpose**: System administration and user management
**Controllers**: 
- `AdminController` - User CRUD operations
- `DashboardController` - Admin analytics and overview
- `UserManagementController` - Merchant, Kasir, and Mahasiswa management

**Features**:
- User management (create, read, update, delete users)
- Merchant registration and management
- Kasir account creation and oversight
- Student account management
- System-wide analytics and reporting
- Dashboard with key metrics and charts

**Key Routes**:
- `/admin/dashboard` - Main admin dashboard
- `/admin/users` - User management
- `/admin/merchants` - Merchant management
- `/admin/kasir` - Cashier management
- `/admin/mahasiswa` - Student management

### 2. Kasir (Cashier) Role
**Purpose**: Point of sale operations and product management
**Controllers**:
- `KasirController` - Dashboard, profile, and transaction overview
- `TransactionController` - POS transactions and payment processing
- `ProductController` - Product management for merchant

**Features**:
- POS transaction processing
- Product management (CRUD operations)
- Student balance verification
- Transaction history and reporting
- Inventory management
- Dashboard with sales analytics

**Key Routes**:
- `/kasir/dashboard` - Cashier dashboard
- `/kasir/transactions` - Transaction management
- `/kasir/products` - Product management
- `/kasir/pos` - Point of sale interface

### 3. Mahasiswa (Student) Role
**Purpose**: Wallet management and transaction monitoring
**Controllers**:
- `MahasiswaController` - Profile, dashboard, and merchant discovery
- `WalletController` - Wallet operations (top-up, transfer)
- `TransactionHistoryController` - Transaction history and analytics

**Features**:
- Wallet balance management
- Top-up functionality
- Student-to-student transfers
- Transaction history with filtering
- QR code generation for payments
- Merchant discovery and transaction history
- Spending analytics and reports

**Key Routes**:
- `/mahasiswa/dashboard` - Student dashboard
- `/mahasiswa/wallet` - Wallet management
- `/mahasiswa/transactions` - Transaction history
- `/mahasiswa/qr-code` - QR code for payments

## Request Validation Classes

### Admin Requests
- `UserRequest` - User creation validation
- `UserUpdateRequest` - User update validation with unique email handling
- `MerchantRequest` - Merchant creation/update validation

### Kasir Requests
- `TransactionRequest` - POS transaction validation with stock checking
- `ProductRequest` - Product CRUD validation with image upload
- `PaymentRequest` - Payment processing validation with balance verification

### Mahasiswa Requests
- `TopUpRequest` - Wallet top-up validation with daily/monthly limits
- `TransactionRequest` - Transfer validation with balance and limit checking
- `ProfileUpdateRequest` - Student profile update validation

## Middleware

### Role-Based Access Control
- `AdminMiddleware` - Ensures only admin users can access admin routes
- `KasirMiddleware` - Protects kasir routes and validates kasir profile existence
- `MahasiswaMiddleware` - Protects student routes and validates student profile/wallet

**Features**:
- Automatic role-based redirects
- Profile existence validation
- JSON response support for API requests
- Comprehensive error messages

## Database Models Included
- `User` - Base user authentication
- `Mahasiswa` - Student profiles with NIM, program, year
- `Kasir` - Cashier profiles linked to merchants
- `Merchant` - Business entities
- `Wallet` - Student wallet balances
- `WalletTransaction` - All wallet movements (credit/debit)
- `Produk` - Products sold by merchants
- `DetailPengeluaran` - Purchase transaction details
- `DetailPemasukan` - Income/top-up transaction details
- `MetodeBayar` - Payment methods

## Key Features

### Authentication & Authorization
- Laravel Fortify integration
- Spatie Permission package for role management
- Custom middleware for role-based access control
- Profile validation and error handling

### Business Logic
- Automatic balance updates
- Transaction limits and validation
- Stock management for products
- Daily/monthly spending limits
- Transfer restrictions and validation

### Data Validation
- Comprehensive form request validation
- Custom validation rules for business logic
- Error message customization
- Input sanitization and formatting

### API Support
- JSON response support
- AJAX endpoints for search functionality
- Mobile-friendly API structure
- Error handling for API requests

## Getting Started

### 1. Setup Database
```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed # Run all seeders
```

### 2. Create Test Users
The seeders will create:
- Admin users
- 23 Merchants with user accounts
- 13 Kasir linked to merchants
- 53 Mahasiswa with wallets

### 3. Development Workflow
1. **Admin Development**: Start with admin controllers and views
2. **Kasir Development**: Implement POS and product management
3. **Mahasiswa Development**: Build wallet and transaction features
4. **Frontend Development**: Create corresponding Inertia.js React components

### 4. Route Organization
Routes are organized by role in separate files:
- `routes/admin.php` - All admin routes
- `routes/kasir.php` - All kasir routes  
- `routes/mahasiswa.php` - All mahasiswa routes

## Customization

### Adding New Features
1. Create controllers in appropriate role directories
2. Add request validation classes in matching directories
3. Update route files with new endpoints
4. Create corresponding Inertia.js components

### Extending Roles
1. Add new roles in `RoleSeeder`
2. Create new middleware for role protection
3. Add role-specific controller directories
4. Create new route files for the role

### Database Extensions
1. Create new migrations for additional fields
2. Update model relationships
3. Add validation rules in request classes
4. Update seeders with new data

## Security Features
- Role-based access control
- Request validation with business rules
- Transaction limits and verification
- Balance verification before operations
- Profile existence validation
- Input sanitization and validation

## Development Tips
1. Use the custom `IdGenerator` utility for consistent ID generation
2. Implement the `HasCustomId` trait for new models
3. Follow the established controller structure for consistency
4. Use the provided request validation classes as templates
5. Maintain the role-based middleware pattern
6. Keep routes organized by role in separate files

This starter kit provides a solid foundation for e-wallet application development with proper separation of concerns, role-based access control, and comprehensive validation.