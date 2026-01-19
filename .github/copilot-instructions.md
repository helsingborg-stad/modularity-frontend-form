# Copilot Instructions for Modularity Frontend Form

This guide enables AI coding agents to work productively in the Modularity Frontend Form codebase. Follow these conventions and workflows for best results.

## Architecture Overview
- **Frontend**: TypeScript (TS) in `/source/js/` for all UI, field logic, validation, and step navigation. Key patterns:
  - Use interfaces for all APIs and field types (see `fieldsInterface.d.ts`, `editorConfigInterface.d.ts`).
  - Each field type (e.g., WYSIWYG, Checkbox) is a class in `/source/js/fields/field/`.
  - Step navigation and UI management: see `steps/stepNavigator.ts`, `steps/stepUIManager.ts`.
- **Backend**: PHP in `/source/php/` for REST API, admin config, data processing, and field mapping.
  - Field mapping uses mappers and traits (see `FieldMapping/Mapper/Acf/`).
  - Extend REST endpoints in `/source/php/Api/`.
- **Styling**: SCSS in `/source/sass/`. Use variables for theming and keep styles modular.

## Developer Workflows
- **Build**: Use `build.php` for CLI build automation. Supports flags for composer, npm, cleanup, and release.
- **Test**:
  - JS/TS: `npm test` (Jest)
  - PHP: `composer test` (PHPUnit)
- **Lint/Format**: Use Biome for JS/TS: `npm run lint`, `npm run format`. Auto-fix with `lint:write` and `format:write`.
- **Dev Server**: `npm run dev` (Vite)
- **Release**: Use `build.php --release` for production builds. Removes dev files and directories.

## Project-Specific Patterns
- **Field Types**: Add new field types by creating a class in `/source/js/fields/field/` and updating interfaces.
- **Conditional Logic**: Implemented via `conditions/conditionsInterface.d.ts` and PHP mappers.
- **Validation**: Step and field validation via interfaces in JS and PHP.
- **REST API**: All requests require nonces/tokens for security. See PHP API handlers.
- **Accessibility**: All UI must be ARIA-compliant and keyboard accessible.
- **Security**: Escape all output in PHP, validate/sanitize all input, use nonces for REST.

## Integration Points
- **ACF**: Uses Advanced Custom Fields for field groups. See PHP mappers and admin config.
- **External JS**: Uses `@helsingborg-stad/openstreetmap` and other npm packages.
- **SCSS**: Modular, themable styles. Use variables in `_variables.scss`.

## Contribution Guidelines
- Fork, branch, and submit pull requests.
- Write clear commit messages and review for style, security, and performance.
- Follow these instructions and reference this file for all contributions.

## Key Files & Directories
- `/source/js/fields/field/` — Field type classes
- `/source/js/fields/fieldsInterface.d.ts` — Field interfaces
- `/source/js/steps/stepNavigator.ts` — Step navigation logic
- `/source/php/FieldMapping/Mapper/Acf/` — PHP field mappers
- `/source/php/Api/` — REST API endpoints
- `/source/sass/` — SCSS styles
- `build.php` — Build automation
- `README.md` — General documentation

---
For more details, see the source code and this file. All code, documentation, and contributions must follow these workspace guidelines.
