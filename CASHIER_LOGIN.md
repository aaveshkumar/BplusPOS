# Cashier User Account

A cashier user account has been created successfully for the B-Plus POS system.

## Login Credentials

**Username:** `poscashier`  
**Password:** `123123`  
**Role:** Cashier  
**User ID:** 2676

## Access Level

The cashier role has the following permissions:
- Access POS terminal
- Process sales transactions
- View product catalog
- Search and select customers
- Process payments (Cash, Card, UPI)
- Print receipts
- Hold/retrieve orders
- View daily sales (own transactions)

## Restricted Access

Cashiers **cannot** access:
- Product management (add/edit products)
- Customer management (add/edit customers)
- Inventory management
- Reporting & analytics
- System settings
- User management
- Multi-store settings
- Financial reports

## Login URL

Access the POS system at:
- Development: `http://localhost:5000/login` (or current Replit URL)
- Production: `http://your-domain.com/login`

## Security Notes

- Change the default password after first login
- Passwords are hashed using WordPress-compatible phpass encryption
- All login attempts are logged in the activity log
- Session timeout: 8 hours (configurable)

## Testing the Account

1. Navigate to the login page
2. Enter username: `poscashier`
3. Enter password: `123123`
4. You will be redirected to the POS Terminal interface
5. Start processing sales transactions

---

**Created:** October 31, 2025  
**Status:** Active  
**Last Updated:** October 31, 2025
