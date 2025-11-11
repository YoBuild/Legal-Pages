## Purpose

Short, actionable guidance to help AI coding agents quickly become productive in this repository.

## Quickstart (what to run locally)

- Install PHP deps: run `composer install` in the project root.
- The UI pages are static HTML/JS; open `legal-generator.html` (preferred) or `legal-page-generator.html` (legacy) in a browser that can reach the PHP backend, or run a local PHP server from the project root for integrated testing:

  ```powershell
  composer install;
  php -S localhost:8000 -t .
  # then open http://localhost:8000/legal-generator.html
  ```

Note: the frontend expects an AJAX endpoint at `/ajax/` (see `legal-page-manager.js`). Ensure your server rewrites or serves `ajax/` to the PHP handler (see `ajax_legal_handler.php`). This repo was often used with Laragon-style local hosts; adapt server document root so the `ajax/` fetch calls resolve.

## Big-picture architecture

- PHP library code (PSR-4) is under the `Y0hn/` directory and autoloaded via `composer.json` (PSR-4: `Y0hn\` => `Y0hn/`). The canonical legal page generation library lives in `Y0hn/Gens/Legal/`. Edit domain code in that tree.
- The legal templates live in the `legal/` directory, organized by `base/`, `personal/`, `ecommerce/`, `social/`, and `placeholders/` (e.g. `legal/base/privacy-policy.md`). Templates are plain Markdown with repository-specific placeholder and conditional syntax.
- Frontend: `legal-generator.html` (primary UI) + `js/legal-generator.js` provide the multi-step UI and call the AJAX endpoint at `/ajax/` (see `ajax/ajax_legal_handler.php`).
- AJAX entrypoint: `ajax/ajax_legal_handler.php` loads Composer autoload and delegates to `Y0hn\Gens\Legal\LegalPageController`, which routes actions and returns JSON.

Note on new AJAX fields:
- `output_format` (optional): `html`, `markdown`, or `both`. Controls what the server returns and saves. Defaults: preview => `html`, final generation => `both`.
- `theme` (optional): `light`, `dark`, or `auto`. If not provided the controller will try to read client hints (`Sec-CH-Prefers-Color-Scheme`) and otherwise falls back to `dark`.

## Key conventions & patterns (concrete, repo-specific)

- Placeholder format: `{{category:field}}` (examples: `{{company:name}}`, `{{website:url}}`, `{{current:date}}`). See `legal/base/privacy-policy.md`.
- Flat placeholder fallback: code accepts underscore keys and maps `company_name` -> `company:name` in `LegalPageGenerator::setPlaceholders()` for compatibility.
- Conditional sections: use `{{if:token}} ... {{endif}}`. `token` can be a website-type (e.g. `personal`, `ecommerce`, `social`) or a compliance/feature key tested against placeholders (for example `{{if:gdpr}}...{{endif}}`). See `legal/base/privacy-policy.md` for examples.
- Template resolution order: generator looks for `legal/{websiteType}/{pageType}.md` first, then falls back to `legal/base/{pageType}.md`. Keep this rule when adding templates.
- Config system: uses `Yohns\Core\Config` to load site defaults (company name, email, etc.) and provides methods to extract, save, and normalize placeholder data.

## Client ↔ Server flow (concrete files)

- Frontend: `legal-generator.html` + `js/legal-generator.js` build FormData and post to `/ajax/` with action names like `init`, `next_step`, `generate_preview`, `generate_final`.
- Server: `ajax/ajax_legal_handler.php` includes Composer autoload and instantiates `Y0hn\Gens\Legal\LegalPageController`, calling its `handleRequest()` method which routes based on POST['action']. Returns JSON.
- Controller supports both multi-step workflows (init/next_step/previous_step/get_website_presets/generate_preview/generate_final) and classic form flows (form/selectType/generatePage/deletePage).

## Where to change behavior

- Implement/modify generation logic in `Y0hn/Gens/Legal/*` — the unified namespace now contains all generation, configuration, and form-handling classes. Key files:
  - `LegalPageGenerator` — template loading, placeholder replacement, conditionals, Markdown-to-HTML conversion, file saving.
  - `LegalPageTemplate` — template discovery and metadata.
  - `LegalPageConfig` — loads site config and manages placeholder defaults.
  - `LegalPageController` — AJAX action routing and response handling.
  - `LegalContentPresets` — default placeholders by website type.
- Edit / add templates in `legal/` (use `base/` for common text, `personal/`, `ecommerce/`, `social/` for overrides).
- Update frontend UI in `legal-generator.html` and `js/legal-generator.js`. Keep `fetch('/ajax/', { method: 'POST', body: formData })` as the client endpoint.
- Update AJAX handler in `ajax/ajax_legal_handler.php` if you change action routing or directory structure.

## Developer workflows & debugging tips

- Dependency install: `composer install` (project root).
- Live testing: open `legal-page-generator.html` after starting a local PHP server with the project root as document root so `ajax/` routes resolve. Alternatively adapt Laragon or your local virtual host.
- Quick debug: enable errors or use XDebug in PHP; examine network panel in browser to inspect `POST` payloads and JSON responses from `ajax/`.
- Template preview: use the generator classes (`LegalPageGenerator`) to programmatically render Markdown to HTML — `convertToHtml()` uses League/CommonMark.

## Important files to reference (examples)

- `composer.json` — PSR-4 autoload and deps
- `implementation-guide.md` — reference for class stubs (describes older design; see Y0hn/Gens/Legal source for merged implementation)
- `ajax/ajax_legal_handler.php` — AJAX entrypoint routing actions -> LegalPageController
- `legal-generator.html` + `js/legal-generator.js` — current UI and AJAX flow
- `Y0hn/Gens/Legal/*.php` — core generator, template management, config, controller, presets (unified namespace)
- `legal/base/*.md`, `legal/{personal,ecommerce,social}/*.md` — markdown templates with placeholders and conditionals
- `legal/placeholders/cheat-sheet.md` — canonical placeholder names and examples

## Editing rules & safety

- Never change placeholder keys in templates without coordinating the generator: placeholders are expected as `category:field` keys.
- Keep generated output writes constrained to the configured output directory (the example code writes to `/path/to/generated/legal/` — update to a safe path when enabling persistence).

## Notes / Known gaps

- There is no automated test suite present; rely on the browser preview and the `generate_preview` AJAX flow for verification.
- Server routing for `ajax/` may require configuring your webserver or using the provided handler path; the frontend assumes an `ajax/` endpoint.

If anything here looks incomplete or you want more examples (e.g., a short checklist for adding a new template or a small unit test scaffold), tell me which area to expand and I will update the file.
