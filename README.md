# FitPulse

FitPulse is a gym management web application built with PHP, PostgreSQL, HTML, CSS, and JavaScript. It is designed to help a fitness center manage users, trainers, members, attendance, workout progress, invoices, reports, and M-Pesa payments.

The project includes a public website for visitors and separate dashboards for admins, trainers, and members.

## What The System Does

FitPulse provides a simple way to run common gym operations from one web system:

- Members can register, log in, view plans, track attendance, view billing records, and monitor calories.
- Trainers can manage member attendance and fitness progress.
- Admins can manage members, billing, reports, attendance, and calorie records.
- The system supports M-Pesa STK Push payments through the Safaricom Daraja API.
- The project can run locally, inside Docker, or on Render.

## Main Features

- Public landing page
- About, features, and contact pages
- User registration
- Secure login and logout
- Forgot password flow using security questions
- Role-based dashboard access
- Admin dashboard
- Trainer dashboard
- Member dashboard
- Member management
- Attendance tracking
- Calorie and workout tracking
- Billing and invoice management
- Reports
- M-Pesa payment integration
- PostgreSQL database support
- Docker deployment support
- Render deployment support

## User Roles

### Admin

The admin controls the main management side of the system.

Admin can access:

- Dashboard
- Members
- Attendance
- Calories
- Billing
- Reports

### Trainer

The trainer focuses on member fitness activity and progress.

Trainer can access:

- Dashboard
- Members
- Attendance
- Calories

### Member

The member uses the system to track personal gym activity and payments.

Member can access:

- Dashboard
- Plans
- Attendance
- Calories
- Billing
- Reports

## Technologies Used

| Part | Technology |
| --- | --- |
| Backend | PHP 8.2 |
| Database | PostgreSQL |
| Frontend | HTML, CSS, JavaScript |
| Server | Apache |
| Payments | M-Pesa Daraja API |
| Deployment | Docker, Render |

## Folder Structure

```text
Fitness-main/
+-- admin/
+-- api/
+-- assets/
+-- auth/
+-- config/
+-- css/
+-- includes/
+-- js/
+-- member/
+-- trainer/
+-- about.html
+-- contact.html
+-- features.html
+-- index.html
+-- Dockerfile
+-- render.yaml
```

### Important Folders

- `admin/` contains admin dashboard pages.
- `trainer/` contains trainer dashboard pages.
- `member/` contains member dashboard pages.
- `auth/` contains login, registration, logout, and password reset files.
- `config/` contains database connection and SQL setup files.
- `api/` contains M-Pesa payment endpoints.
- `includes/` contains shared sidebar files.
- `css/` contains the main stylesheet.
- `js/` contains the main JavaScript file.
- `assets/images/` contains images used by the website.

## Requirements

To run the project locally, you need:

- PHP 8.2 or newer
- PostgreSQL
- Apache or PHP built-in server
- PHP PostgreSQL extensions
- PHP cURL extension

Required PHP extensions:

```text
pdo
pdo_pgsql
pgsql
curl
```

## Local Installation

1. Open the project folder.

```bash
cd Fitness-main
```

2. Create a PostgreSQL database.

```sql
CREATE DATABASE fitness_gym;
```

3. Import the database schema.

```bash
psql -U your_database_user -d your_database_name -f config/fitpulse_gym_pg.sql
```

4. Check database settings in `config/database.php`.

Default settings:

```text
DB_HOST=your_database_host
DB_PORT=your_database_port
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
```

5. Start the PHP development server.

```bash
php -S localhost:8000
```

6. Open the project in your browser.

```text
http://localhost:8000
```

## Database Setup Page

The project also includes a setup page:

```text
config/setup.php
```

After configuring the database, you can visit:

```text
http://localhost:8000/config/setup.php
```

This page creates the required tables and inserts the default admin account.

## Admin Access

For security reasons, admin login credentials and admin invite codes are not included in this README. They should only be shared privately with authorized users or examiners when required.

Do not commit or publish real admin passwords, invite codes, or recovery answers.

## Environment Configuration

The project reads configuration values from environment variables when available.

Common variables:

```text
DATABASE_URL=postgresql://your_database_user:your_database_password@your_database_host:your_database_port/your_database_name
DB_HOST=your_database_host
DB_PORT=your_database_port
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
APP_ENV=development
APP_URL=http://localhost:8000
```

If `DATABASE_URL` exists, the project uses it for the PostgreSQL connection. This is useful when deploying to Render.

## M-Pesa Configuration

M-Pesa payments are handled through the files in the `api/` folder.

Required M-Pesa environment variables:

```text
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://your-domain.com/api/mpesa_callback.php
```

Use `sandbox` while testing. Use `production` only when you have live Safaricom Daraja credentials.

M-Pesa callbacks require a public HTTPS URL.

## Running With Docker

Build the image:

```bash
docker build -t fitpulse .
```

Run the container:

```bash
docker run -p 8000:80 \
  -e DB_HOST=your_database_host \
  -e DB_PORT=your_database_port \
  -e DB_NAME=your_database_name \
  -e DB_USER=your_database_user \
  -e DB_PASS=your_database_password \
  -e APP_URL=http://localhost:8000 \
  fitpulse
```

Open:

```text
http://localhost:8000
```

## Deployment On Render

The project includes a `render.yaml` file for Render deployment.

It defines:

- A Docker web service
- A PostgreSQL database
- Database environment variables
- Production environment settings

Deployment steps:

1. Push the project to GitHub.
2. Create a new Blueprint on Render.
3. Select the repository.
4. Deploy the service.
5. Set `APP_URL` to your deployed Render URL.
6. Add M-Pesa environment variables if payments are needed.

## Main Database Tables

The PostgreSQL schema creates these tables:

- `users`
- `attendance`
- `calories`
- `invoices`
- `reports`
- `mpesa_transactions`

The main schema file is:

```text
config/fitpulse_gym_pg.sql
```

## Security Recommendations

- Change the default admin password immediately.
- Do not publish real database passwords.
- Do not publish real M-Pesa credentials.
- Use HTTPS in production.
- Set `APP_ENV=production` on a live server.
- Disable or restrict access to setup files after deployment.
- Keep database backups.

## Author

Developed by Ayata Horace.
