# PROJECT PLAN & SPECIFICATION: CLINIC CONNECT MVP (SMART CLINIC MANAGEMENT & QUEUE ECOSYSTEM)

**Product Name:** Clinic Connect  
**Architecture:** Laravel 12 (PHP 8.4) + Inertia.js + Vue 3 + Tailwind CSS + SQLite/MySQL + PWA  
**Target Audience:** Outpatient Clinics, Polyclinics, Pediatric & Family Medical Centers, Solo/Group Physician Practices  
**Objective:** Deliver a production-grade, interactive web ecosystem that streamlines patient self-service booking, dynamic doctor scheduling, multi-secretary front-desk triage, clinical consultation workflows, and family health management.

---

## 1. Executive Summary & Value Proposition

Traditional outpatient clinics suffer from unpredictable waiting times, congested lobby areas, manual paper logbooks, and poor coordination between front-desk secretaries and attending physicians. 

**Clinic Connect** solves these operational bottlenecks by providing a unified, zero-friction digital healthcare platform:
* **For Patients & Families:** Frictionless mobile booking, real-time queue tokens, dynamic doctor availability, dependent/child profile management, and pediatric vaccination tracking.
* **For Secretaries & Receptionists:** Real-time queue board, instant walk-in registration, multi-doctor coverage allocation, and 1-click patient status progression.
* **For Doctors & Specialists:** Interactive practice timeline plotting, live clinic presence toggling (In Clinic / Delayed / Out of Clinic), streamlined consultation notes with prescription capture, and automated electronic medical record (EMR) archiving.
* **For Clinic Owners & Administrators:** Full homepage customizer (branding, logo, favicon, story templates, Google Maps directions embed, operating hours) and on-demand demo data management.

---

## 2. Core Feature Scope & Developed MVP Modules

```
                                  CLINIC CONNECT ECOSYSTEM
 ┌────────────────────────────────────────────────────────────────────────────────────────┐
 │                                                                                        │
 │  ┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────────────────┐  │
 │  │   PUBLIC HOMEPAGE    │  │ PATIENT SELF-SERVICE │  │   PATIENT & FAMILY PORTAL    │  │
 │  │   & BRANDING CMS     │  │   ONLINE BOOKING     │  │   (DEPENDENTS & VACCINES)    │  │
 │  └──────────┬───────────┘  └──────────┬───────────┘  └──────────────┬───────────────┘  │
 │             │                         │                             │                  │
 │             ▼                         ▼                             ▼                  │
 │  ┌──────────────────────────────────────────────────────────────────────────────────┐  │
 │  │                         CENTRAL QUEUE & EMR ENGINE                               │  │
 │  │         (Sequential Doctor Queue Tokens, Status Flow, Appointment Sync)          │  │
 │  └──────────────────────────┬───────────────────────────────┬───────────────────────┘  │
 │                             │                               │                          │
 │                             ▼                               ▼                          │
 │  ┌─────────────────────────────────────┐  ┌─────────────────────────────────────────┐  │
 │  │    SECRETARY QUEUE DASHBOARD        │  │       DOCTOR CONSULTATION WORKSPACE     │  │
 │  │  (Multi-Doctor Coverage Allocation) │  │    (Timeline Plotter, Notes & Status)   │  │
 │  └─────────────────────────────────────┘  └─────────────────────────────────────────┘  │
 │                                                                                        │
 └────────────────────────────────────────────────────────────────────────────────────────┘
```

---

### Module A: Public Clinic Homepage & Homepage Customizer (CMS)
* **High-Impact Visual Aesthetic:** Designed with glassmorphism, responsive cards, curated typography, and authentic medical photography for departments (Cardiology, Pediatrics, Family Care, Diagnostics) and physicians.
* **Specialist & Service Showcase:** Lists active clinic services with duration, pricing, and attending doctors.
* **Interactive Location & Embed:** Integrated Google Maps navigation link and live interactive embed map.
* **Admin / Doctor Clinic Customizer:**
  * **Branding & Logo/Favicon:** Upload custom logos or pick curated SVG medical presets with dynamic browser favicon updating.
  * **Clinic Story & Mission:** Built-in copy templates for Family Clinics, Multi-Specialist Centers, and Urgent Care.
  * **Contact & Emergency Hotline:** Manage public phone lines, emergency contact numbers, email, and operating days.

---

### Module B: Patient Self-Service Booking & Dynamic Slot Engine
* **Universal Browser Accessibility:** Seamless responsive web form requiring zero app downloads or account pre-registration.
* **Physician Live Status Badging:** Displays real-time physician status (*In Clinic*, *Delayed*, *Out of Clinic*) before booking.
* **Dynamic Availability & Slot Computation:**
  * Synchronized with each doctor's weekly plotted schedule.
  * Dynamically computes 15-to-30 minute appointment windows (Morning & Afternoon).
  * Automatically detects days off and provides friendly notices with doctor's active clinic schedule.
* **Family Profile Auto-Fill:** Allows logged-in patients to book for themselves or select registered children/dependents with one click.
* **Instant Queue Token Generation:** Issues sequential daily queue tickets (e.g., `Q-101` for Dr. Santos, `Q-201` for Dr. dela Cruz) with printable/savable confirmation cards.

---

### Module C: Secretary / Receptionist Queue Dashboard
* **Real-Time Multi-Stage Queue Board:**
  * **Waiting in Lobby:** Checked-in patients ready for consultation.
  * **Active Consultation:** Currently inside the doctor's clinic.
  * **Upcoming Booked:** Scheduled arrivals for today not yet checked in.
  * **Completed / Handled Today:** Archive of patients served today.
* **Instant Walk-In Ingestion:** Dedicated modal for front-desk secretaries to register walk-ins and phone bookings in under 10 seconds.
* **Multi-Secretary & Doctor Queue Allocation:**
  * Support for clinics with multiple receptionists and multiple doctors.
  * Configurable assignment matrix: Secretaries can be dedicated to 1 doctor, assigned to specific doctor groups, or operate as clinic-wide floaters.
  * Dynamic filtering: Secretary workspace automatically scopes queue tables and stats to assigned physicians.
* **1-Click Status Progression:** Rapid action buttons to check in patients, call them to lobby, or mark status transitions.

---

### Module D: Doctor Consultation Workspace & Availability Timeline
* **Single-Screen Clinical View:** Distraction-free consultation card featuring the active patient, chief complaint, age, contact details, and assigned queue number.
* **Interactive Weekly Availability & Timeline Plotter:**
  * **7-Day Day-by-Day Editor:** Toggle active consultation days and days off.
  * **Visual Timeline Spectrum (7:00 AM – 8:00 PM):** Graphical representation of working hours with break indicators.
  * **Multi-Slot Windows:** Configure morning, afternoon, and evening clinic blocks with custom start/end times.
  * **1-Click Quick Presets:** Full Day (8–12 & 1–5), Morning Clinic (8–12), Afternoon (1–5), Evening (5–8), Day Off, and "Copy Monday to Weekdays".
* **Live Status Toggle:** Switch status between *In Clinic* (live ping), *Delayed*, and *Out of Clinic*.
* **Consultation Notes & EMR Auto-Archiving:** Capture clinical notes and prescriptions with automatic creation of permanent `MedicalRecord` entries upon checkout.
* **Queue Ingestion:** "Call Next Patient" button instantly fetches the next waiting patient from the lobby.

---

### Module E: Patient & Family Health Portal
* **Family Dependents Management:** Manage children, spouses, and elderly parents with age, gender, blood type (including "Unknown" support), and recorded allergies.
* **Child Vaccination Immunization Tracker:**
  * Tracks childhood vaccines (MMR Booster, DTaP, Varicella, Hepatitis B, etc.).
  * Automatic due-date calculations and status tracking (*Pending* vs. *Completed*).
* **Consultation History:** Chronological archive of past visits, diagnoses, treatments, and prescriptions.

---

### Module F: Admin Demo Suite & Prototype Switcher
* **Universal Role Switcher Bar:** Instant 1-click switching between *Patient Booking*, *Secretary Queue*, *Doctor Workspace*, and *Family Portal*.
* **On-Demand Demo Data Lifecycle:**
  * **Clean Slate Reset (`🗑️ Reset Demo Data`):** Instantly purges test appointments, queue tickets, medical notes, and dependents while preserving clinic branding, services, and doctor schedules.
  * **Realistic Scenario Generator (`🔄 Generate Demo Data`):** Populates authentic Filipino demo patient records (`Juan dela Cruz`, `Mateo dela Cruz`, `Dr. Maria Cristina Santos`, etc.) across all queue stages.

---

## 3. Database Schema Overview

| Table | Purpose | Key Fields |
| :--- | :--- | :--- |
| `clinics` | Clinic profile & CMS settings | `name`, `tagline`, `about`, `logo_url`, `address`, `google_map_embed_url`, `phone`, `emergency_phone`, `open_time`, `close_time`, `operating_days` |
| `users` | User accounts & roles | `name`, `email`, `phone`, `specialization`, `live_status`, `clinic_id` (Roles: `Admin`, `Doctor`, `Secretary`, `Patient`) |
| `availabilities` | Doctor weekly practice schedule | `doctor_id`, `day_of_week` (0–6), `start_time`, `end_time`, `is_available` |
| `doctor_secretary` | Staff coverage assignments | `secretary_id`, `doctor_id` |
| `services` | Clinic consultation types | `clinic_id`, `name`, `description`, `duration_minutes`, `price`, `is_active` |
| `appointments` | Bookings & daily queue | `clinic_id`, `doctor_id`, `service_id`, `patient_id`, `queue_number`, `appointment_date`, `time_slot`, `patient_name`, `status` (`booked`, `waiting_in_lobby`, `in_consultation`, `completed`, `cancelled`) |
| `dependents` | Family members & children | `user_id`, `name`, `relationship`, `age`, `gender`, `blood_type`, `allergies` |
| `vaccine_reminders` | Child immunization schedule | `patient_id`, `patient_name`, `vaccine_name`, `due_date`, `status` (`pending`, `completed`) |
| `medical_records` | Archival consultation notes | `appointment_id`, `doctor_id`, `patient_id`, `patient_name`, `diagnosis`, `treatment`, `prescription`, `visit_date` |

---

## 4. 3-Day Development & Implementation Milestone Map

### Day 1: Architecture, Responsive Booking & Homepage CMS
* Setup Laravel 12 + Inertia.js (Vue 3) + Tailwind CSS stack.
* Implemented multi-role RBAC schema (Spatie Permissions).
* Created public clinic homepage with responsive departments and Google Maps integration.
* Built patient self-service booking flow with sequential queue token generation.
* Developed Clinic Settings / Homepage Customizer (Logo, favicon, story templates, contact details).

### Day 2: Secretary Operations & Clinical Workspace
* Created Secretary Queue Board with real-time stage progression.
* Implemented rapid Walk-In Ingestion modal.
* Built multi-secretary doctor queue coverage assignment engine.
* Developed Doctor Consultation Workspace with active patient card, live status toggle, and checkout EMR archiving.
* Built Patient & Family Health Portal with dependent profiles and pediatric vaccine tracker.

### Day 3: Availability Timeline Plotter, Demo Suite & Quality Assurance
* Developed interactive Doctor Availability & Practice Timeline Plotter with multi-window time editors and quick presets.
* Integrated dynamic doctor slot calculation into the public booking engine.
* Created Admin Demo Suite (Clean Slate reset and Filipino scenario generator).
* Established full feature test suite (40/40 tests passing with 100% core flow coverage).
* Formatted codebase to PSR-12 / Laravel standards using Laravel Pint.

---

## 5. Live Demonstration Workflow (Pitch Script)

1. **Step 1: Patient Online Booking (`/book`)**
   - Select *Dr. Maria Cristina Santos (Pediatrics)* and pick a date.
   - Observe how time slots dynamically match Dr. Santos' plotted availability.
   - Book for a child (*Mateo dela Cruz*) for *Pediatric Immunization* and receive Queue Token `Q-101`.

2. **Step 2: Secretary Queue Management (`/secretary/dashboard`)**
   - Switch to Secretary role; see `Q-101` in the *Upcoming Booked* list.
   - Click **Check In** when the patient arrives at the clinic &rarr; status moves to *Waiting in Lobby*.
   - Use **+ Add Walk-In** to register a walk-in patient (`Q-102`) in seconds.

3. **Step 3: Doctor Consultation Workspace (`/doctor/dashboard`)**
   - Switch to Doctor role; toggle status to *In Clinic*.
   - Click **Call Next Patient** &rarr; `Q-101 (Mateo dela Cruz)` moves into *Active Consultation*.
   - Review patient complaint, enter consultation notes and prescription, and click **Mark Consultation as Completed**.
   - Open **📅 My Schedule & Timeline** to inspect/adjust weekly hours and visual timeline blocks.

4. **Step 4: Patient & Family Health Portal (`/patient/dashboard`)**
   - Switch to Patient role; view completed consultation note and prescription history.
   - Inspect child dependents (*Mateo* & *Althea*) and check upcoming MMR vaccine booster reminders.

5. **Step 5: Admin Demo Reset (`Clinic Settings` or Top Demo Bar)**
   - Click **🗑️ Reset Demo Data** for a clean slate, or **🔄 Generate Demo Data** to reload scenarios for the next client demo.
