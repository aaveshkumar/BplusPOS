# System Options Page - Documentation

## 📍 Location
**URL:** `/admin/options`  
**Access:** Admin only (requires `manage_system` permission)

## 📋 Overview
The System Options page is a comprehensive dashboard that displays all 28 available features in the B-Plus POS system, organized by category with complete role-based access control information.

## 🎯 Purpose
- Provide administrators with a complete overview of all system features
- Show which roles have access to each feature
- Serve as a quick reference guide for feature URLs and descriptions
- Help administrators understand the full capabilities of the system

## 🎨 Features

### 1. **Role Legend**
Displays all four system roles with visual badges:
- 🔴 Admin (Full system access)
- 🔵 Manager (Most operational features)
- 🟢 Cashier (POS operations only)
- 🟡 Stock Manager (Inventory focus)

### 2. **Categorized Feature List**
All 28 features organized into 10 categories:
- **Core Operations** (4 features)
- **Order Management** (2 features)
- **Inventory** (3 features)
- **Analytics & Reports** (3 features)
- **Multi-Store** (1 feature)
- **Communications** (2 features)
- **Automation** (1 feature)
- **Promotions** (2 features)
- **Configuration** (4 features)
- **Integration** (2 features)
- **System** (4 features)

### 3. **Feature Details Table**
Each feature displays:
- **ID Number** - Sequential identifier
- **Feature Name** - With icon
- **Description** - What the feature does
- **URL** - Direct link to access the feature
- **Access Roles** - Visual badges showing which roles can access

### 4. **Quick Statistics**
Summary cards showing:
- Total features available to Admin
- Total features available to Manager
- Total features available to Cashier
- Total features available to Stock Manager

### 5. **System Information**
Footer section with:
- Total feature count
- Integration details
- Technologies used
- Special features highlights
- Version and date information

## 🚀 How to Access

### For Administrators:
1. Login with admin credentials
2. Click **"Options"** in the sidebar navigation
3. View the complete feature list

### Navigation:
- Sidebar link: `Options` (visible to Admin only)
- Direct URL: `/admin/options`

## 📊 Role-Based Access

The page shows exactly which features are available to each role:

| Role | Feature Count | Access Level |
|------|---------------|--------------|
| **Admin** | 28 | All features |
| **Manager** | 15 | Operational features |
| **Cashier** | 2 | POS and Notifications |
| **Stock Manager** | 5 | Inventory-focused |

## 🎨 Visual Design

- **Color-coded badges** for each role
- **Responsive tables** for easy viewing
- **Hover effects** on cards
- **Category grouping** for better organization
- **Direct links** to each feature
- **Icon indicators** for visual recognition

## 🔧 Technical Details

### Files Created/Modified:
1. **app/controllers/AdminController.php** - Added `options()` method
2. **app/views/admin/options.php** - New view page
3. **public/index.php** - Added route `/admin/options`
4. **app/views/layouts/header.php** - Added sidebar navigation link

### Security:
- Requires authentication (`requireAuth()`)
- Requires admin permission (`requirePermission('manage_system')`)
- Only accessible by Admin role

### Data Structure:
Features are defined with:
```php
[
    'id' => int,              // Unique identifier
    'category' => string,     // Group category
    'name' => string,         // Feature name
    'url' => string,          // Access URL
    'description' => string,  // What it does
    'admin' => bool,          // Admin access
    'manager' => bool,        // Manager access
    'cashier' => bool,        // Cashier access
    'stock_manager' => bool,  // Stock Manager access
    'icon' => string          // FontAwesome icon class
]
```

## 📈 Benefits

1. **Complete Feature Visibility** - See all available modules at a glance
2. **Access Control Reference** - Understand role permissions instantly
3. **Quick Navigation** - Direct links to every feature
4. **Documentation** - Built-in description of each feature
5. **System Overview** - Understand full system capabilities
6. **Training Aid** - Help new users learn the system

## 🎯 Use Cases

- **Onboarding new admins** - Show them all available features
- **Permission planning** - Decide which roles need access to what
- **System audit** - Review complete feature set
- **Feature discovery** - Find capabilities you didn't know existed
- **Documentation** - Quick reference for feature locations
- **Client demos** - Show system capabilities to clients

## 🔄 Future Enhancements

Potential improvements:
- Search/filter functionality
- Export to PDF/Excel
- Feature usage statistics
- Custom role configuration
- Feature enable/disable toggles
- Detailed feature documentation links

---

**Created:** October 31, 2025  
**Version:** 1.0.0  
**Access Level:** Admin Only  
**Total Features:** 28 modules
