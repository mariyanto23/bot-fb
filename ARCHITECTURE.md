# ARCHITECTURE.md

# FB Affiliate Comment Bot Architecture

## Project Type

PHP Native MVC Web Application for Shared Hosting Environment.

## Main Goals

- Facebook comment automation
- Telegram notification integration
- GUI monitoring dashboard
- Shared hosting compatibility
- Anti spam protection
- Cron automation

---

# Architecture Pattern

Use:

- MVC Pattern
- Service Layer Pattern
- Repository/Model Pattern
- Helper Utilities
- Modular Structure

---

# Core Principles

- Clean Code
- Reusable Components
- Separation of Concerns
- Secure Coding
- Minimal Dependencies
- Shared Hosting Friendly
- Maintainable Structure
- Human-readable code

---

# Application Layers

## Controllers

Responsibilities:

- Receive request
- Validate request
- Call services
- Return response/view

Controllers must NOT:

- Directly query database
- Contain business logic
- Contain HTML

---

## Models

Responsibilities:

- Database interaction
- CRUD operations
- Prepared statements
- Data mapping

Models must NOT:

- Render HTML
- Handle routing

---

## Services

Responsibilities:

- Business logic
- Facebook processing
- Telegram integration
- Randomization
- Delay handling
- Cooldown handling

---

## Helpers

Responsibilities:

- Utility functions
- cURL wrappers
- Logger utilities
- Auth utilities
- Response formatting

Helpers must be stateless.

---

## Views

Responsibilities:

- UI rendering only
- Bootstrap layout
- Admin dashboard
- AJAX integration

Views must NOT:

- Query database
- Contain business logic

---

# Security Standards

Mandatory:

- PDO Prepared Statements
- CSRF token
- Session validation
- XSS escaping
- Input sanitization

Forbidden:

- Raw SQL queries
- Hardcoded credentials
- Plaintext passwords

---

# Database Rules

Use:

- snake_case
- indexed columns
- timestamps
- foreign keys where needed

Must support:

- anti duplicate post
- logging
- scalability

---

# Bot System Rules

Bot must:

- avoid duplicate comments
- use random delays
- rotate comments
- support cooldown
- detect expired cookies
- support cron resume

Bot must NOT:

- spam continuously
- comment too fast
- hardcode selectors

---

# UI Standards

Use:

- Bootstrap 5
- AdminLTE or Tabler
- Dark Mode
- Responsive Design
- DataTables
- Chart.js

Dashboard must include:

- statistics
- logs
- status indicators
- realtime updates

---

# Performance Rules

Optimize for:

- shared hosting
- low memory
- low CPU usage

Avoid:

- headless browser
- Puppeteer
- Selenium
- heavy frameworks

---

# Folder Standards

/app
/config
/controllers
/models
/services
/helpers
/views

/public
/assets

/storage
/logs
/cache
/cookies

/routes
/cron

---

# Coding Standards

- PSR-12 style
- descriptive naming
- reusable functions
- comments for critical logic
- avoid duplicated code

---

# Deployment Target

Must run on:

- cPanel shared hosting
- PHP 8+
- MySQL
- Apache

Must support:

- cron job execution
- environment variables
- Composer autoload
