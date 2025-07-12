# Sky Pharmacy Management System

A comprehensive pharmacy management system built with PHP, MySQL, and modern web technologies. This system provides complete pharmacy operations management including inventory, prescriptions, sales, AI-powered recommendations, and multi-role user management.

## 🌟 Features

### 🔐 Multi-Role Authentication System

- **Admin**: Full system management, user management, reports, settings
- **Pharmacist**: Inventory management, prescription approval, AI insights, reports
- **Cashier**: Order processing, payment handling, receipt printing
- **Customer**: Drug search, prescription upload, order placement, profile management

### 💊 Drug & Inventory Management

- Complete drug catalog with categories, strengths, and dosage forms
- Real-time stock tracking with reorder level alerts
- Batch management with expiry date tracking
- Prescription requirement flags
- Stock adjustment and transfer operations

### 📋 Prescription Management

- Digital prescription upload and storage
- Pharmacist review and approval workflow
- Prescription-to-order conversion
- Status tracking (pending, approved, rejected, dispensed)

### 🛒 Order & Sales Management

- Customer order placement with cart functionality
- Multi-payment method support (Cash, Card, Mobile Money, Bank Transfer)
- Order status tracking (pending, processing, completed, cancelled)
- Tax and discount calculation
- Sales transaction recording

### 🤖 AI-Powered Health Assistant

- Symptom-based drug recommendations
- Integration with Google Gemini AI
- Available drug suggestions
- Health advice and guidance

### 📊 Comprehensive Reporting

- Sales analytics and revenue reports
- Inventory reports with expiry tracking
- User activity reports
- Top-selling drugs analysis
- Multi-format export (CSV, PDF, Excel)

### 🖨️ Receipt & Documentation

- Professional receipt generation
- Prescription document management
- Report export functionality

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **AI Integration**: Google Gemini API
- **File Upload**: PDF, Image support
- **Export**: CSV, PDF generation

## 📁 Project Structure

```
pharmacy/
├── admin/                 # Admin dashboard and management
│   ├── dashboard.php     # Admin main dashboard
│   ├── users.php         # User management
│   ├── reports.php       # Comprehensive reports
│   ├── export_report.php # Report export functionality
│   └── settings.php      # System settings
├── pharmacist/           # Pharmacist operations
│   ├── dashboard.php     # Pharmacist dashboard
│   ├── manage_inventory.php # Inventory management
│   ├── prescriptions.php # Prescription management
│   ├── ai_insights.php   # AI recommendations
│   └── reports.php       # Pharmacist reports
├── cashier/              # Cashier operations
│   ├── dashboard.php     # Cashier dashboard
│   ├── process_orders.php # Order processing
│   └── print_receipts.php # Receipt printing
├── customer/             # Customer interface
│   ├── dashboard.php     # Customer dashboard
│   ├── profile.php       # Profile management
│   └── orders.php        # Order history
├── classes/              # PHP Classes
│   ├── Auth.php          # Authentication & authorization
│   ├── Drug.php          # Drug management
│   ├── Order.php         # Order & sales management
│   ├── Prescription.php  # Prescription handling
│   └── AIService.php     # AI integration
├── api/                  # API endpoints
│   ├── get_order_details.php
│   ├── get_drug_details.php
│   ├── search_drugs.php
│   └── update_stock.php
├── config/               # Configuration
│   └── database.php      # Database connection
├── database/             # Database schema
│   └── schema.sql        # Complete database structure
├── uploads/              # File uploads
│   └── prescriptions/    # Prescription documents
├── server/               # Node.js AI server
│   ├── index.js          # AI server main file
│   ├── controller/       # AI controllers
│   └── routes/           # API routes
├── index.php             # Main entry point
├── logout.php            # Logout functionality
└── README.md             # This file
```

## 🚀 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Node.js 14+ (for AI server)
- Google Gemini API key

### Database Setup

1. Create a MySQL database
2. Import the schema from `database/schema.sql`
3. Update database credentials in `config/database.php`

### Web Server Setup

1. Clone or download the project to your web server directory
2. Set proper permissions for uploads directory
3. Configure your web server to point to the project directory

### AI Server Setup

1. Navigate to the `server` directory
2. Install dependencies: `npm install`
3. Add your Google Gemini API key to the configuration
4. Start the server: `npm start`

### Default Login Credentials

- **Admin**: admin / password
- **Pharmacist**: pharmacist1 / password
- **Cashier**: cashier1 / password

## 💡 Key Features Explained

### Role-Based Access Control

The system implements comprehensive role-based access control:

- Each user has a specific role with defined permissions
- Pages and functionality are restricted based on user roles
- Session management ensures secure access

### Inventory Management

- Real-time stock tracking with automatic updates
- Batch management for expiry date tracking
- Reorder level alerts for low stock
- Stock adjustment operations with audit trail

### AI Integration

- Google Gemini AI provides intelligent drug recommendations
- Symptom-based suggestions with available drug filtering
- Health advice and guidance for customers
- AI insights for pharmacists

### Reporting System

- Comprehensive sales and revenue analytics
- Inventory reports with expiry tracking
- User activity and registration reports
- Export functionality in multiple formats

### Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with output escaping
- Session-based authentication
- Role-based authorization

## 🔧 Configuration

### Database Configuration

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sky_pharmacy');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### AI Server Configuration

Edit `server/index.js`:

```javascript
const GEMINI_API_KEY = "your_gemini_api_key";
```

### File Upload Configuration

- Maximum file size: 10MB
- Supported formats: PDF, JPG, PNG
- Upload directory: `uploads/prescriptions/`

## 📊 Database Schema

The system includes the following main tables:

- `users` - User accounts and authentication
- `drugs` - Drug inventory and information
- `drug_categories` - Drug categorization
- `drug_batches` - Batch tracking with expiry dates
- `prescriptions` - Prescription management
- `orders` - Customer orders
- `order_items` - Order line items
- `sales` - Sales transactions
- `refund_requests` - Refund management
- `refunds` - Processed refunds
- `ai_recommendations` - AI interaction logs

## 🎯 Use Cases

### For Pharmacists

1. Manage drug inventory and stock levels
2. Review and approve customer prescriptions
3. Access AI-powered drug recommendations
4. Generate inventory and sales reports
5. Track drug expiry dates and batches

### For Cashiers

1. Process customer orders and payments
2. Print receipts and invoices
3. Manage order status updates
4. Track daily sales transactions

### For Customers

1. Search and browse available drugs
2. Upload prescriptions for review
3. Place orders with multiple payment options
4. Access AI health recommendations

### For Administrators

1. Manage all user accounts and roles
2. Generate comprehensive system reports
3. Configure system settings
4. Monitor system activity and performance
5. Export data in various formats

## 🔒 Security Considerations

- All user inputs are validated and sanitized
- SQL queries use prepared statements
- Passwords are hashed using bcrypt
- Session management prevents unauthorized access
- File uploads are restricted and validated
- API endpoints require authentication

## 🚀 Deployment

### Production Deployment

1. Set up a production web server (Apache/Nginx)
2. Configure SSL certificates for HTTPS
3. Set up database with proper security
4. Configure file upload limits
5. Set up automated backups
6. Monitor system performance

### Docker Deployment (Optional)

Create a `docker-compose.yml` file for containerized deployment:

```yaml
version: "3.8"
services:
  web:
    image: php:8.1-apache
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: sky_pharmacy
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is developed for educational purposes as a college final project.

## 👨‍💻 Developer

Developed as a comprehensive pharmacy management system for college final project requirements.

## 📞 Support

For support or questions, please refer to the project documentation or contact the development team.

---

**Sky Pharmacy Management System** - A complete solution for modern pharmacy operations management.
#   p h a r m a c y _ w i t h _ A I  
 