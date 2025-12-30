# Pro Elite Management System - Database Schema & Functionalities

**Last Updated:** December 30, 2025  
**System Status:** ✅ Fully Operational

---

## 📊 Database Schema Overview

### **Tables Structure**

#### 1. **PRODUCTS** `products` table DONE
```
id (Primary Key)
├── name (string) - Product name
├── sku (string, unique) - Stock Keeping Unit
├── image (string, nullable) - Product image path
├── type (enum: 'retail', 'material') - Product type
├── stock_qty (decimal 10,2) - Current quantity
├── buy_price (decimal 10,2) - Cost price
├── sell_price (decimal 10,2, nullable) - Selling price
├── alert_limit (decimal 10,2) - Low stock alert threshold
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Has Many → InventoryLogs
- Has Many → OrderItems

**Key Methods:**
- `isLowStock()` - Check if stock ≤ alert_limit
- `getFormattedStockAttribute()` - Format stock as currency

---

#### 2. **CUSTOMERS** `customers` table DONE
```
id (Primary Key)
├── name (string) - Customer name
├── email (string) - Email address
├── phone (string) - Phone number
├── address (text) - Street address
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Has Many → Orders

**Key Attributes:**
- `getTotalSpentAttribute()` - Sum of completed orders
- `getTotalOrdersAttribute()` - Count of all orders

---

#### 3. **ORDERS** `orders` table
```
id (Primary Key)
├── customer_id (FK → customers) - Customer reference
├── customer_name (string) - Denormalized name
├── vehicle_type (string, nullable) - Van type (e.g., "Ford Transit")
├── plate_number (string, nullable) - Vehicle plate
├── type (enum: 'product', 'service', 'both') - Order type
├── status (enum: 'pending', 'in_progress', 'completed', 'cancelled')
├── payment_status (enum: 'unpaid', 'partial', 'paid') - Payment state
├── total_amount (decimal 10,2) - Order total
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Belongs To → Customer
- Has Many → OrderItems
- Has One → JobOrder
- Has Many → Payments

**Status Scopes:**
- `pending()` - Not started
- `in_progress()` - Currently being worked on
- `completed()` - Finished
- `cancelled()` - Cancelled orders

---

#### 4. **ORDER_ITEMS** `order_items` table 
```
id (Primary Key)
├── order_id (FK → orders) - Order reference
├── product_id (FK → products, nullable) - Product if applicable
├── service_id (FK → services, nullable) - Service if applicable
├── quantity (decimal 10,2) - Quantity ordered
├── unit_price (decimal 10,2) - Price per unit
├── total_price (decimal 10,2) - quantity × unit_price
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Belongs To → Order
- Belongs To → Product (nullable)
- Belongs To → Service (nullable)

---

#### 5. **SERVICES** `services` table DONE
```
id (Primary Key)
├── name (string) - Service name (e.g., "Custom Paint")
├── base_labor_cost (decimal 10,2) - Base labor rate
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Has Many → OrderItems

---

#### 6. **JOB_ORDERS** `job_orders` table 
```
id (Primary Key)
├── order_id (FK → orders) - Associated order
├── status (enum: 'pending', 'in_progress', 'completed')
├── start_date (timestamp, nullable) - When work started
├── end_date (timestamp, nullable) - When work completed
├── notes (text, nullable) - Job notes/comments
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Belongs To → Order

---

#### 7. **INVENTORY_LOGS** `inventory_logs` table DONE
```
id (Primary Key)
├── product_id (FK → products) - Product affected
├── change_amount (decimal 10,2) - Amount changed (+/-)
├── reason (string) - Reason (sale, adjustment, damage, return, etc.)
├── reference_id (string, nullable) - Related order/document ID
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Belongs To → Product

**Purpose:** Complete audit trail of all stock changes

---

#### 8. **PAYMENTS** `payments` table
```
id (Primary Key)
├── order_id (FK → orders) - Order being paid
├── amount (decimal 10,2) - Payment amount
├── method (enum: 'cash', 'card', 'bank_transfer', 'check')
├── status (enum: 'pending', 'completed', 'failed')
├── reference (string, nullable) - Payment reference/receipt
├── paid_at (timestamp, nullable) - When payment was made
├── created_at (timestamp)
└── updated_at (timestamp)
```
**Relationships:**
- Belongs To → Order

---

#### 9. **USERS** `users` table
```
id (Primary Key)
├── name (string) - User name
├── email (string, unique) - Email address
├── email_verified_at (timestamp, nullable)
├── password (string) - Hashed password
├── role (enum: 'admin', 'staff', 'manager', 'viewer')
├── remember_token (string, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## 🎯 System Functionalities

### **1. PRODUCT INVENTORY MANAGEMENT**
**Location:** `/inventory` (ProductInventoryTable + ProductInventoryForm)

#### Features:
✅ **Create Products**
- Add new products with name, SKU, type, pricing
- Upload product images
- Set stock quantities and alert thresholds
- Support for Retail & Material types

✅ **Read Products**
- View all products in table format
- Mobile-responsive card layout
- Sort by any column (name, SKU, stock, price, date)
- Display product images with fallback icons

✅ **Update Products**
- Edit product details
- Update prices, stock, and alerts
- Change product images
- Modify product type

✅ **Delete Products**
- Remove products with confirmation dialog
- Livewire confirmation using `wire:confirm`
- Success/error flash messages

✅ **Search & Filter**
- Real-time search by product name or SKU (500ms debounce)
- Filter by product type (Retail/Material)
- Clear filters button

✅ **Pagination**
- 10 products per page (configurable)
- Responsive pagination controls
- First/Last item indicators

✅ **Low Stock Alerts**
- Visual indicator (⚠️) for low stock products
- Red background highlight for low stock rows
- Configurable alert threshold per product

---

### **2. ORDER MANAGEMENT**
**Location:** `/orders` (OrderManagement)

#### Features:
✅ **Create Orders**
- Link products and services to orders
- Add customer information
- Calculate total automatically
- Set order type (product, service, both)

✅ **Order Tracking**
- Status tracking: pending → in_progress → completed
- Payment status: unpaid → partial → paid
- Vehicle information (type, plate number)

✅ **Order Items**
- Add multiple items per order
- Mix products and services
- Automatic pricing calculation
- Quantity management

✅ **Search & Filter**
- Search by order number/customer
- Filter by status
- Filter by order type
- Date range filtering

---

### **3. CUSTOMER MANAGEMENT**
**Location:** `/customers` (CustomerManagement + CustomerForm)

#### Features:
✅ **Create Customers**
- Store customer details (name, email, phone, address)
- Validation on all fields

✅ **View Customers**
- List all customers
- Show total spent and order count
- Search functionality

✅ **Update Customers**
- Edit customer information
- Update contact details

✅ **Delete Customers**
- Remove customers (only if no associated orders)
- Safety check to prevent data loss

✅ **Customer Analytics**
- Total amount spent
- Number of orders placed
- Order history linked to customer

---

### **4. SERVICE MANAGEMENT**
**Location:** `/services` (ServiceManagement + ServiceForm)

#### Features:
✅ **Create Services**
- Add customization services
- Set base labor costs
- Examples: Custom Paint, Welding, Upholstery

✅ **Manage Services**
- View all available services
- Edit service details and pricing
- Delete services

✅ **Service Usage**
- Link services to orders
- Track service usage in order items
- Calculate service costs in orders

---

### **5. JOB BOARD**
**Location:** `/job-board` (JobBoard)

#### Features:
✅ **Job Tracking**
- Create job orders from orders
- Track job status (pending, in_progress, completed)
- Set start and end dates
- Add notes and comments

✅ **Job Management**
- View all active jobs
- Update job progress
- Mark jobs as complete

---

### **6. POINT OF SALE (POS)**
**Location:** `/pos` (PointOfSale)

#### Features:
✅ **Quick Sales**
- Fast product checkout
- Real-time inventory updates
- Instant receipt generation

✅ **Service POS**
**Location:** `/service-pos` (ServicePOS)
- Sell services
- Calculate labor costs
- Process service payments

---

### **7. PAYMENTS**
**Location:** Integrated in Orders

#### Features:
✅ **Payment Recording**
- Record payments for orders
- Multiple payment methods: cash, card, bank transfer, check
- Payment status tracking: pending, completed, failed
- Payment reference/receipt tracking

✅ **Payment Reconciliation**
- Mark orders as paid, partial, or unpaid
- Track remaining balance
- Payment history per order

---

### **8. INVENTORY TRACKING**
**Location:** `/inventory/history` (InventoryHistory)

#### Features:
✅ **Audit Trail**
- Log all stock changes
- Reasons: sale, adjustment, damage, return
- Reference to related orders
- Complete history with timestamps

✅ **Stock Adjustments**
**Location:** `/inventory/{id}/adjust` (QuickAdjustStock)
- Manual stock adjustments
- Record adjustment reasons
- Update alert limits

---

### **9. SALES DASHBOARD**
**Location:** `/dashboard` (SalesDashboard)

#### Features:
✅ **Analytics & KPIs**
- Total sales revenue
- Order statistics
- Customer metrics
- Inventory status overview

---

### **10. ADMIN PANEL**
**Location:** `/admin` (AdminPanel)

#### Features:
✅ **System Management**
- User management
- System settings
- Data management
- Report generation

---

## 🔗 Database Relationships Diagram

```
USERS (1) ──┐
            │
CUSTOMERS (1) ──┬─── (M) ORDERS (1) ──┬─── (M) ORDER_ITEMS ──┬─── (M) PRODUCTS
            │                         │                        │
            └─────────────────────────┴─── (1) JOB_ORDERS      │
                                      │                        │
                                      └─── (M) PAYMENTS        │
                                                                │
                                      SERVICES ─────────── (M) ORDER_ITEMS
                                                                │
                                                                │
                                      INVENTORY_LOGS ────── (M) PRODUCTS
```

---

## 📈 Data Flow

### **Product Sale Flow:**
1. Customer orders → Order created
2. Products added to OrderItems
3. Stock updated in Products table
4. InventoryLog created (audit trail)
5. Payment recorded
6. Order status updated
7. JobOrder created (if service)

### **Inventory Flow:**
1. Stock adjustment made
2. InventoryLog entry created
3. Product stock_qty updated
4. Alert threshold checked
5. Low stock notifications triggered

---

## 🔒 Data Integrity

**Foreign Keys:**
- Orders.customer_id → Customers.id
- OrderItems.order_id → Orders.id
- OrderItems.product_id → Products.id
- OrderItems.service_id → Services.id
- JobOrders.order_id → Orders.id
- Payments.order_id → Orders.id
- InventoryLogs.product_id → Products.id

**Unique Constraints:**
- Products.sku (unique SKU per product)
- Users.email (unique email per user)

---

## 📊 System Statistics

**Total Tables:** 9 (+ 3 Laravel default: users, cache, jobs)
**Total Models:** 9
**Total Migrations:** 16
**Livewire Components:** 18
**Views:** 20+

---

## ✨ Key Features Summary

| Feature | Status | Location |
|---------|--------|----------|
| Product CRUD | ✅ | /inventory |
| Customer Management | ✅ | /customers |
| Order Management | ✅ | /orders |
| Service Management | ✅ | /services |
| Job Tracking | ✅ | /job-board |
| POS System | ✅ | /pos |
| Inventory Audit | ✅ | /inventory/history |
| Payment Processing | ✅ | Integrated in Orders |
| Sales Dashboard | ✅ | /dashboard |
| Admin Panel | ✅ | /admin |
| Search & Filter | ✅ | All modules |
| Real-time Updates | ✅ | Livewire |
| Mobile Responsive | ✅ | All pages |
| Image Upload | ✅ | Products |

---

## 🚀 System Ready for Production

✅ All CRUD operations functional  
✅ Database relationships properly configured  
✅ Real-time updates with Livewire  
✅ Responsive mobile design  
✅ Error handling & validation  
✅ Audit trail logging  
✅ Role-based access ready  

---

**System Created:** December 2025  
**Framework:** Laravel 11 + Livewire 3  
**Database:** MySQL  
**Frontend:** Tailwind CSS + Alpine.js
