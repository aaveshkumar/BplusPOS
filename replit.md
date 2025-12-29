# B-Plus POS System

## Overview
B-Plus POS is a comprehensive PHP-based Point-of-Sale system designed for WooCommerce-powered stores. It supports multi-store management, various user roles (Admin, Stock Manager, Cashier), real-time inventory tracking, order processing, customer management, and sales reporting. The system aims to provide a robust web-based POS solution with advanced features like coupon/loyalty integration, detailed reporting including GST compliance, and workflow automation. It focuses on a hybrid integration model, leveraging remote MySQL for read operations and the WooCommerce REST API for write operations, with recent additions for standalone database support. The project's ambition is to offer a full-featured, secure, and scalable POS system for retail environments.

## User Preferences
- Stack: PHP (as specifically requested despite Replit limitations)
- Architecture: MVC pattern
- Database: Remote MySQL + WooCommerce API hybrid approach
- Security: Production-grade security for trusted internal operators

## System Architecture
The B-Plus POS system follows an MVC (Model-View-Controller) architectural pattern.

### UI/UX Decisions
The POS interface has been redesigned for a clean, full-width layout with a sticky cart sidebar. It uses AJAX extensively for zero page reloads, featuring auto-add to cart via barcode/SKU, customer AJAX search with Select2, and infinite scroll for product loading. Design elements include modern gradient headers, color-coded stock indicators, and SALE badges. Admin interfaces are responsive, using Bootstrap 5, with interactive modals, data tables, and statistical dashboards.

### Technical Implementations
- **Core Technology**: PHP 8.4
- **Database Integration**: Hybrid approach using remote MySQL for read operations (products, inventory, customers, orders) from WooCommerce and WooCommerce REST API for write operations (create orders, update stock, manage customers). Recently, standalone database support was added, allowing configuration-based switching between WordPress/WooCommerce and a dedicated PHP database.
- **Security**: Features include PDO with prepared statements, WordPress-compatible password hashing, CSRF protection, secure session management, comprehensive input validation, SQL injection prevention, server-side price verification, XSS protection, and role-based access control.
- **User Roles**: Admin (full access), Stock Manager (inventory/product management), Cashier (POS operations).
- **Customer Management**: Fully integrated with WordPress `wp_users` and `wp_usermeta` tables, ensuring all customers are WordPress user accounts with specific role filtering to exclude staff. Optimized search and CRUD operations are provided.
- **Tax Calculation**: Custom tax rules are applied based on product categories, calculated after all discounts, and support tax-inclusive extraction.
- **Discount & Loyalty**: Coupon code input with WooCommerce integration and loyalty points redemption from customer WordPress profiles. Server-side validation ensures security.
- **Admin Modules**: Implements comprehensive modules for Cashier Sessions, Multi-Store Management, Advanced Reports (including GST compliance), WhatsApp Business Integration, Discount & Coupon Management, Payment Methods Configuration, and Workflow Automation.
- **Deployment**: Supports local development environments (PHP built-in server, XAMPP) and Hostinger production deployment with an interactive setup wizard for easy configuration.

### Feature Specifications
- **Multi-store management**: Centralized control over multiple retail locations.
- **Real-time inventory tracking**: Synchronized stock levels.
- **Order management**: Processing, holding, and resuming orders.
- **Customer management**: Search, creation, and update of customer profiles.
- **Sales reporting & analytics**: Daily, weekly, monthly sales, inventory, customer, and payment reports.
- **GST Reports**: Generation of GSTR-1, GSTR-2, GSTR-3B, GSTR-9 reports, including calculation and compliance tools.
- **Automated Notifications**: WhatsApp Business integration for various customer communications.
- **Payment Gateway Mapping**: Customizable mapping of POS payment methods to WooCommerce gateways.

## External Dependencies
- **WooCommerce**:
    - **Remote MySQL Database**: Used for reading product, inventory, customer, and order data.
    - **REST API**: Used for writing operations such as creating orders, updating stock, and managing customers.
- **PHP**: PHP 8.4 is the core language.
- **MySQL**: The primary database system, used both remotely for WooCommerce integration and as a standalone option.
- **Select2**: JavaScript library used for enhanced customer search dropdowns.
- **Bootstrap 5**: Front-end framework for responsive design.
- **Payment Gateways**: Integration points for services like Razorpay, Stripe, PayU (configurable).
- **WhatsApp API Providers**: Supports various providers like Twilio, Gupshup, MSG91, Wati.io for WhatsApp Business integration.