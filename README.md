# Modularity Frontend Form

A modular, accessible, and extensible multi-step frontend form system for WordPress, built with TypeScript, PHP, and SCSS. Supports custom field types, validation, REST API integration, and advanced admin configuration.

---

## Features

- **Multi-step forms**: Create forms with any number of steps, each with its own fields, validation, and UI.
- **Custom field types**: Supports text, email, date, checkbox, select, WYSIWYG, repeater, and more.
- **Progress bar**: Visual step progress indicator with validation feedback.
- **REST API integration**: Submit, update, and read form data via secure endpoints.
- **Admin configuration**: Use ACF to configure steps, fields, handlers, and logic.
- **Validation**: Client and server-side validation, including custom error messages.
- **Accessibility**: ARIA attributes, keyboard navigation, and semantic HTML.
- **Status overlays**: User feedback for loading, errors, and success.
- **Security**: Nonces, token validation, and output escaping.
- **Extensible**: Add custom field types, handlers, and hooks.

---

## Usage

### 1. Add a Form Module

- In WordPress admin, add a new \"Frontend Form\" module.
- Configure steps and fields using the ACF field group \"Configure Multistep Form\".
- Each step can have a title, description, and any number of fields.
- Supported field types: text, email, date, checkbox, select, WYSIWYG, repeater, etc.

### 2. Configure Submission Handlers

- Choose where submissions are sent: Database, E-Mail, Webhook.
- Configure handler settings in the module admin (e.g., webhook URL, email recipient).

### 3. Display the Form

- Use the module in any Modularity-enabled area (template, block, shortcode).
- The form will render with animated step transitions, progress bar, and validation.

### 4. REST API Endpoints

- Submit: `POST /wp-json/modularity-frontend-form/v1/submit/post`
- Update: `POST /wp-json/modularity-frontend-form/v1/submit/update`
- Read:   `GET  /wp-json/modularity-frontend-form/v1/read/get`
- Nonce:  `GET  /wp-json/modularity-frontend-form/v1/nonce/get`
- All endpoints require valid nonces and tokens for security.

### 5. Customization

- Add new field types by extending the JS/TS field architecture.
- Add new handlers by implementing PHP handler interfaces.
- Use SCSS variables for theming in `sass/_variables.scss`.
- Override translations in the admin or via language files.

---

## Example: Basic Usage

1. **Add a module**:  
   - Go to \"Add Module\" → \"Frontend Form\".
   - Configure steps and fields.

2. **Display in template**:  
   - Use Modularity's template system or shortcode to render the form.

3. **Handle submissions**:  
   - Data is stored, emailed, or sent to a webhook as configured.

---

## Developer Guide

- **JS/TS**: All frontend logic is in `/source/js/`. Use TypeScript interfaces for all APIs.
- **PHP**: Backend logic, REST API, and admin config in `/source/php/`.
- **SCSS**: Styles in `/source/sass/`. Use variables for theming.
- **Tests**: Unit tests are next to source files. Run with `npm test` (JS/TS) or `composer test` (PHP).
- **Linting**: Use `npm run lint` for JS/TS.

---

## Accessibility & UX

- All UI components are ARIA-compliant and keyboard accessible.
- Animations are smooth and non-blocking.
- Progress bar and step navigation are visually clear.

---

## Security

- All output is escaped in PHP templates.
- All user input is validated and sanitized.
- Nonces and tokens are required for all REST API requests.

---

## Extending the Plugin

- **Add a field type**: Create a new JS/TS class in `/source/js/fields/field/`.
- **Add a handler**: Implement a PHP handler in `/source/php/DataProcessor/Handlers/`.
- **Add a REST endpoint**: Extend `/source/php/Api/`.

---

## Contribution Guidelines

- Fork, branch, and submit pull requests for all changes.
- Write clear commit messages.
- Review code for style, security, and performance.
- Follow the standards in `.github/copilot-instructions.md`.

---

## License

MIT

---

For more details, see `.github/copilot-instructions.md` and the source code. All code, documentation, and contributions must follow workspace guidelines.