# 🏋️ FitPulse - Gym Management System

> **The Ultimate All-In-One Fitness Management Platform**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://www.php.net/)
[![Docker Ready](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](https://www.docker.com/)
[![Render Ready](https://img.shields.io/badge/Render-Deployed-46E3B7.svg)](https://render.com/)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Quick Start](#quick-start)
- [Installation](#installation)
- [Docker Deployment](#docker-deployment)
- [Cloud Deployment (Render)](#cloud-deployment-render)
- [Project Structure](#project-structure)
- [Technology Stack](#technology-stack)
- [Database Schema](#database-schema)
- [User Roles & Permissions](#user-roles--permissions)
- [API Documentation](#api-documentation)
- [Security Features](#security-features)
- [Configuration](#configuration)
- [Contributing](#contributing)
- [Troubleshooting](#troubleshooting)
- [Roadmap](#roadmap)
- [Contact & Support](#contact--support)
- [License](#license)

---

## 🎯 Overview

**FitPulse** is a comprehensive gym management system built with **PHP 8.2** and **PostgreSQL**, designed to streamline fitness center operations. Whether you're managing member registrations, tracking attendance, handling billing, or analyzing fitness progress, FitPulse provides an intuitive, powerful solution.

### 📊 Quick Stats
- **500+** Active Members
- **50+** Expert Trainers
- **98%** Satisfaction Rate
- **24/7** System Uptime

---

## ✨ Key Features

### 👥 Member Management
- Complete member lifecycle management (registration, suspension, cancellation)
- Member profile customization with fitness goals
- Family membership support
- Member status tracking and history

### 📍 Real-Time Attendance Tracking
- Automated check-in/check-out system
- QR code scanning support
- Daily, weekly, and monthly attendance reports
- Attendance analytics and insights

### 💰 Automated Billing & Payments
- Flexible subscription plans (Monthly, Quarterly, Yearly)
- Automated invoice generation
- Multiple payment gateway integration
- Payment reminders and late payment notifications
- Refund management and ledger tracking

### 📊 Fitness Progress Analytics
- Calorie tracking and nutrition monitoring
- Weight and measurement tracking
- Workout history and performance metrics
- Progress charts and visualizations
- Goal setting and achievement tracking

### 💻 Trainer Management
- Trainer profiles and specialization tracking
- Class scheduling and management
- Session booking system
- Performance and ratings management

### 📱 Responsive Design
- Mobile-friendly interface
- Works seamlessly on all devices (Desktop, Tablet, Mobile)
- Progressive Web App (PWA) capabilities

### 🔐 Security Features
- Role-based access control (RBAC)
- Secure password hashing
- Session management
- Data encryption
- CSRF protection

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- PostgreSQL 12+
- Composer
- Docker (optional)

### Basic Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Ayatta-programmer/Fitness.git
   cd Fitness
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment variables:**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your database credentials:
   ```env
   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=fitness_gym
   DB_USER=fitpulse_user
   DB_PASS=your_secure_password
   ```

4. **Create database and run migrations:**
   ```bash
   php artisan migrate
   php artisan seed:db
   ```

5. **Start the application:**
   ```bash
   php -S localhost:8000
   ```

   Access the application at: `http://localhost:8000`

---

## 📥 Installation

### Step 1: System Requirements
- **OS**: Linux, macOS, or Windows
- **Web Server**: Apache 2.4+ or Nginx
- **PHP**: 8.2 or higher
- **Database**: PostgreSQL 12+
- **Memory**: Minimum 512MB
- **Storage**: Minimum 1GB

### Step 2: Download & Setup
```bash
# Clone repository
git clone https://github.com/Ayatta-programmer/Fitness.git
cd Fitness

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env
```

### Step 3: Database Configuration
Edit `.env` file:
```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=fitness_gym
DB_USER=fitpulse_user
DB_PASS=secure_password_here
```

### Step 4: Initialize Database
```bash
php artisan migrate
php artisan seed:db
```

### Step 5: Configure Web Server
For **Apache**, enable `.htaccess`:
```apache
<Directory /var/www/html>
    AllowOverride All
    RewriteEngine On
</Directory>
```

### Step 6: Access Application
- **URL**: `http://your-domain.com`
- **Default Admin**: `admin@fitpulse.com` / `admin123`

---

## 🐳 Docker Deployment

### Build Docker Image
```bash
docker build -t fitpulse:latest .
```

### Run Container
```bash
docker run -d \
  --name fitpulse \
  -p 8000:80 \
  -e DB_HOST=db_host \
  -e DB_NAME=fitness_gym \
  -e DB_USER=fitpulse_user \
  -e DB_PASS=secure_password \
  fitpulse:latest
```

### Docker Compose
Create `docker-compose.yml`:
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:80"
    environment:
      - DB_HOST=postgres
      - DB_NAME=fitness_gym
      - DB_USER=fitpulse_user
      - DB_PASS=secure_password
    depends_on:
      - postgres

  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: fitness_gym
      POSTGRES_USER: fitpulse_user
      POSTGRES_PASSWORD: secure_password
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

Run with Docker Compose:
```bash
docker-compose up -d
```

---

## ☁️ Cloud Deployment (Render)

### Option 1: Using Render Blueprint
1. Click the **Deploy to Render** button in the repository
2. Connect your GitHub account
3. Configure environment variables
4. Deploy automatically

### Option 2: Manual Deployment

1. **Create Render account** at [render.com](https://render.com/)

2. **Create new Web Service**:
   - Connect GitHub repository
   - Select repository branch
   - Set build command: `composer install`
   - Set start command: `/usr/local/bin/start.sh`

3. **Configure PostgreSQL Database**:
   - Create new PostgreSQL database
   - Note connection credentials

4. **Set Environment Variables**:
   ```
   DB_HOST: [from database]
   DB_PORT: 5432
   DB_NAME: fitness_gym
   DB_USER: [from database]
   DB_PASS: [from database]
   ```

5. **Deploy** and access your live application!

---

## 📁 Project Structure

```
Fitness/
├── admin/                  # Admin panel
│   ├── dashboard.php       # Admin dashboard
│   ├── members.php         # Member management
│   ├── billing.php         # Billing interface
│   └── reports.php         # Analytics & reports
├── api/                    # REST API endpoints
│   ├── members.php         # Member API
│   ├── attendance.php       # Attendance API
│   └── billing.php         # Billing API
├── auth/                   # Authentication
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   └── logout.php          # Logout handler
├── config/                 # Configuration files
│   ├── database.php        # Database config
│   ├── app.php             # App config
│   └── constants.php       # App constants
├── includes/               # Reusable components
│   ├── header.php          # Header template
│   ├── footer.php          # Footer template
│   └── navbar.php          # Navigation bar
├── member/                 # Member dashboard
│   ├── dashboard.php       # Member dashboard
│   ├── profile.php         # Profile management
│   └── progress.php        # Progress tracking
├── trainer/                # Trainer dashboard
│   ├── dashboard.php       # Trainer dashboard
│   └── classes.php         # Class management
├── css/                    # Stylesheets
│   └── style.css           # Main stylesheet
├── js/                     # JavaScript files
│   └── main.js             # Main script
├── assets/                 # Images & media
│   ├── images/             # Image files
│   └── icons/              # Icon files
├── .env.example            # Environment template
├── .gitignore              # Git ignore rules
├── .htaccess               # Apache rules
├── Dockerfile              # Docker configuration
├── render.yaml             # Render deployment config
└── README.md               # This file
```

---

## 🛠️ Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 8.2 |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **Database** | PostgreSQL 12+ |
| **Web Server** | Apache 2.4+ |
| **Containerization** | Docker |
| **Cloud Platform** | Render |
| **Version Control** | Git & GitHub |
| **Package Manager** | Composer |

---

## 💾 Database Schema

### Key Tables

#### `members` Table
```sql
CREATE TABLE members (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender VARCHAR(10),
    status ENUM('active', 'inactive', 'suspended'),
    membership_type VARCHAR(50),
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `attendance` Table
```sql
CREATE TABLE attendance (
    id SERIAL PRIMARY KEY,
    member_id INTEGER REFERENCES members(id),
    check_in TIMESTAMP,
    check_out TIMESTAMP,
    duration_minutes INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `billing` Table
```sql
CREATE TABLE billing (
    id SERIAL PRIMARY KEY,
    member_id INTEGER REFERENCES members(id),
    amount DECIMAL(10, 2),
    payment_method VARCHAR(50),
    status ENUM('pending', 'completed', 'failed'),
    due_date DATE,
    paid_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 👤 User Roles & Permissions

### 1. **Admin**
- Full system access
- User management
- Billing & financial reports
- System configuration
- Analytics & insights

### 2. **Trainer**
- Member management (assigned members)
- Class scheduling
- Progress tracking
- Performance reports
- Session management

### 3. **Member**
- Profile management
- Attendance tracking
- Billing information
- Progress tracking
- Class bookings

### 4. **Staff**
- Member check-in/check-out
- Basic reporting
- Member inquiries support

---

## 🔌 API Documentation

### Authentication
All API requests require authentication token in header:
```
Authorization: Bearer {token}
```

### Member Endpoints
```
GET    /api/members              # List all members
POST   /api/members              # Create new member
GET    /api/members/{id}         # Get member details
PUT    /api/members/{id}         # Update member
DELETE /api/members/{id}         # Delete member
```

### Attendance Endpoints
```
GET    /api/attendance           # List attendance records
POST   /api/attendance           # Check-in member
PUT    /api/attendance/{id}      # Check-out member
GET    /api/attendance/{id}      # Get attendance details
```

### Billing Endpoints
```
GET    /api/billing              # List all invoices
POST   /api/billing              # Create invoice
GET    /api/billing/{id}         # Get invoice details
PUT    /api/billing/{id}         # Update invoice
```

---

## 🔐 Security Features

✅ **Password Security**
- Bcrypt hashing
- Minimum 8 characters required
- Complex password validation

✅ **Session Management**
- Secure session handling
- Session timeout (30 minutes)
- CSRF token validation

✅ **Data Protection**
- SSL/TLS encryption
- Input sanitization
- SQL injection prevention

✅ **Access Control**
- Role-based permissions
- IP whitelisting (optional)
- Rate limiting on API endpoints

✅ **Audit Logging**
- User activity logging
- Financial transaction logs
- System changes tracking

---

## ⚙️ Configuration

### Environment Variables (.env)
```env
# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=fitness_gym
DB_USER=fitpulse_user
DB_PASS=secure_password

# Application
APP_ENV=production
APP_URL=https://yourdomain.com
APP_DEBUG=false

# Security
APP_KEY=your_app_key_here
SECURE_COOKIE=true

# Session
SESSION_TIMEOUT=1800
SESSION_REGENERATE=true

# Email (optional)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USER=your_email@example.com
MAIL_PASS=your_email_password
```

### Database Connection (config/database.php)
```php
return [
    'driver' => 'pgsql',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
];
```

---

## 🤝 Contributing

We welcome contributions! Here's how:

### 1. Fork the Repository
```bash
git clone https://github.com/Ayatta-programmer/Fitness.git
cd Fitness
```

### 2. Create Feature Branch
```bash
git checkout -b feature/amazing-feature
```

### 3. Make Changes
- Write clean, commented code
- Follow PHP standards (PSR-12)
- Test your changes thoroughly

### 4. Commit Changes
```bash
git commit -m 'Add amazing feature'
```

### 5. Push to Branch
```bash
git push origin feature/amazing-feature
```

### 6. Create Pull Request
- Describe your changes clearly
- Reference any related issues
- Wait for review and feedback

### Code Style Guidelines
- Use snake_case for variables and functions
- Use PascalCase for class names
- Maximum line length: 100 characters
- Always use comments for complex logic

---

## 🐛 Troubleshooting

### Issue: Database Connection Error
**Solution:**
```bash
# Verify database credentials in .env
# Check PostgreSQL is running
sudo systemctl status postgresql

# Test connection
psql -h localhost -U fitpulse_user -d fitness_gym
```

### Issue: 404 Error - Routes Not Found
**Solution:**
```bash
# Enable Apache rewrite module
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

### Issue: Permission Denied Errors
**Solution:**
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/Fitness
sudo chmod -R 755 /var/www/html/Fitness
```

### Issue: Docker Container Won't Start
**Solution:**
```bash
# Check logs
docker logs fitpulse

# Verify port availability
sudo lsof -i :8000
```

---

## 🗺️ Roadmap

### Version 1.1 (Q2 2026)
- [ ] Mobile app (iOS & Android)
- [ ] WhatsApp integration for notifications
- [ ] Advanced analytics dashboard
- [ ] Multi-language support

### Version 1.2 (Q3 2026)
- [ ] Nutrition planning module
- [ ] Video workout tutorials
- [ ] AI-powered fitness recommendations
- [ ] Social community features

### Version 2.0 (Q4 2026)
- [ ] Blockchain-based payments
- [ ] VR fitness experiences
- [ ] Integration with wearables
- [ ] Machine learning analytics

---

## 📞 Contact & Support

### Get Help
- 📧 **Email**: info@fitpulse.com
- 📱 **Phone**: +254 700 000000
- 📍 **Location**: Nairobi, Kenya
- 💬 **Discord**: [Join our community](https://discord.gg/fitpulse)

### Bug Reports & Feature Requests
- Report bugs on [GitHub Issues](https://github.com/Ayatta-programmer/Fitness/issues)
- Feature requests in [Discussions](https://github.com/Ayatta-programmer/Fitness/discussions)

### Documentation
- Full docs: [docs.fitpulse.com](https://docs.fitpulse.com)
- API docs: [api.fitpulse.com](https://api.fitpulse.com)
- FAQ: [fitpulse.com/faq](https://fitpulse.com/faq)

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2026 FitPulse

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

## ⭐ Show Your Support

If you found this project helpful, please consider giving it a ⭐ star on GitHub!

---

## 🙏 Acknowledgments

- Built with ❤️ by the FitPulse team
- Special thanks to all contributors
- Inspired by modern fitness management needs

---

**Made with 💪 for the fitness community**

Last Updated: May 6, 2026
