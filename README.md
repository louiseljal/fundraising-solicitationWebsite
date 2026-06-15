# fundraising-solicitationWebsite
a project fundraising/solicitation website
figma: https://www.figma.com/design/yVlLW3a88wxt7qb3GBZbu1/Website?node-id=0-1&
t=zXeij6m6JUDsAAkR-1

Fundraising Solicitation Website -FundsOL

A comprehensive fundraising and solicitation platform built with PHP, MySQL, HTML, CSS, and JavaScript. This project enables users to create fundraising campaigns, make donations, submit solicitations, and provides administrators with powerful management tools including analytics dashboards and verification queues.

## Table of Contents
- [Features](#-features)
- [How to View the Site](#-how-to-view-the-site)
- [Project Structure](#-project-structure)
- [Tools Used](#-tools-used)
- [Database Architecture](#-database-architecture)
- [Admin Features](#-admin-features)
- [License](#-license)

## Features
- **User Authentication**: Secure login/registration system with role-based access control (Admin/Donor)
- **Campaign Management**: Users can create and manage fundraising campaigns with goals, categories, and deadlines
- **Donation Processing**: Multiple payment methods including Credit Card, PayPal, G_Cash, Bank Transfer, and Manual
- **Solicitation System**: Users can submit solicitation requests for admin approval
- **Admin Dashboard**: Comprehensive admin interface with KPI metrics and analytics
- **Verification Queues**: Admin approval workflow for donations, campaigns, and solicitations
- **User Management**: Admin can manage user roles and account status
- **Activity Logging**: Complete audit trail of system activities
- **OLAP Analytics**: ETL pipeline for data warehousing and advanced analytics
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5.3

## How to View the Site
To run this website, you need a local web server with PHP and MySQL support:

1. **Download** or clone this project repository to your computer
2. **Install** a local web server environment (XAMPP, WAMP, MAMP, or similar)
3. **Import** the database schema:
   - Import `fundraising_db.sql` for the operational database
   - Import `olap_schema.sql` for the analytics database
4. **Configure** database connection in `includes/db.php` and `includes/dw_db.php`
5. **Start** your web server and MySQL service
6. **Open** your browser and navigate to `http://localhost/your-project-folder/index.html`

## Project Structure
Here is a quick map of where all the project files live:

```text
├── index.html                    # Main homepage
├── login.html                    # User login page
├── registration.html              # User registration page
├── admin.php                     # Admin dashboard
├── manage_campaigns.php          # Campaign management interface
├── queues.php                    # Verification queues interface
├── membership.php                # User membership management
├── activity_log.php              # Activity log viewer
├── fundraising.html              # Fundraising page
├── donations.html                # Donations page
├── solicitations.html            # Solicitations feed
├── style.css                     # Main stylesheet
├── main.js                       # Core JavaScript functionality
├── admin.js                      # Admin-specific JavaScript
├── api/                          # Backend API endpoints
│   ├── admin_backend.php         # Admin dashboard API
│   ├── admin_protect.php         # Admin authentication
│   ├── campaigns.php            # Campaign CRUD operations
│   ├── donations.php             # Donation processing
│   ├── solicitations.php         # Solicitation management
│   ├── queues_backend.php        # Verification queue processing
│   ├── members.php               # User management API
│   ├── activity_logs.php         # Activity log API
│   └── etl_sync.php              # ETL pipeline for OLAP sync
├── includes/                     # Configuration and utilities
│   ├── db.php                    # Operational database connection
│   ├── dw_db.php                 # Data warehouse connection
│   └── session.php               # Session management
├── fpdf/                         # PDF generation library
├── uploads/                      # User uploaded files
│   └── solicitations/            # Solicitation attachments
├── fundraising_db.sql            # Operational database schema
├── olap_schema.sql               # OLAP data warehouse schema
└── README.md                     # This file
```

## Tools Used
- **PHP**: Server-side scripting for backend logic and API endpoints
- **MySQL**: Relational database for data storage (operational and OLAP)
- **HTML5**: Structure and content of web pages
- **CSS3**: Styling and responsive design with Bootstrap 5.3.0
- **JavaScript**: Client-side interactivity and AJAX requests
- **Bootstrap 5.3**: UI framework for responsive design and components
- **FPDF**: PDF generation library for reports
- **ETL Pipeline**: Data synchronization between operational and analytical databases

## Database Architecture
The project uses a dual-database architecture:

### Operational Database (fundraising_db)
- **users**: User accounts and authentication
- **user_profiles**: Extended user information
- **campaigns**: Fundraising campaign data
- **donations**: Transaction records
- **solicitations**: User-submitted solicitation requests
- **announcements**: System announcements
- **collections**: Offline donation collections

### OLAP Data Warehouse (olap_schema)
- **dim_campaign**: Campaign dimension table
- **dim_donor**: Donor dimension table
- **dim_time**: Time dimension table
- **dim_payment_method**: Payment method dimension
- **fact_donations**: Donation fact table
- **fact_campaign_performance**: Campaign performance metrics

The ETL pipeline (`api/etl_sync.php`) synchronizes data from the operational database to the OLAP warehouse for analytics purposes.

## Admin Features
The admin interface provides comprehensive management capabilities:

- **Dashboard**: Real-time KPIs including total funds raised, unique donors, pending actions, and active campaigns
- **Campaign Management**: Create, edit, archive, and manage fundraising campaigns with password verification
- **Verification Queues**: Review and approve/reject pending donations, draft campaigns, and solicitation requests
- **User Management**: View all users, modify roles (Admin/Donor), and manage account status (Active/Suspended)
- **Activity Logs**: Filterable audit trail of all system activities by date range and activity type
- **Security**: Role-based access control with admin password verification for sensitive operations

## License
This project is open-source and free for anyone to use under the [Apache License Version 2.0, January 2004](http://www.apache.org/licenses/)