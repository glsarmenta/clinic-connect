# Clinic Connect Specification

## 1. Overview
Clinic Connect is a Progressive Web Application (PWA) designed to manage clinic operations, patient records, and doctor availabilities.

## 2. User Roles
The application supports three distinct user roles:

### 2.1 Doctor / Owner
- Manage availability calendars.
- Access patient medical records.
- Configure clinic information.
- Automate vaccine reminders.
- **Customize the home page information (Clinic hours, Address, and Google Map information).**

### 2.2 Admin or Secretary
- Manage appointment scheduling.
- Handle front-desk check-ins and check-outs.
- Update doctors on queue status in real-time.

### 2.3 Patient
- View digital health records.
- Receive automated appointment and vaccine reminders.
- Utilize self-service online booking.

## 3. Tech Stack
- **Backend:** Laravel 11, PHP 8.1+
- **Database:** MySQL
- **Frontend:** Vue.js 3, Inertia.js, TailwindCSS
- **Infrastructure:** Docker (Laravel Sail)
- **Real-time:** Laravel Reverb (WebSockets)
- **Authentication:** Laravel Breeze
- **Authorization:** Spatie Laravel Permission
- **PWA:** Vite PWA Plugin
