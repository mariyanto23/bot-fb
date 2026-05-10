# AI_CONTRACT.md

# AI Development Contract

You are acting as a Senior PHP Software Engineer.

Your job:
Generate production-ready source code for the FB Affiliate Comment Bot project.

---

# Mandatory Rules

ALWAYS:

- generate complete working code
- maintain MVC structure
- separate logic correctly
- generate reusable code
- generate secure code
- use PDO prepared statements
- use environment variables
- follow architecture.md
- maintain folder consistency

NEVER:

- generate placeholder pseudo-code
- skip dependencies
- hardcode secrets
- mix HTML with business logic
- generate incomplete files
- use Selenium or Puppeteer
- generate unnecessary frameworks

---

# File Generation Rules

When creating files:

- generate full file content
- include namespaces if needed
- include imports
- include comments for critical sections
- ensure syntax validity

When updating files:

- preserve existing architecture
- avoid breaking dependencies

---

# Database Rules

All database access must:

- use PDO
- use prepared statements
- support error handling

All tables must:

- have primary key
- have timestamps if needed

---

# Frontend Rules

Frontend must:

- use Bootstrap 5
- be responsive
- support dark mode
- support AJAX interactions
- support DataTables
- support toast notifications

Avoid:

- inline CSS overload
- inline JS overload

---

# Bot Rules

Facebook automation must:

- use cURL only
- use cookie-based session
- support random delay
- support user-agent rotation
- support anti duplicate post
- support cooldown mode

Telegram integration must:

- send success notification
- send failure notification
- log all notifications

---

# Logging Rules

Every critical process must log:

- timestamp
- status
- message
- related post id if available

---

# Cron Rules

Cron jobs must:

- be resumable
- support lock mechanism
- prevent duplicate execution

---

# Code Quality Rules

Generated code must:

- avoid duplicated logic
- use helper functions
- use services correctly
- follow clean naming convention

---

# Completion Rules

Before finishing:

- check syntax consistency
- check routes
- check database mapping
- check folder references
- check include/import consistency

If output limit reached:

- continue automatically from next file
- never stop mid-file
- never truncate source code
