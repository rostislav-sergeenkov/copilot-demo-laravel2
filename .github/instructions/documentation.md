---
description: Documentation organization guidelines and best practices
applyTo: docs/**
---

# Documentation Organization Guidelines

Guidelines for organizing, naming, and maintaining documentation in the `/docs` folder.

## File Placement Rules

**`/docs` folder** - All technical and development documentation:
- ✅ Testing guides and test summaries
- ✅ Architecture documentation
- ✅ Code quality standards
- ✅ Developer guides (not feature specifications)
- ✅ API documentation
- ✅ Deployment guides

**`/specs` folder** - Feature specifications (GitHub Spec Kit):
- ✅ Feature specs (spec.md, plan.md, quickstart.md)
- ✅ Data models and contracts
- ✅ Requirements and acceptance checklists
- ✅ Research documents for specific features

**`/.github` folder** - GitHub-specific configuration:
- ✅ Workflow files (.github/workflows/)
- ✅ Copilot instructions
- ✅ Issue templates
- ✅ PR templates

**Project root** - Only essential top-level documents:
- ✅ README.md (project overview)
- ✅ CHANGELOG.md (if needed)
- ❌ No other documentation (move to /docs)

**`/laravel-app` folder** - Laravel application:
- ❌ No documentation files (except auto-generated)
- ❌ No README.md (Laravel default - not needed)

---

## File Naming Conventions

**Use kebab-case for all documentation files**:
- ✅ `e2e-testing-guide.md`
- ✅ `architecture-testing.md`
- ✅ `code-quality.md`
- ❌ `TESTING.md` (use `testing-overview.md`)
- ❌ `E2E_Guide.md` (use `e2e-testing-guide.md`)
- ❌ `Project_Architecture.md` (use `project-architecture.md`)

**Descriptive names that indicate content**:
- ✅ `testing-overview.md` (not just `testing.md`)
- ✅ `e2e-testing-guide.md` (not just `e2e.md`)
- ✅ `unit-tests-summary.md` (not just `unit.md`)

---

## Organization Principles

### 1. Consolidate Related Content

**Merge when**:
- Files share the same topic but split into small pieces
- Content is <100 lines and closely related
- Reduces navigation between multiple files
- Creates single source of truth

**Example**: Merge `e2e-quickstart.md`, `e2e-strategy.md`, `e2e-auth.md` → `e2e-testing-guide.md`

### 2. Separate Distinct Concerns

**Keep separate when**:
- Different audiences (developers vs architects)
- Different purposes (guide vs reference)
- File would exceed ~1000 lines
- Frequently accessed independently

**Example**: Keep `testing-overview.md` (guide) separate from `unit-tests-summary.md` (reference)

### 3. Create Category-Based Structure

Group documentation by primary purpose:
- **Testing**: `testing-overview.md`, `e2e-testing-guide.md`, `unit-tests-summary.md`
- **Architecture**: `project-architecture-blueprint.md`, `architecture-testing.md`
- **Quality**: `code-quality.md`

### 4. Always Include docs/README.md

The `/docs` folder MUST have an index file:
- List all documentation files with descriptions
- Organize by category
- Provide quick navigation links
- Include quick start commands
- Explain when to use each document

---

## File Size Guidelines

- **Small** (<200 lines): Consider merging with related docs
- **Medium** (200-600 lines): Ideal size for focused guides
- **Large** (600-1000 lines): Acceptable for comprehensive guides
- **Very Large** (>1000 lines): Consider splitting by topic or adding table of contents

---

## Link Management

**Internal links**:
- ✅ Use relative paths: `./e2e-testing-guide.md`
- ✅ Update all links when renaming files
- ❌ Don't use absolute paths: `/docs/guide.md`
- ❌ Don't link to removed files

**External links**:
- Always use full URLs
- Keep links up to date with referenced resources

---

## Maintenance Rules

### When Adding New Documentation

1. Determine if it fits existing file or needs new file
2. Follow kebab-case naming convention
3. Place in correct folder (/docs vs /specs)
4. Update docs/README.md index
5. Update any related cross-references

### When Updating Documentation

1. Keep file focused on single topic
2. Update last modified date (if applicable)
3. Check and update all internal links
4. Ensure consistency with related docs

### When Removing Documentation

1. Remove obsolete or redundant files
2. Update docs/README.md index
3. Remove or update links from other files
4. Keep only current, useful documentation

---

## Anti-Patterns to Avoid

- ❌ Creating multiple small files for same topic
- ❌ Mixing different naming conventions
- ❌ Outdated documentation that contradicts code
- ❌ Duplicate content across multiple files
- ❌ Generic framework READMEs (e.g., Laravel default)
- ❌ Historical documents (summaries of past work)
- ❌ Analysis documents without actionable information

---

## Documentation Lifecycle

1. **Create**: Write focused, actionable documentation
2. **Review**: Ensure it fits organization structure
3. **Maintain**: Update when code changes
4. **Consolidate**: Merge related docs as project evolves
5. **Remove**: Delete when obsolete or redundant

---

## Examples of Proper Organization

### Good Structure
```
docs/
├── README.md (index)
├── testing-overview.md (guide)
├── e2e-testing-guide.md (comprehensive)
├── unit-tests-summary.md (reference)
├── feature-tests-summary.md (reference)
├── complete-test-suite-overview.md (summary)
├── project-architecture-blueprint.md (architecture)
├── architecture-testing.md (rules)
└── code-quality.md (standards)
```

### Bad Structure
```
docs/
├── testing.md
├── e2e-quickstart.md
├── e2e-strategy.md
├── e2e-commands.md
├── E2E_Auth.md
├── UNIT-TESTS.md
├── architecture_guide.md
└── Quality.MD
```

---

## Current Documentation Structure

**8 files organized into categories:**

### Testing (5 files)
- `testing-overview.md` - Complete testing guide
- `e2e-testing-guide.md` - E2E testing with Playwright
- `unit-tests-summary.md` - Unit test coverage
- `feature-tests-summary.md` - Feature test coverage
- `complete-test-suite-overview.md` - All tests summary

### Architecture (3 files)
- `project-architecture-blueprint.md` - Complete architecture overview
- `architecture-testing.md` - Architectural testing rules
- `code-quality.md` - Code quality tools and standards

---

## Quick Reference

When working with documentation:

1. **Always use kebab-case** for filenames
2. **Update docs/README.md** when adding/removing files
3. **Use relative links** (`./filename.md`)
4. **Consolidate** files <200 lines with related content
5. **Separate** different concerns (guide vs reference)
6. **Remove** historical or redundant documentation
