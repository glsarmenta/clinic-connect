import os
import sys
import base64
import subprocess

def get_base64_image(file_path):
    if not os.path.exists(file_path):
        return ""
    ext = os.path.splitext(file_path)[1].lower().replace('.', '')
    if ext == 'jpg':
        ext = 'jpeg'
    with open(file_path, "rb") as image_file:
        encoded = base64.b64encode(image_file.read()).decode('utf-8')
    return f"data:image/{ext};base64,{encoded}"

print("Reading snapshot images...")
img_homepage = get_base64_image("public/snapshots/01_clinic_homepage.png")
img_booking = get_base64_image("public/snapshots/02_patient_booking.png")
img_secretary = get_base64_image("public/snapshots/03_secretary_queue_dashboard.png")
img_doctor = get_base64_image("public/snapshots/04_doctor_workspace.png")
img_family = get_base64_image("public/snapshots/05_patient_family_portal.png")

html_content = f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Clinic Connect - Features & System Documentation</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

  @page {{
    size: A4 portrait;
    margin: 12mm 12mm 14mm 12mm;
    @bottom-center {{
      content: "Clinic Connect Documentation | Page " counter(page);
      font-size: 8pt;
      color: #94a3b8;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }}
  }}

  * {{
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }}

  body {{
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1e293b;
    background: #ffffff;
    font-size: 9.5pt;
    line-height: 1.55;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }}

  .page {{
    page-break-after: always;
    break-after: page;
    position: relative;
    padding-bottom: 5mm;
  }}

  .page-last {{
    page-break-after: auto;
    break-after: auto;
  }}

  .avoid-break {{
    page-break-inside: avoid;
    break-inside: avoid;
  }}

  /* Typography */
  h1, h2, h3, h4 {{
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
  }}

  h1 {{ font-size: 20pt; line-height: 1.2; margin-bottom: 8px; }}
  h2 {{ font-size: 13pt; line-height: 1.3; color: #0284c7; border-bottom: 2px solid #e0f2fe; padding-bottom: 4px; margin-top: 14px; margin-bottom: 8px; }}
  h3 {{ font-size: 10.5pt; line-height: 1.3; color: #0369a1; margin-top: 10px; margin-bottom: 4px; font-weight: 600; }}
  h4 {{ font-size: 9.5pt; color: #334155; margin-top: 6px; margin-bottom: 2px; }}

  p {{
    margin-bottom: 6px;
    color: #334155;
  }}

  ul, ol {{
    margin-left: 18px;
    margin-bottom: 8px;
  }}

  li {{
    margin-bottom: 3px;
    color: #334155;
  }}

  code {{
    font-family: 'JetBrains Mono', monospace;
    font-size: 8pt;
    background: #f1f5f9;
    padding: 1px 4px;
    border-radius: 4px;
    color: #0369a1;
  }}

  pre {{
    font-family: 'JetBrains Mono', monospace;
    font-size: 7.5pt;
    background: #0f172a;
    color: #e2e8f0;
    padding: 8px 10px;
    border-radius: 6px;
    overflow-x: hidden;
    line-height: 1.4;
    margin: 6px 0;
  }}

  /* Cover Page Styling */
  .cover-container {{
    min-height: 92vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 30px 20px 20px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #f0fdfa 50%, #e0f2fe 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
  }}

  .cover-header {{
    display: flex;
    align-items: center;
    gap: 12px;
  }}

  .logo-badge {{
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #0284c7, #0d9488);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 20px;
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
  }}

  .brand-text {{
    font-size: 16pt;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.03em;
  }}

  .brand-tag {{
    font-size: 8.5pt;
    color: #0284c7;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }}

  .cover-body {{
    margin: 40px 0;
  }}

  .doc-category {{
    display: inline-block;
    background: #0284c7;
    color: #ffffff;
    font-size: 8pt;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 12px;
  }}

  .cover-title {{
    font-size: 26pt;
    line-height: 1.15;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 10px;
  }}

  .cover-subtitle {{
    font-size: 11.5pt;
    color: #475569;
    max-width: 580px;
    line-height: 1.5;
    margin-bottom: 24px;
  }}

  .meta-grid {{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 14px 16px;
    max-width: 540px;
  }}

  .meta-item-label {{
    font-size: 7.5pt;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.05em;
  }}

  .meta-item-value {{
    font-size: 9pt;
    font-weight: 700;
    color: #0f172a;
  }}

  .cover-footer {{
    border-top: 1px solid #cbd5e1;
    padding-top: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 8pt;
    color: #64748b;
  }}

  /* Tables */
  table {{
    width: 100%;
    border-collapse: collapse;
    font-size: 8pt;
    margin: 8px 0;
  }}

  th {{
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 700;
    text-align: left;
    padding: 5px 8px;
    border: 1px solid #cbd5e1;
    font-size: 8pt;
  }}

  td {{
    padding: 5px 8px;
    border: 1px solid #e2e8f0;
    color: #334155;
    vertical-align: top;
  }}

  tr:nth-child(even) td {{
    background: #f8fafc;
  }}

  /* Cards & Callouts */
  .card {{
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 8px;
  }}

  .callout {{
    border-left: 3px solid #0284c7;
    background: #f0f9ff;
    padding: 7px 10px;
    border-radius: 0 4px 4px 0;
    margin: 6px 0;
    font-size: 8.5pt;
  }}

  .callout-success {{
    border-left-color: #10b981;
    background: #ecfdf5;
  }}

  .callout-warning {{
    border-left-color: #f59e0b;
    background: #fffbeb;
  }}

  /* Badges */
  .badge {{
    display: inline-block;
    padding: 2px 7px;
    border-radius: 12px;
    font-size: 7pt;
    font-weight: 700;
    text-transform: uppercase;
  }}
  .badge-blue {{ background: #e0f2fe; color: #0369a1; }}
  .badge-green {{ background: #dcfce7; color: #15803d; }}
  .badge-amber {{ background: #fef3c7; color: #b45309; }}
  .badge-purple {{ background: #f3e8ff; color: #7e22ce; }}

  /* Screenshots Container */
  .screenshot-frame {{
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    overflow: hidden;
    margin: 8px 0;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
  }}

  .screenshot-titlebar {{
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    padding: 4px 8px;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 7.5pt;
    font-weight: 600;
    color: #475569;
  }}

  .dot {{
    width: 6px;
    height: 6px;
    border-radius: 50%;
  }}
  .dot-red {{ background: #ef4444; }}
  .dot-yellow {{ background: #f59e0b; }}
  .dot-green {{ background: #10b981; }}

  .screenshot-img {{
    width: 100%;
    display: block;
    max-height: 250px;
    object-fit: cover;
    object-position: top;
  }}

  .img-caption {{
    font-size: 7.5pt;
    color: #64748b;
    text-align: center;
    font-style: italic;
    margin-top: 3px;
  }}

  /* Flow Diagram */
  .flow-box {{
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    margin: 8px 0;
  }}

  .flow-step {{
    text-align: center;
    flex: 1;
  }}

  .flow-step-num {{
    width: 22px;
    height: 22px;
    background: #0284c7;
    color: white;
    font-size: 8pt;
    font-weight: bold;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 3px;
  }}

  .flow-step-title {{
    font-size: 7.5pt;
    font-weight: 700;
    color: #0f172a;
  }}

  .flow-step-desc {{
    font-size: 6.5pt;
    color: #64748b;
    line-height: 1.2;
  }}

  .flow-arrow {{
    color: #94a3b8;
    font-size: 14pt;
    padding: 0 4px;
  }}

  .grid-2 {{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }}

  .grid-3 {{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
  }}

  .stat-card {{
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 10px;
    text-align: center;
  }}

  .stat-number {{
    font-size: 13pt;
    font-weight: 800;
    color: #0284c7;
  }}

  .stat-label {{
    font-size: 7pt;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
  }}
</style>
</head>
<body>

<!-- PAGE 1: COVER PAGE -->
<div class="page">
  <div class="cover-container">
    <div class="cover-header">
      <div class="logo-badge">CC</div>
      <div>
        <div class="brand-text">CLINIC CONNECT</div>
        <div class="brand-tag">Smart Clinic Management & Digital Queue Ecosystem</div>
      </div>
    </div>

    <div class="cover-body">
      <div class="doc-category">Official System Documentation & Product Guide</div>
      <div class="cover-title">Features, Architecture & Operational Manual</div>
      <div class="cover-subtitle">
        A complete guide to the modern, synchronized healthcare operating system designed for outpatient practices, pediatric centers, and specialist clinics.
      </div>

      <div class="meta-grid">
        <div>
          <div class="meta-item-label">Product Name</div>
          <div class="meta-item-value">Clinic Connect MVP</div>
        </div>
        <div>
          <div class="meta-item-label">Release Version</div>
          <div class="meta-item-value">v1.0 (Production Ready)</div>
        </div>
        <div>
          <div class="meta-item-label">Technology Stack</div>
          <div class="meta-item-value">Laravel 12 (PHP 8.4) + Inertia.js (Vue 3)</div>
        </div>
        <div>
          <div class="meta-item-label">Test Suite Verification</div>
          <div class="meta-item-value" style="color: #10b981;">✓ 40 / 40 Tests Passing (100%)</div>
        </div>
        <div>
          <div class="meta-item-label">Primary Stakeholders</div>
          <div class="meta-item-value">Doctors, Secretaries, Patients & Families</div>
        </div>
        <div>
          <div class="meta-item-label">Release Date</div>
          <div class="meta-item-value">October 2026</div>
        </div>
      </div>
    </div>

    <div class="cover-footer">
      <div>Enterprise Clinical Software Architecture</div>
      <div>Confidential & Proprietary &bull; Clinic Connect Ecosystem</div>
    </div>
  </div>
</div>

<!-- PAGE 2: EXECUTIVE SUMMARY & SYSTEM OVERVIEW -->
<div class="page">
  <h2>1. Executive Summary & Value Proposition</h2>
  <p>
    Outpatient medical facilities frequently experience unpredictable patient wait times, crowded physical waiting areas, manual paper-based queue registries, and disjointed coordination between reception staff and attending physicians.
  </p>
  <p>
    <strong>Clinic Connect</strong> provides an end-to-end digital ecosystem that bridges these gaps. It provides instant online booking without mobile app downloads, automated doctor availability slotting, real-time waiting lobby triage, and a clean physician workspace that connects seamlessly into patient family health records.
  </p>

  <div class="grid-3" style="margin: 10px 0;">
    <div class="stat-card">
      <div class="stat-number">0 Apps</div>
      <div class="stat-label">Mobile Browser Self-Service</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">&lt; 10s</div>
      <div class="stat-label">Secretary Walk-in Registration</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">100%</div>
      <div class="stat-label">Automated EMR Archiving</div>
    </div>
  </div>

  <h3>Core Stakeholder Personas & Value Delivery</h3>
  <table>
    <thead>
      <tr>
        <th style="width: 25%;">User Persona</th>
        <th style="width: 35%;">Key Pain Points Addressed</th>
        <th style="width: 40%;">Core System Capabilities</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>🧑‍⚕️ Doctors & Specialists</strong></td>
        <td>Unpredictable patient arrival surges, lack of arrival visibility, tedious paperwork.</td>
        <td>Visual weekly practice timeline (7am–8pm), live presence status toggle, single-click "Call Next Patient", and integrated digital prescriptions.</td>
      </tr>
      <tr>
        <td><strong>📋 Clinic Secretaries</strong></td>
        <td>Overwhelmed reception desk, tracking multiple doctors simultaneously, paper queues.</td>
        <td>4-stage live waiting room board, 10-second rapid walk-in intake, and flexible multi-doctor assignment matrices.</td>
      </tr>
      <tr>
        <td><strong>👨‍👩‍👧 Patients & Families</strong></td>
        <td>Waiting hours in crowded lobbies without knowing doctor status or position in line.</td>
        <td>Instant online slot booking, real-time doctor live status alerts, digital queue tokens, dependent profiles, and pediatric vaccine tracking.</td>
      </tr>
      <tr>
        <td><strong>🏥 Clinic Administrators</strong></td>
        <td>Inflexible clinic branding, lack of marketing control, complex deployment.</td>
        <td>Clinic homepage customizer (CMS), logo/favicon dynamic switcher, Google Maps integration, and live demonstration suite.</td>
      </tr>
    </tbody>
  </table>

  <h2>2. End-to-End Operational Lifecycle</h2>
  <div class="flow-box">
    <div class="flow-step">
      <div class="flow-step-num">1</div>
      <div class="flow-step-title">Self-Service Booking</div>
      <div class="flow-step-desc">Patient selects doctor, sees live status & booked slot &rarr; Receives Q-101 Token</div>
    </div>
    <div class="flow-arrow">&rarr;</div>
    <div class="flow-step">
      <div class="flow-step-num">2</div>
      <div class="flow-step-title">Front-Desk Arrival</div>
      <div class="flow-step-desc">Secretary marks "Check In" or enters 10s walk-in &rarr; Moves to Lobby Queue</div>
    </div>
    <div class="flow-arrow">&rarr;</div>
    <div class="flow-step">
      <div class="flow-step-num">3</div>
      <div class="flow-step-title">Doctor Consultation</div>
      <div class="flow-step-desc">Doctor clicks "Call Next", logs findings, creates prescription & completes visit</div>
    </div>
    <div class="flow-arrow">&rarr;</div>
    <div class="flow-step">
      <div class="flow-step-num">4</div>
      <div class="flow-step-title">Family Health Archive</div>
      <div class="flow-step-desc">Notes & medications auto-saved to patient portal; vaccine schedules updated</div>
    </div>
  </div>

  <div class="callout callout-success">
    <strong>Synchronized Reality:</strong> Any status update made by the secretary or doctor immediately updates the patient's queue progression and electronic records with zero page refreshes, powered by Inertia.js.
  </div>
</div>

<!-- PAGE 3: FEATURE 1 - PUBLIC CLINIC HOMEPAGE & CMS -->
<div class="page">
  <h2>3. Feature 1: Public Clinic Showcase & Homepage CMS</h2>
  <p>
    Clinic Connect serves as the clinic's public-facing digital front door. It presents a modern aesthetic designed with glassmorphism, authentic medical photography, interactive Google Maps directions, and an integrated branding CMS for administrators.
  </p>

  <div class="screenshot-frame">
    <div class="screenshot-titlebar">
      <div class="dot dot-red"></div><div class="dot dot-yellow"></div><div class="dot dot-green"></div>
      <span>Clinic Connect — Public Homepage & Brand Showcase (/)</span>
    </div>
    <img src="{img_homepage}" class="screenshot-img" alt="Homepage Screenshot" />
  </div>
  <div class="img-caption">Figure 1.1: Responsive clinic portal showcasing medical departments, accepted services, doctor profiles, and location.</div>

  <h3>Key Functional Capabilities</h3>
  <ul>
    <li><strong>Department Showcase:</strong> Curated displays for Family Medicine, Pediatrics, Cardiology, and Diagnostic Laboratories with service pricing and consultation durations.</li>
    <li><strong>Interactive Google Maps Embed:</strong> Responsive location map with direct "Get Directions" navigation linking to Google Maps.</li>
    <li><strong>Clinic Branding Customizer (CMS):</strong> Clinic administrators can dynamically edit the clinic name, tagline, logo image URL, browser favicon, and contact numbers without editing code.</li>
    <li><strong>Pre-Crafted Story Templates:</strong> Built-in 1-click marketing copy presets tailored for <em>Family Clinics</em>, <em>Multi-Specialist Centers</em>, and <em>Pediatric Practices</em>.</li>
    <li><strong>Direct-Dial Hotlines:</strong> Prominent emergency hotline and inquiry telephone buttons for mobile users.</li>
  </ul>
</div>

<!-- PAGE 4: FEATURE 2 - PATIENT SELF-SERVICE BOOKING -->
<div class="page">
  <h2>4. Feature 2: Patient Self-Service Booking & Slot Engine</h2>
  <p>
    The self-service booking engine allows patients to reserve an appointment from any smartphone or computer in under 60 seconds with zero software installations.
  </p>

  <div class="screenshot-frame">
    <div class="screenshot-titlebar">
      <div class="dot dot-red"></div><div class="dot dot-yellow"></div><div class="dot dot-green"></div>
      <span>Clinic Connect — Patient Self-Service Booking (/book)</span>
    </div>
    <img src="{img_booking}" class="screenshot-img" alt="Booking Screenshot" />
  </div>
  <div class="img-caption">Figure 2.1: Dynamic slot generation matching physician plotted availability and sequential queue token preview.</div>

  <h3>Key Functional Capabilities</h3>
  <ul>
    <li><strong>Doctor Live Presence Badging:</strong> Real-time status indicator shows if the doctor is currently <span class="badge badge-green">In Clinic</span>, <span class="badge badge-amber">Delayed</span>, or <span class="badge badge-blue">Out of Clinic</span> before the patient books.</li>
    <li><strong>Dynamic Availability Slot Computation:</strong> The calendar dynamically queries the selected doctor's plotted weekly schedule. Available appointment slots (e.g. 8:00 AM, 8:30 AM, 9:00 AM) are computed on the fly. If a physician is off on that day, a helpful schedule advisory is shown.</li>
    <li><strong>Instant Sequential Queue Tokens:</strong> Patients receive clean sequential daily tokens (e.g., <code>Q-101</code> for Doctor 1, <code>Q-201</code> for Doctor 2) that can be downloaded or printed.</li>
    <li><strong>Family Member Auto-Fill:</strong> Authenticated patients can toggle between booking for themselves or selecting from their registered dependents (e.g., children or elderly parents) with 1 click.</li>
  </ul>
</div>

<!-- PAGE 5: FEATURE 3 - SECRETARY QUEUE DASHBOARD -->
<div class="page">
  <h2>5. Feature 3: Secretary & Front-Desk Queue Dashboard</h2>
  <p>
    The front-desk secretary dashboard is designed for high-stress, high-volume clinic reception desks. It organizes patient flow into a 4-stage visual board and provides 10-second walk-in intake.
  </p>

  <div class="screenshot-frame">
    <div class="screenshot-titlebar">
      <div class="dot dot-red"></div><div class="dot dot-yellow"></div><div class="dot dot-green"></div>
      <span>Clinic Connect — Secretary Live Waiting Room Board (/secretary/dashboard)</span>
    </div>
    <img src="{img_secretary}" class="screenshot-img" alt="Secretary Dashboard Screenshot" />
  </div>
  <div class="img-caption">Figure 3.1: 4-stage triage board with doctor filtering pills and rapid walk-in registration.</div>

  <h3>Key Functional Capabilities</h3>
  <ul>
    <li><strong>4-Stage Real-Time Triage Board:</strong>
      <ol style="margin-top: 3px;">
        <li><span class="badge badge-amber">Upcoming Booked:</span> Expected patient arrivals for the day.</li>
        <li><span class="badge badge-blue">Waiting in Lobby:</span> Physically arrived patients checked in at front desk.</li>
        <li><span class="badge badge-purple">In Consultation:</span> Patient currently inside the doctor's exam room.</li>
        <li><span class="badge badge-green">Completed Today:</span> Archived record of finished consultations.</li>
      </ol>
    </li>
    <li><strong>10-Second Rapid Walk-In Ingestion:</strong> Receptionists can register walk-ins or phone inquiries instantly via a modal that auto-assigns the next available queue token.</li>
    <li><strong>Multi-Secretary & Doctor Queue Allocation:</strong> Clinics with multiple doctors and secretaries can assign specific secretaries to individual physicians or allow floaters to oversee all queues.</li>
    <li><strong>1-Click Status Progression:</strong> Receptionists can advance patients from "Booked" to "Waiting in Lobby" with a single click.</li>
  </ul>
</div>

<!-- PAGE 6: FEATURE 4 - DOCTOR WORKSPACE & TIMELINE -->
<div class="page">
  <h2>6. Feature 4: Doctor Workspace & Availability Timeline</h2>
  <p>
    The physician consultation room is a distraction-free environment that prioritizes patient care. Doctors can call patients from the waiting room, record diagnoses, write prescriptions, and visually plan their working hours.
  </p>

  <div class="screenshot-frame">
    <div class="screenshot-titlebar">
      <div class="dot dot-red"></div><div class="dot dot-yellow"></div><div class="dot dot-green"></div>
      <span>Clinic Connect — Doctor Clinical Workspace (/doctor/dashboard)</span>
    </div>
    <img src="{img_doctor}" class="screenshot-img" alt="Doctor Workspace Screenshot" />
  </div>
  <div class="img-caption">Figure 4.1: Active patient consultation card, digital prescription editor, and weekly practice availability plotter.</div>

  <h3>Key Functional Capabilities</h3>
  <ul>
    <li><strong>Active Patient Card:</strong> Displays chief complaint, patient age, queue number, and previous medical history.</li>
    <li><strong>Interactive Practice Timeline Plotter:</strong> An interactive visual bar (7:00 AM – 8:00 PM) allowing doctors to customize clinic blocks, split shifts, and break times for every day of the week.</li>
    <li><strong>1-Click Schedule Presets:</strong> Quickly apply presets such as <em>"Full Day (8–12 & 1–5)"</em>, <em>"Morning Clinic Only"</em>, or <em>"Copy Monday to All Weekdays"</em>.</li>
    <li><strong>Live Status Switcher:</strong> Doctors can toggle their real-time presence with an active glowing radar badge (<span class="badge badge-green">In Clinic</span> / <span class="badge badge-amber">Delayed</span> / <span class="badge badge-blue">Out of Clinic</span>).</li>
    <li><strong>Digital Prescription & EMR Archiving:</strong> Form fields for diagnosis, clinical notes, and medication prescriptions that automatically generate permanent patient health records on completion.</li>
  </ul>
</div>

<!-- PAGE 7: FEATURE 5 & 6 - FAMILY PORTAL & DEMO SUITE -->
<div class="page">
  <h2>7. Feature 5: Patient & Family Health Portal</h2>
  <p>
    The Patient Portal empowers families to take control of their healthcare records, manage dependents, and track pediatric immunizations.
  </p>

  <div class="screenshot-frame">
    <div class="screenshot-titlebar">
      <div class="dot dot-red"></div><div class="dot dot-yellow"></div><div class="dot dot-green"></div>
      <span>Clinic Connect — Patient & Family Health Portal (/patient/dashboard)</span>
    </div>
    <img src="{img_family}" class="screenshot-img" alt="Family Portal Screenshot" />
  </div>
  <div class="img-caption">Figure 5.1: Family dependent management profiles, immunization tracker, and consultation records archive.</div>

  <div class="grid-2" style="margin-top: 6px;">
    <div class="card">
      <h3>👶 Family Dependents Manager</h3>
      <p>Parents can create profiles for their children or elderly family members, tracking blood types, age, relationship, and known drug allergies.</p>
    </div>
    <div class="card">
      <h3>💉 Child Vaccination Tracker</h3>
      <p>Automated reminders for essential childhood vaccines (MMR, DTaP, Varicella, Hepatitis B) with due-date alerts and status badges.</p>
    </div>
  </div>

  <h2>8. Feature 6: Interactive Demo Suite & Scenario Generator</h2>
  <p>
    To ensure seamless live client presentations, Clinic Connect includes a built-in testing and demonstration control suite accessible from the top banner.
  </p>

  <div class="card">
    <div class="grid-2">
      <div>
        <h4>🔄 Realistic Scenario Generator</h4>
        <p>Instantly populates authentic test patient records across all 4 queue stages with realistic local demographics (e.g. <em>Juan dela Cruz, Mateo dela Cruz, Dr. Santos</em>).</p>
      </div>
      <div>
        <h4>🗑️ Clean Slate Demo Reset</h4>
        <p>Clears test appointments, queues, and clinical notes in one click while preserving clinic branding, services, and doctor schedules.</p>
      </div>
    </div>
  </div>
</div>

<!-- PAGE 8: TECHNICAL ARCHITECTURE & DATABASE SCHEMA -->
<div class="page">
  <h2>9. System Architecture & Technical Specifications</h2>
  
  <div class="grid-2" style="margin-bottom: 8px;">
    <div class="card">
      <h3>🖥️ Technology Stack</h3>
      <ul style="margin-bottom: 0;">
        <li><strong>Backend Framework:</strong> Laravel 12 (PHP 8.4)</li>
        <li><strong>Frontend Layer:</strong> Inertia.js (Vue 3, Composition API)</li>
        <li><strong>Styling System:</strong> Tailwind CSS + Lucide Icons</li>
        <li><strong>Database:</strong> SQLite (Dev) / MySQL 8.0+ (Prod)</li>
        <li><strong>PWA:</strong> Service Worker & Web App Manifest</li>
      </ul>
    </div>
    <div class="card">
      <h3>🔒 Security & Access Control</h3>
      <ul style="margin-bottom: 0;">
        <li><strong>RBAC:</strong> Spatie Laravel-Permission</li>
        <li><strong>Roles:</strong> Admin, Doctor, Secretary, Patient</li>
        <li><strong>Route Guarding:</strong> Middleware role checks</li>
        <li><strong>Security:</strong> CSRF tokens, strict validation</li>
        <li><strong>Session:</strong> Encrypted HTTP-only cookies</li>
      </ul>
    </div>
  </div>

  <h2>10. Database Schema & Data Dictionary</h2>
  <table>
    <thead>
      <tr>
        <th style="width: 22%;">Table Name</th>
        <th style="width: 38%;">Purpose</th>
        <th style="width: 40%;">Key Columns & Foreign Keys</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><code>clinics</code></td>
        <td>Clinic branding, contact info & CMS configuration</td>
        <td><code>id, name, tagline, about, logo_url, address, google_map_embed_url, phone, emergency_phone, open_time, close_time</code></td>
      </tr>
      <tr>
        <td><code>users</code></td>
        <td>System accounts with role assignments</td>
        <td><code>id, name, email, phone, specialization, live_status, clinic_id</code></td>
      </tr>
      <tr>
        <td><code>availabilities</code></td>
        <td>Doctor weekly practice hours (7 days)</td>
        <td><code>id, doctor_id, day_of_week (0–6), start_time, end_time, is_available</code></td>
      </tr>
      <tr>
        <td><code>doctor_secretary</code></td>
        <td>Staff allocation matrix between doctors & secretaries</td>
        <td><code>secretary_id (FK: users), doctor_id (FK: users)</code></td>
      </tr>
      <tr>
        <td><code>services</code></td>
        <td>Consultation offerings and pricing</td>
        <td><code>id, clinic_id, name, description, duration_minutes, price, is_active</code></td>
      </tr>
      <tr>
        <td><code>appointments</code></td>
        <td>Bookings and real-time daily queue tickets</td>
        <td><code>id, clinic_id, doctor_id, service_id, patient_id, queue_number, appointment_date, time_slot, status</code></td>
      </tr>
      <tr>
        <td><code>dependents</code></td>
        <td>Patient children and family members</td>
        <td><code>id, user_id (FK: users), name, relationship, age, gender, blood_type, allergies</code></td>
      </tr>
      <tr>
        <td><code>vaccine_reminders</code></td>
        <td>Childhood immunization calendar & status</td>
        <td><code>id, patient_id, patient_name, vaccine_name, due_date, status ('pending'|'completed')</code></td>
      </tr>
      <tr>
        <td><code>medical_records</code></td>
        <td>Archival clinical notes & prescriptions</td>
        <td><code>id, appointment_id, doctor_id, patient_id, patient_name, diagnosis, treatment, prescription, visit_date</code></td>
      </tr>
    </tbody>
  </table>
</div>

<!-- PAGE 9: WEB ROUTES & USER MANUAL -->
<div class="page">
  <h2>11. Web Routes & Endpoint Reference</h2>
  <table>
    <thead>
      <tr>
        <th style="width: 12%;">Method</th>
        <th style="width: 32%;">URI / Endpoint</th>
        <th style="width: 26%;">Route Name</th>
        <th style="width: 30%;">Access Control</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><span class="badge badge-blue">GET</span></td>
        <td><code>/</code></td>
        <td><code>home</code></td>
        <td>Public</td>
      </tr>
      <tr>
        <td><span class="badge badge-blue">GET</span></td>
        <td><code>/book</code></td>
        <td><code>booking.create</code></td>
        <td>Public</td>
      </tr>
      <tr>
        <td><span class="badge badge-green">POST</span></td>
        <td><code>/book</code></td>
        <td><code>booking.store</code></td>
        <td>Public</td>
      </tr>
      <tr>
        <td><span class="badge badge-blue">GET</span></td>
        <td><code>/doctor/dashboard</code></td>
        <td><code>doctor.dashboard</code></td>
        <td>Doctor / Admin</td>
      </tr>
      <tr>
        <td><span class="badge badge-amber">PATCH</span></td>
        <td><code>/doctor/live-status</code></td>
        <td><code>doctor.live-status.update</code></td>
        <td>Doctor / Admin</td>
      </tr>
      <tr>
        <td><span class="badge badge-green">POST</span></td>
        <td><code>/doctor/call-next</code></td>
        <td><code>doctor.call-next</code></td>
        <td>Doctor / Admin</td>
      </tr>
      <tr>
        <td><span class="badge badge-amber">PATCH</span></td>
        <td><code>/doctor/appointments/&#123;id&#125;/complete</code></td>
        <td><code>doctor.consultation.complete</code></td>
        <td>Doctor / Admin</td>
      </tr>
      <tr>
        <td><span class="badge badge-blue">GET</span></td>
        <td><code>/secretary/dashboard</code></td>
        <td><code>secretary.dashboard</code></td>
        <td>Secretary</td>
      </tr>
      <tr>
        <td><span class="badge badge-green">POST</span></td>
        <td><code>/secretary/walk-in</code></td>
        <td><code>secretary.walkin.store</code></td>
        <td>Secretary</td>
      </tr>
      <tr>
        <td><span class="badge badge-blue">GET</span></td>
        <td><code>/patient/dashboard</code></td>
        <td><code>patient.dashboard</code></td>
        <td>Patient</td>
      </tr>
      <tr>
        <td><span class="badge badge-green">POST</span></td>
        <td><code>/patient/dependents</code></td>
        <td><code>patient.dependents.store</code></td>
        <td>Patient</td>
      </tr>
      <tr>
        <td><span class="badge badge-green">POST</span></td>
        <td><code>/demo/generate-patients</code></td>
        <td><code>demo.generate-patients</code></td>
        <td>Authenticated / Demo Bar</td>
      </tr>
    </tbody>
  </table>

  <h2>12. Standard Operating Procedures (SOP)</h2>
  
  <h3>For Front-Desk Receptionists</h3>
  <ol>
    <li>Upon clinic opening, open <code>/secretary/dashboard</code>.</li>
    <li>When an appointment patient arrives at the desk, verify their name or queue ticket and click <strong>Check In</strong>.</li>
    <li>For walk-in patients without an existing appointment, click <strong>+ Add Walk-In</strong>, enter their details, and issue the generated token.</li>
  </ol>

  <h3>For Attending Physicians</h3>
  <ol>
    <li>Log into <code>/doctor/dashboard</code> upon arriving at your examination room.</li>
    <li>Toggle your live status badge to <span class="badge badge-green">In Clinic</span> so patients are alerted.</li>
    <li>Click <strong>Call Next Patient</strong> to advance the highest priority patient from the waiting lobby into your office.</li>
    <li>Input examination findings, diagnosis notes, and medication prescriptions.</li>
    <li>Click <strong>Mark Consultation as Completed</strong> to conclude the visit and save the electronic health record.</li>
  </ol>
</div>

<!-- PAGE 10: INSTALLATION, TESTING & CONCLUSION -->
<div class="page page-last">
  <h2>13. Installation, Deployment & System Administration</h2>
  <p>Clinic Connect is built on modern PHP 8.4 and Laravel 12 standards, making it easy to deploy on any VPS, cloud server, or containerized environment.</p>

  <h3>Step-by-Step Installation Commands</h3>
  <pre># 1. Clone repository and install dependencies
git clone https://github.com/clinicconnect/clinic-connect.git
cd clinic-connect
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Run database migrations & seed demo dataset
php artisan migrate --seed

# 4. Build frontend assets & launch server
npm run build
php artisan serve</pre>

  <h2>14. Quality Assurance & Test Verification</h2>
  <p>The system includes comprehensive automated Feature & Unit test coverage covering authentication, multi-doctor queue progression, availability calculations, and walk-in ingestion.</p>

  <div class="callout callout-success">
    <strong>Test Suite Health: 100% Passed</strong><br>
    Ran <code>php artisan test --compact</code>: <strong>40 tests passed, 157 assertions verified</strong> across all workflows with 0 errors and 0 warnings.
  </div>

  <h2>15. Conclusion & Product Roadmap</h2>
  <p>
    <strong>Clinic Connect MVP</strong> successfully transforms traditional outpatient medical centers into modern, digitally synchronized healthcare hubs. By eliminating lobby congestion, speeding up front-desk triage, and providing physicians with an intuitive workspace, it significantly improves patient satisfaction and clinic productivity.
  </p>

  <div class="card" style="margin-top: 15px; border-left: 3px solid #10b981;">
    <h3 style="color: #065f46;">Future Roadmap Enhancements</h3>
    <ul style="margin-bottom: 0;">
      <li><strong>SMS & WhatsApp Notifications:</strong> Real-time automated text message updates when a patient is "2 patients away".</li>
      <li><strong>Integrated Teleconsultation:</strong> Secure WebRTC audio/video consultations for remote patient follow-ups.</li>
      <li><strong>Pharmacy & Laboratory Billing Module:</strong> Point-of-sale invoicing for dispensed medications and lab diagnostic orders.</li>
    </ul>
  </div>

  <div style="margin-top: 40px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px; color: #94a3b8; font-size: 7.5pt;">
    Clinic Connect System Features & Documentation &bull; Official Release &bull; Prepared by Product & Engineering
  </div>
</div>

</body>
</html>
"""

html_filename = "clinic_connect_documentation.html"
pdf_filename = "Clinic_Connect_System_Documentation_and_Features.pdf"

with open(html_filename, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"Generated HTML documentation: {html_filename} ({os.path.getsize(html_filename)} bytes)")

chrome_path = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
print("Invoking Chrome Headless to compile PDF...")

cmd = [
    chrome_path,
    "--headless=new",
    "--no-pdf-header-footer",
    f"--print-to-pdf={pdf_filename}",
    os.path.abspath(html_filename)
]

result = subprocess.run(cmd, capture_output=True, text=True)
if os.path.exists(pdf_filename):
    print(f"SUCCESS: Generated {pdf_filename} ({os.path.getsize(pdf_filename)} bytes)")
else:
    print(f"ERROR: Failed to generate PDF. Output: {result.stderr}")
