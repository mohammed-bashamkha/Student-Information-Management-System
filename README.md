# 🎓 SIMS - Student Information Management System

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

**SIMS (Student Information Management System)** is a comprehensive and advanced School Resource Planning system built with the **Laravel** framework (Backend API). It is designed to manage all aspects of student affairs, grades, final results, transfers, and admissions, in addition to organizing academic years and managing users with precise role-based permissions.

---

## ✨ Key Features

- **🔐 Advanced Role-Based Access Control (RBAC):** Precise management of users, roles, and permissions to protect API endpoints using the `spatie/laravel-permission` package.
- **🏫 Academic Management:** Full control over Academic Years, Schools, Levels, Classes, and Subjects.
- **🧑‍🎓 Student Affairs Management:** Register new students, manage temporary admissions, handle suspended students, and manage inter-school transfer and admission requests, as well as issuing replacement certificates.
- **📊 Grades and Results Management:** An integrated platform for recording and managing grades across various subjects, with high-efficiency Excel import and export capabilities for student data and final results (using `maatwebsite/excel`).
- **📄 PDF Document Generation:** Generate and print official documents such as replacement certificates, transfer forms, admission forms, and final results using `spatie/laravel-pdf` powered by `Puppeteer`.
- **📝 Activity Logging (Audit Trail):** Automatically track and log all sensitive actions performed by users (such as creation, modification, or deletion) to ensure transparency and accountability using `spatie/laravel-activitylog`.
- **🗄️ Automated Backups:** Built-in database backup system to prevent data loss (using `spatie/laravel-backup`).
- **📚 Comprehensive API Documentation:** Interactive and well-documented RESTful API endpoints ready to be consumed by frontend developers, generated via `knuckleswtf/scribe`.

---

## 🛠️ Tech Stack

- **Backend Framework:** PHP 8.4, Laravel 12.x
- **Authentication:** Laravel Sanctum (Token-based API Authentication)
- **Database:** MySQL
- **Roles & Permissions:** Spatie Laravel Permission
- **Excel Integration:** Maatwebsite Excel
- **PDF Generation:** Spatie Laravel PDF (Requires Node.js & Puppeteer)
- **Auditing/Logging:** Spatie Laravel Activitylog
- **API Documentation:** Knuckles Scribe

---

## 🚀 Installation & Local Setup

Follow these steps to successfully set up and run the project on your local environment:

### 1. Clone the repository
```bash
git clone <repository-url>
cd SIMS
```

### 2. Install PHP Dependencies (Composer)
```bash
composer install
```

### 3. Install Node.js Dependencies (NPM)
This step is required to set up the `Puppeteer` environment responsible for generating PDF files.
```bash
npm install
```

### 4. Environment Setup
Copy the `.env.example` file to create a new `.env` file.
```bash
cp .env.example .env
```
Open the `.env` file and update your database connection credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sims
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Run Migrations & Seeders
Build the database schema and populate it with initial data, roles, and permissions.
```bash
php artisan migrate --seed
```

### 7. Link Storage Directory
Create a symbolic link to allow public access to uploaded files (like generated documents and exported files).
```bash
php artisan storage:link
```

### 8. Run the Local Development Server
Now you can start the backend development server:
```bash
php artisan serve
```
The system will be accessible at: `http://localhost:8000`

---

## 📖 API Documentation

This system is built as a RESTful API tailored for integration with a frontend application (e.g., React.js, Vue.js).
You can generate or update the API documentation directly from the source code comments using `Scribe`:

```bash
php artisan scribe:generate
```
After generation, you can browse the documentation interactively at:
`http://localhost:8000/docs`

---

## 🗂️ Main API Routes Overview

All core system routes are located inside `routes/api.php` and are protected by `Sanctum` authentication (`auth:sanctum`). Some of the key endpoints include:

- **🔐 Authentication & Users:** Login, user creation, and user management (`/api/users`).
- **🛡️ Permissions:** Manage roles and permissions (`/api/roles`, `/api/permissions`).
- **📅 Academic Settings:** Setup and manage Academic Years, Levels, Schools, Classes, and Subjects.
- **🧑‍🎓 Student Affairs:** Register students (`/api/students`), manage suspended students (`/api/suspended-students`).
- **📑 Transfers & Admissions:** Issue replacement certificates, transfer students between schools (`/api/transfers-admissions`).
- **📊 Grades:** Add grades, and import final results from Excel files (`/api/import/final-result`).
- **📄 PDF Export:** Generate and print official documents as PDF streams (`/api/pdf/...`).

---

## 🔒 System Auditing

To ensure the highest administrative security standards, the system is configured to automatically record all significant user activities (Create, Update, Delete) on sensitive records.
System administrators can review these audit logs either through the system dashboard or by querying the `activity_log` table directly in the database.

---

## 📄 License

This system follows the standards of the [Laravel](https://laravel.com/) ecosystem and is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
