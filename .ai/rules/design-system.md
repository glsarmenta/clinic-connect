# Clinic Connect UI Design System & Color Palette Rules

All frontend pages, components, and layouts MUST strictly adhere to this design system to ensure a cohesive, professional, human-centered medical clinic aesthetic (avoiding generic or cold "AI-generated" looks).

## 1. Color Palette

- **Primary Teal / Cyan Brand Accent:**
  - Dark Teal Accent: `#0e7490` / `#155e75` (`teal-700` / `cyan-700`)
  - Primary Action / Buttons: `#38a3b5` / `#1793a9` (`cyan-600` / `brand-teal`)
  - Soft Teal / Active states: `#4ecadc` / `#22d3ee`
  - Subtle Tints / Highlights: `#e6f7fa` / `#cbeef4` / `#dcf5f9`
- **Backgrounds:**
  - Page Background: Warm clean porcelain `#f8fafc` / `#f1f5f9` or soft dual gradient `bg-gradient-to-b from-[#f0f9fb] via-[#f8fafc] to-white`.
  - Hero Card / Feature Frames: Soft arctic cyan gradient `bg-gradient-to-br from-[#cbeef4] via-[#dcf5f9] to-[#bcecf3]`.
  - Content Cards: Crisp white `#ffffff` or frosted glass `bg-white/85 backdrop-blur-md`.
- **Text & Contrast:**
  - Headings: Deep Navy / Slate 900 (`#0f172a` / `#1e293b`), tracking-tight, font-semibold or font-bold.
  - Body Text: Slate 600 (`#475569`) or Slate 700 (`#334155`).
  - Muted / Meta: Slate 400 (`#94a3b8`) or Slate 500 (`#64748b`).

## 2. Geometry, Cards & Glassmorphism

- **Outer Enclosing Card / Hero Container:**
  - Border radius: `rounded-[28px]` to `rounded-[40px]`.
  - Subtle borders: `border border-cyan-100` or `border border-white/60`.
  - Shadows: Soft, diffused drop shadows `shadow-sm` or `shadow-md shadow-cyan-900/5`.
- **Content Cards & Panels:**
  - Border radius: `rounded-2xl` or `rounded-3xl`.
  - Padding: `p-6` to `p-8`.
  - Glass effect: `bg-white/90 backdrop-blur-sm border border-slate-100 shadow-sm`.
- **Navigation & Bars:**
  - Floating pill or rounded navbar: `rounded-full` or `rounded-2xl`, frosted glass `bg-white/80 backdrop-blur-md border border-white/70 shadow-sm`.

## 3. Interactive Elements & Buttons

- **Primary Action Buttons:**
  - Pill shape: `rounded-full` or `rounded-xl`.
  - Color: `bg-[#38a3b5] hover:bg-[#2e8a9a] text-white font-medium shadow-sm transition-all duration-200 active:scale-[0.98]`.
- **Secondary / Ghost Buttons:**
  - Pill shape: `rounded-full border border-slate-200 bg-white/90 text-slate-700 hover:bg-slate-50`.
- **Form Inputs:**
  - Smooth rounded corners: `rounded-xl border border-slate-200 bg-white/95 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 transition-all`.

## 4. Typography & Human-Centric Vibe

- Clean, friendly sans-serif typography (`Figtree`, `Inter`, or `Plus Jakarta Sans`).
- Emphasize clear hierarchies: Large prominent headline, friendly sub-caption, bite-sized chips and badges (`rounded-full px-3 py-1 text-xs font-semibold`).
- Authentic medical iconography (emergency cross, heartbeat pulse, stethoscope, shield).
- Real Filipino contextual defaults (Philippine Peso `₱`, Metro Manila addresses, local clinic hours).
