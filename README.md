# Primary & Family Care Electronic Health Record (EHR) System
### Core PHP 8.2+ MVC + AJAX REST API + Vanilla ES6 Component Frontend

An enterprise-grade, HIPAA-compliant, and WCAG 2.1 AA accessible Electronic Health Record (EHR) platform designed specifically for Primary Care and Family Medicine practices. Built with a decoupled MVC/REST architecture in pure PHP (no external framework dependencies) and a modern component-driven Vanilla JS frontend. It covers General/Family Medicine, Pediatrics, and OB/GYN.

---

## 1. Directory Structure

```text
pf_ehr/
├── backend/
│   ├── app/
│   │   ├── Controllers/         # Handles REST API requests and responses
│   │   │   ├── AuthController.php
│   │   │   ├── PatientController.php
│   │   │   ├── CalendarController.php
│   │   │   ├── ClinicalController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── MessagingController.php
│   │   │   └── BillingController.php
│   │   ├── Models/              # Database models using Prepared Statements
│   │   │   └── Database.php
│   │   ├── Middleware/          # Request filters (Session, CSRF, Rate Limiting, RBAC)
│   │   │   ├── AuthenticationMiddleware.php
│   │   │   ├── CSRFMiddleware.php
│   │   │   ├── RateLimitingMiddleware.php
│   │   │   └── RBACMiddleware.php
│   │   ├── Services/            # Business operations (HIPAA Auditing, Crypto)
│   │   │   ├── EncryptionService.php
│   │   │   └── AuditLogger.php
│   │   ├── Security/            # Security engines
│   │   │   ├── SessionManager.php
│   │   │   └── CSRFTokenManager.php
│   │   └── Validators/          # Strict input validators
│   │       └── RequestValidator.php
│   ├── config/                  # Configuration files
│   │   ├── database.php
│   │   └── security.php
│   ├── routes/                  # API endpoint registration
│   │   └── api.php
│   └── bootstrap/               # Initialization, headers, exception handling
│       └── app.php
├── public/                      # Public Web Root
│   ├── css/
│   │   └── theme.css            # System-wide design & contrast themes
│   ├── js/
│   │   ├── components/          # Reusable Vanilla UI Components (Modal, Toast, Tabs, Accordion)
│   │   │   ├── modal.js
│   │   │   ├── toast.js
│   │   │   └── accordion.js
│   │   ├── security/            # Developer tools for enforcement
│   │   │   └── inspector.js     # Security Inspector (detects inline CSS/JS and WCAG failures)
│   │   ├── api.js               # Fetch wrapper with CSRF injection
│   │   ├── router.js            # Frontend single-page layout loader
│   │   └── app.js               # Main application logic and calculations (BMI, EDD)
│   ├── modules/                 # SPA Views/Fragments loaded dynamically
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   ├── calendar.php
│   │   ├── patients.php
│   │   ├── clinical.php
│   │   ├── telehealth.php
│   │   ├── messaging.php
│   │   ├── billing.php
│   │   ├── documents.php
│   │   ├── administration.php
│   │   └── reports.php
│   └── index.php                # Front Controller & SPA Bootstrapper
└── storage/                     # Document store outside public web root
    └── documents/
```

---

## 2. Database Setup

1. Create a MySQL database named `pf_ehr`.
2. Import the schema file located in `backend/config/schema.sql`.
3. Update `backend/config/database.php` with your database credentials.
