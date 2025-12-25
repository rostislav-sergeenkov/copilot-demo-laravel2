<!--
SYNC IMPACT REPORT
==================
Version Change: Template → 1.0.0
Modified Principles: N/A (initial creation)
Added Sections: All core principles, Technical Standards, Development Workflow, Governance
Removed Sections: N/A
Templates Status:
  ✅ plan-template.md - Constitution Check section aligns
  ✅ spec-template.md - Requirements structure aligns
  ✅ tasks-template.md - Test-first approach aligns
Follow-up TODOs: None
-->

# Expense Tracker Constitution

## Core Principles

### I. Simplicity & Convention Over Configuration

The application MUST follow Laravel's standard conventions and defaults without introducing unnecessary abstraction layers. Single-model architecture (Expense as the sole domain entity) is mandatory. Repository patterns, service layers, or other abstraction layers are prohibited unless explicitly justified by genuine complexity requirements.

**Rationale**: This principle ensures maintainability and reduces cognitive overhead. Laravel's MVC pattern provides sufficient structure for this domain. Additional layers would add complexity without proportional benefit, violating YAGNI principles and making the codebase harder to understand and modify.

### II. Test-First Development (NON-NEGOTIABLE)

All feature work MUST follow this sequence:
1. Write comprehensive tests that specify expected behavior (PHPUnit for unit/feature, Playwright for E2E)
2. Verify tests fail with the current implementation
3. Implement the feature to make tests pass
4. Refactor while keeping tests green

Tests are NOT optional documentation—they are executable specifications. Pull requests MUST include tests for all new functionality.

**Rationale**: Tests serve as living documentation, regression protection, and design validation. The test-first approach forces clear thinking about requirements before implementation, resulting in better-designed, more maintainable code. CI pipeline enforcement ensures this discipline is maintained.

### III. Validation & Data Integrity

Validation rules MUST be centralized in the Expense model via `validationRules()` static method and consumed by Form Request classes (StoreExpenseRequest, UpdateExpenseRequest). Duplicate validation logic is prohibited. All deletions MUST use soft deletes via the SoftDeletes trait—hard deletes are forbidden.

**Rationale**: Centralized validation ensures consistency across all entry points (web, API, console) and provides a single source of truth. Soft deletes preserve data history for audit trails, error recovery, and analytics while protecting against accidental data loss. This supports data integrity and regulatory compliance.

### IV. Material UI Design Standards

All user interfaces MUST follow Material UI design principles: clean layouts, proper spacing, accessible color contrast, responsive design, and semantic HTML. Blade templates MUST use the shared layout (`layouts/app.blade.php`) and reusable partials (e.g., `_form.blade.php`). Custom CSS MUST be minimal and follow Material guidelines.

**Rationale**: Consistent design language improves user experience, accessibility (WCAG compliance), and reduces maintenance burden. Shared components prevent duplication and ensure design system consistency. Material UI principles are battle-tested and provide professional appearance with minimal custom styling.

### V. Code Quality Automation

All code MUST pass Laravel Pint (PSR-12 style) and Larastan (level 5+ static analysis) checks before merge. GitHub Actions CI pipeline MUST enforce these checks on every pull request. PRs with linting or analysis failures MUST NOT be merged. Baseline files (`phpstan-baseline.neon`) should gradually shrink as issues are resolved.

**Rationale**: Automated quality gates remove subjective code review discussions, catch bugs early, and maintain consistent code style across contributors. This reduces technical debt accumulation and makes the codebase more maintainable. CI enforcement ensures compliance without requiring manual verification.

## Technical Standards

### Technology Stack (Mandatory)
- **Language/Runtime**: PHP 8.4+ (strict types enabled)
- **Framework**: Laravel 11 (latest LTS)
- **Database**: SQLite (development), compatible with MySQL/PostgreSQL (production)
- **Frontend**: Blade templates + Vanilla JS (no heavy frameworks)
- **Testing**: PHPUnit (unit/feature), Playwright (E2E)
- **CI/CD**: GitHub Actions

### Data Storage Requirements
- Amount fields: `decimal(10,2)` with model cast to `'decimal:2'`
- Date fields: `date` type with Carbon casting
- Indexes required on: `date`, `category`, `deleted_at`
- Categories stored as ENUM-like constants in model, never in separate table

### Testing Coverage Standards
- **Unit tests**: All model methods and validation rules
- **Feature tests**: All controller actions and form submissions
- **E2E tests**: Happy path for each user journey (create, read, update, delete, daily view, monthly view)
- **Target**: >80% code coverage (monitored but not blocking)

## Development Workflow

### Pre-Commit Requirements
1. Run `composer lint` to auto-fix style issues
2. Run `composer analyze` to check for type errors
3. Run `php artisan test` to verify all tests pass
4. Ensure no console errors in browser testing

### Pull Request Checklist
1. All CI checks pass (tests, Pint, Larastan)
2. Feature tests included for new functionality
3. E2E tests added for user-facing features
4. Database migrations reviewed for reversibility
5. Blade templates validated for accessibility
6. No hard-coded categories (use `Expense::CATEGORIES`)

### Route Conventions
- Custom routes (`/expenses/daily`, `/expenses/monthly`) MUST be defined **before** `Route::resource()` in `routes/web.php`
- RESTful resource routes preferred over custom routes when possible

### Database Conventions
- All model classes MUST use `HasFactory` trait
- Soft-deletable models MUST use `SoftDeletes` trait
- Timestamps enabled by default (`created_at`, `updated_at`)
- Migration files MUST have `up()` and `down()` methods

## Implementation Standards

### Code Quality Standards

**CQ-1: Single Responsibility** - Each class and method must have a single, well-defined responsibility. Controllers handle HTTP requests, Models manage data and relationships, minimal business logic.

**CQ-2: Meaningful Naming** - Use descriptive, intention-revealing names for variables, methods, and classes. Names must explain what the code does without requiring comments. Use domain terminology (expense, category, amount) consistently.

**CQ-3: Keep Methods Small** - Methods must be concise and focused. If a method exceeds 20 lines, refactor into smaller, reusable methods. Complex conditionals must be extracted into well-named private methods.

**CQ-4: No Magic Numbers or Strings** - Use constants (`Expense::CATEGORIES`) or configuration values instead of hardcoded values. Validation rules and business thresholds must be defined in centralized locations.

### Testing Standards

**TS-1: Unit Test Isolation** - Unit tests must be isolated and independent. Mock external dependencies. Each test must verify a single behavior. Tests must not depend on execution order.

**TS-2: Test Naming Convention** - Test methods must clearly describe the scenario being tested. Use the pattern: `test_[action]_[condition]_[expectedResult]`. Example: `test_create_expense_with_valid_data_returns_success`.

**TS-3: Test Data Management** - Use factories and seeders for test data generation. Never rely on production data. Reset database state between tests using `RefreshDatabase` trait.

### User Experience Standards

**UX-1: Material Design Compliance** - All UI components must follow Material Design guidelines. Use consistent spacing (8px grid system), elevation, and typography. Maintain visual hierarchy throughout the application.

**UX-2: Responsive Design** - The interface must be fully functional on desktop, tablet, and mobile devices. Use responsive breakpoints consistently. Touch targets must be minimum 44x44 pixels on mobile.

**UX-3: Error Handling** - Display clear, actionable error messages to users. Validation errors must appear inline near the relevant field. System errors must be user-friendly, not technical stack traces.

**UX-4: Accessibility Standards** - The application must meet WCAG 2.1 AA compliance. All interactive elements must be keyboard accessible. Color contrast ratios must meet minimum standards. Form fields require proper labels and ARIA attributes.

### Performance Standards

**PR-1: Page Load Time** - Initial page load must complete within 2 seconds on standard connections. Time to Interactive (TTI) must be under 3 seconds. Optimize critical rendering path.

**PR-2: API Response Time** - API endpoints must respond within 200ms for simple queries. Complex aggregations (monthly totals, filtered reports) must complete within 500ms. Implement appropriate caching strategies.

**PR-3: Database Optimization** - Use eager loading to prevent N+1 query problems. Index frequently queried columns (date, category). Paginate large result sets rather than loading all records.

**PR-4: Memory Efficiency** - Process large datasets in chunks rather than loading entirely into memory. Avoid memory leaks in frontend JavaScript. Monitor and optimize server memory usage.

## Governance

This constitution supersedes all other development practices and guidelines. Violations of NON-NEGOTIABLE principles (Test-First Development) require explicit justification and architect approval. Other principle violations require documented rationale in pull request descriptions.

### Amendment Process
1. Propose changes via GitHub issue with justification
2. Team discussion and consensus building
3. Document migration plan for existing code
4. Update constitution version following semantic versioning
5. Propagate changes to all template files

### Versioning Policy
- **MAJOR**: Backward-incompatible governance changes or principle removals
- **MINOR**: New principles added or material expansions to existing principles
- **PATCH**: Clarifications, wording improvements, typo fixes

### Compliance Review
All pull requests MUST verify constitution compliance. Reviewers MUST check:
- Test-first workflow followed
- No duplicate validation logic
- Soft deletes used consistently
- Code quality checks passing
- Material UI standards maintained

For runtime development guidance, consult `.github/copilot-instructions.md`.

**Version**: 1.0.0 | **Ratified**: 2025-12-25 | **Last Amended**: 2025-12-25
