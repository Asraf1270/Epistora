# Laravel Migration Prompt Pack (Step by Step)

Use these prompts one by one with your coding agent to migrate `/v1-old` (legacy raw PHP) into `/Epistorav2` (Laravel).  
Run each step in order. Do not skip to the next step until the current step deliverables are complete.

---

## Step 1 - Project Discovery and Module Inventory

**Prompt:**

```text
Act as a senior Laravel migration engineer.
Context:
- Legacy app path: /v1-old
- New Laravel app path: /Epistorav2

Task:
1) Scan /v1-old and produce a complete module inventory:
   - Auth/login/logout/session files
   - Dashboard/home pages
   - CRUD modules
   - Shared includes (header, footer, topbar, sidebar, config, DB helper)
   - Utility/helper functions
2) List all legacy entry-point pages and inferred routes.
3) Produce a migration map table:
   - Legacy file/path
   - Purpose
   - Target Laravel layer (Route/Controller/Model/View/Middleware)
4) Do not write code yet.

Output format:
- Summary
- Module inventory
- Legacy -> Laravel mapping
- Risks/unknowns
```

---

## Step 2 - Database Reverse Engineering

**Prompt:**

```text
Analyze /v1-old and extract full database structure and relationships.

Task:
1) Identify all tables used by legacy code.
2) For each table, document:
   - Columns, data type guess, nullable/default, PK/FK, indexes, unique keys
3) Infer relationships:
   - one-to-one, one-to-many, many-to-many
4) Detect data quality issues (inconsistent dates, nulls, duplicated keys, etc.).
5) Do not create files yet.

Output format:
- Table-by-table schema matrix
- Relationship diagram in text form
- Migration order based on FK dependencies
- Notes on assumptions
```

---

## Step 3 - Generate Laravel Migrations

**Prompt:**

```text
Using the schema analysis, generate Laravel migrations in /Epistorav2 for all legacy tables.

Requirements:
1) Create migrations in correct FK-safe order.
2) Use best-fit Laravel schema types and constraints.
3) Add indexes/unique constraints exactly where needed.
4) Preserve legacy compatibility where possible.
5) Run migration syntax checks and fix issues.

After coding:
- Show list of created migration files
- Explain key type/constraint decisions
- Provide command to run: php artisan migrate
```

---

## Step 4 - Create Eloquent Models and Relationships

**Prompt:**

```text
Create Eloquent models for all migrated tables in /Epistorav2.

Requirements:
1) Add fillable/guarded fields properly.
2) Add casts for dates, booleans, JSON, numeric fields.
3) Implement all relationships:
   - belongsTo, hasMany, belongsToMany, hasOne
4) Add table name/custom PK only when non-standard.
5) Keep models clean and Laravel-conventional.

After coding:
- List models created/updated
- List relationships added per model
```

---

## Step 5 - Convert Authentication Logic

**Prompt:**

```text
Refactor legacy authentication logic from /v1-old into Laravel auth in /Epistorav2.

Requirements:
1) Replace manual session/login checks with Laravel auth flow.
2) Implement login/logout controller actions (or Laravel auth scaffolding integration).
3) Ensure password hashing compatibility (migrate to Hash::check / Hash::make as needed).
4) Add middleware protection for authenticated pages.
5) Remove auth logic from views.

After coding:
- Show routes and controllers used for auth
- Show middleware groups applied
- Mention any legacy password migration strategy
```

---

## Step 6 - Convert Procedural CRUD/Form Handling to Controllers + Form Requests

**Prompt:**

```text
Convert legacy procedural form handling ($_POST/$_GET, inline SQL) into Laravel controllers with Eloquent and Form Request validation.

Requirements:
1) For each module, create resource-style controller actions where appropriate.
2) Replace raw SQL with Eloquent queries/query builder.
3) Create Form Request classes for validation and authorization.
4) Use redirect()->route()->with() for success/error flash messages.
5) Keep controllers thin and readable.

After coding:
- List controllers and FormRequest files created
- Show legacy file -> controller action mapping
```

---

## Step 7 - Migrate Frontend to Blade Layouts and Components

**Prompt:**

```text
Migrate legacy HTML/CSS from /v1-old into Laravel Blade in /Epistorav2.

Requirements:
1) Create base layout(s) (app/admin/auth as needed).
2) Convert shared partials (topbar/sidebar/footer) into Blade partials/components.
3) Replace hardcoded links with named routes using route().
4) Replace static asset paths with asset() (or Vite if configured).
5) Ensure all forms include @csrf and method spoofing where needed.

After coding:
- List Blade files/components added
- Confirm asset path strategy used
- Confirm navigation links updated to named routes
```

---

## Step 8 - Define Named Routes and Route Groups

**Prompt:**

```text
Refactor and organize routes in /Epistorav2 to follow Laravel best practices.

Requirements:
1) Create named routes for all migrated pages/actions.
2) Group routes by middleware (guest/auth/role).
3) Use prefixes and controller groups for modules.
4) Keep route names consistent and predictable.
5) Remove URL hardcoding from Blade files.

After coding:
- Provide route list summary (name, method, URI, action)
- Highlight any route conflicts resolved
```

---

## Step 9 - Add Middleware and Authorization Rules

**Prompt:**

```text
Implement middleware and authorization rules based on legacy access logic.

Requirements:
1) Add middleware for auth and role/permission checks.
2) Apply middleware to route groups/controllers.
3) Move authorization out of view files and inline checks.
4) If needed, define Gates/Policies for sensitive actions.
5) Keep behavior aligned with legacy permissions.

After coding:
- List middleware/policies created
- Explain where each is applied
```

---

## Step 10 - Data Migration/Seeder Support

**Prompt:**

```text
Prepare data migration support from legacy database into Laravel-compatible structure.

Requirements:
1) Create seeders/factories for baseline data.
2) If legacy import is required, add import script/command for data mapping.
3) Handle date/enum/null normalization.
4) Preserve foreign key integrity during import.

After coding:
- List seeders/commands created
- Provide execution order and commands
```

---

## Step 11 - Testing and Verification

**Prompt:**

```text
Create a verification pass for migrated modules in /Epistorav2.

Requirements:
1) Add feature tests for:
   - Authentication
   - Protected routes
   - Core CRUD success and validation failures
2) Add key model relationship tests.
3) Run tests and fix failures.
4) Provide a concise test report.

After coding:
- Show test files created
- Show pass/fail summary
- Mention remaining risk areas
```

---

## Step 12 - Final Cleanup and Production Readiness

**Prompt:**

```text
Perform final Laravel best-practice cleanup in /Epistorav2.

Checklist:
1) Remove dead legacy patterns and duplicate logic.
2) Ensure consistent validation and error handling.
3) Check N+1 risks and add eager loading where needed.
4) Ensure secure mass assignment and authorization coverage.
5) Confirm code style and directory conventions are Laravel-standard.

Deliverables:
- Final migration completion checklist
- Remaining technical debt list
- Suggested next improvements (short and prioritized)
```

---

## Orchestration Prompt (Optional Master Prompt)

Use this when you want the agent to execute everything in controlled phases:

```text
You are migrating a legacy raw PHP app from /v1-old to Laravel in /Epistorav2.
Execute migration in strict phases:
1) Discovery
2) DB analysis
3) Migrations
4) Models
5) Auth
6) Controllers + FormRequests
7) Blade migration
8) Routes + middleware
9) Seed/import support
10) Tests + cleanup

Rules:
- Complete one phase at a time and report before moving next.
- Keep legacy behavior parity unless explicitly improved.
- Follow Laravel best practices.
- Use named routes, validation classes, middleware, and Eloquent relationships.
- At end of each phase provide changed files and verification steps.
```

