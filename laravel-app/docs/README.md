# Documentation Index

Welcome to the Expense Tracker documentation. This folder contains all technical documentation for the Laravel application.

## � Documentation Overview

This folder contains **8 documentation files** organized into two main categories:

### 🧪 Testing (5 files)

#### Quick Start
- **[testing-overview.md](./testing-overview.md)** - Complete testing guide and acceptance criteria mapping
  - PHPUnit tests (Unit + Feature)
  - E2E tests overview
  - Quick testing commands
  - Coverage by feature

#### E2E Testing
- **[e2e-testing-guide.md](./e2e-testing-guide.md)** - Complete E2E testing guide
  - Setup instructions
  - Running tests (happy path vs comprehensive)
  - Testing strategy and authentication
  - Debugging guide and helper functions
  - Writing new tests
  - Detailed test inventory (appendix)

#### Test Summaries
- **[unit-tests-summary.md](./unit-tests-summary.md)** - Unit test coverage details
  - Model tests (74 tests)
  - Form request tests (35 tests)
  - Helper function tests (11 tests)
- **[feature-tests-summary.md](./feature-tests-summary.md)** - Feature test coverage
  - Validation tests (22 tests)
  - Database tests (22 tests)
  - Controller tests (36 tests)
---

### 🏗️ Architecture Documentation

- **[project-architecture-blueprint.md](./project-architecture-blueprint.md)** - Complete architecture overview
  - MVC pattern explanation
  - Directory structure
  - Key patterns and conventions
  - Database schema
  - Request lifecycle
  
- **[architecture-testing.md](./architecture-testing.md)** - Architectural testing rules
  - Architecture test categories
  - Naming conventions
  - Layer separation rules
  - Type safety requirements
  - Security rules
  - Running architectural tests

- **[code-quality.md](./code-quality.md)** - Code quality tools and standards
  - Laravel Pint (code style)
  - Larastan (static analysis)
  - CI/CD integration
  - Configuration files
  - Best practices

---

## 🚀 Quick Navigation

### Getting Started with Testing

1. **Run all tests quickly:**
   ```bash
   cd laravel-app
   php artisan test           # Unit + Feature (~8s)
   npm run test:e2e           # E2E Happy Path (~3 min)
   ```

2. **Before deployment:**
   ```bash
   php artisan test           # All Laravel tests
   npm run test:e2e:all       # All E2E tests (~20 min)
   ```

### Understanding the Architecture

1. Read [project-architecture-blueprint.md](./project-architecture-blueprint.md) for overview
2. Check [architecture-testing.md](./architecture-testing.md) for rules and conventions
3. Review [code-quality.md](./code-quality.md) for quality standards

### Working with Tests

1. See [testing-overview.md](./testing-overview.md) for complete testing guide
2. For E2E tests, start with [e2e-testing-guide.md](./e2e-testing-guide.md)
3. Check specific summaries for detailed test breakdowns

---

## 📊 Test Coverage Summary

| Test Type | Count | Duration | Coverage |
|-----------|-------|----------|----------|
| **Unit Tests** | 70 | ~4s | Model logic, validation, helpers |
| **Feature Tests** | 80 | ~7s | HTTP, database, controllers |
| **E2E Happy Path** | 16 | ~3 min | Core user workflows |
| **E2E Comprehensive** | 80+ | ~20 min | All acceptance criteria |
| **Architecture Tests** | - | ~2s | Code structure and patterns |

**Total**: 150+ PHPUnit tests, 80+ E2E tests, 100% acceptance criteria coverage

---

## 📁 File Reference

### Testing (6 files)
```
testing-overview.md              - Main testing guide
e2e-testing-guide.md             - Complete E2E guide (includes auth & test inventory)
unit-tests-summary.md            - Unit test details
feature-tests-summary.md         - Feature test details
complete-test-suite-overview.md  - All tests overview
```

### Architecture (3 files)
```
project-architecture-blueprint.md - Architecture overview
architecture-testing.md           - Architectural rules
code-quality.md                   - Quality standards
```

### Deployment
```
See ../README.md (root) for:
  - Local installation guide
  - Fly.io deployment guide
  - Environment configuration
  - Troubleshooting
```

**Total**: 9 documentation files

---

## 🎯 Documentation Goals

This documentation aims to:

✅ Provide clear guidance for running and writing tests  
✅ Explain architectural decisions and patterns  
✅ Define code quality standards  
✅ Enable quick onboarding for new developers  
✅ Serve as reference during development  

---

## 🔄 Keeping Documentation Updated

When making changes to the application:

1. **Update test summaries** if adding/removing tests
2. **Update architecture docs** if changing patterns
3. **Update testing guide** if changing test strategy
4. **Keep README files in sync** with actual implementation

---

## 💡 Tips

- **New to the project?** Start with [project-architecture-blueprint.md](./project-architecture-blueprint.md)
- **Writing tests?** Check [testing-overview.md](./testing-overview.md) for guidance
- **Running E2E tests?** See [e2e-testing-guide.md](./e2e-testing-guide.md)
- **Understanding code rules?** Read [architecture-testing.md](./architecture-testing.md)
- **Setting up linters?** Follow [code-quality.md](./code-quality.md)

---

**Last Updated**: December 30, 2025  
**Project**: Laravel Expense Tracker  
**Documentation Version**: 2.1
