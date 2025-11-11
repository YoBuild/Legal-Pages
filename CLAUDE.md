# Claude.ai Instructions for this Repository

Short, focused guidance to help Claude-based agents become productive quickly.

Quickstart

- Install PHP deps: run `composer install` in the project root.
- Run a local PHP server from the repo root so the frontend can reach the backend:

```powershell
composer install;
php -S localhost:8000 -t .
# then open http://localhost:8000/legal-generator.html
```

Note: `legal-generator.html` is the active frontend. `legal-page-generator.html` is an older template kept for reference.

## Big picture (short)

- PHP library code lives under `Y0hn/Gens/Legal/` and is autoloaded via `composer.json` (PSR-4: `Y0hn\` => `Y0hn/`).
- Markdown templates are in `legal/` organized by `base/`, `personal/`, `ecommerce/`, `social/`, and `placeholders/`.
- Frontend: `legal-generator.html` + `js/legal-generator.js` (or legacy `legal-page-manager.js`) calls the AJAX endpoint `ajax/` with FormData.
- Server AJAX entrypoint: `ajax/ajax_legal_handler.php` (expects POST requests and delegates to `Y0hn\Gens\Legal\LegalPageController`).
- Uses `Yohns\Core\Config` for site configuration integration.

New AJAX fields
- `output_format` (optional): one of `html`, `markdown`, or `both`. Controls what the server returns/saves. Defaults: preview -> `html`, generate -> `both`.
- `theme` (optional): explicit UI theme preference `light`, `dark`, or `auto`. If not provided, the controller will attempt to read client hints and otherwise fall back to `dark`.
Concrete conventions to use in tasks

- Placeholders: `{{category:field}}` (e.g., `{{company:name}}`, `{{website:url}}`, `{{current:date}}`). Use these exact keys when editing templates.
- Flat keys supported: e.g., `company_name` may be mapped to `company:name` by the generator.
- Conditionals: `{{if:token}}...{{endif}}`. `token` may be a website type (`personal|ecommerce|social`) or a compliance/feature key checked against placeholders (`gdpr`, `ccpa`, etc.). See `legal/base/privacy-policy.md` for examples.
- Template resolution: prefer `legal/{websiteType}/{pageType}.md`; fall back to `legal/base/{pageType}.md`.

## Files to reference (high value)

- `composer.json` — dependency list and PSR-4 autoload mapping
- `implementation-guide.md` — canonical description and PHP class stubs (see source for merged implementation)
- `ajax/ajax_legal_handler.php` — how the frontend maps to backend controller methods
- `legal-generator.html` and `js/legal-generator.js` — current UI and AJAX flow
- `Y0hn/Gens/Legal/*.php` — merged generator, template mgmt, config, controller, presets
- `legal/base/*.md` and `legal/{personal,ecommerce,social}/*.md` — template examples and placeholder usage
- `legal/placeholders/cheat-sheet.md` — canonical placeholder names and examples

Editing rules (required)

- Do not rename or remove placeholder tokens in templates without updating the generator logic that maps placeholders (see `LegalPageGenerator::setPlaceholders()` in `implementation-guide.md`).
- Keep file operations confined to the intended output directory. The example generator writes to `/path/to/generated/legal/` — change to a safe path when enabling write operations.
- For AJAX flows, preserve FormData keys and expected response shape: JSON with `success`, `html`, `markdown`, `filename`, etc.

If you need a minimal task to validate changes, prefer:

1. Add or update a small template under `legal/base/` (e.g., `privacy-policy.md`) using `{{current:date}}` and one placeholder such as `{{company:name}}`.
2. Use the `LegalPageGenerator` (per `implementation-guide.md`) to call `generate()` and `convertToHtml()` in a short PHP script that loads Composer's autoloader.

If you'd like, I can also add a small `scripts/preview.php` runner and a one-file PHPUnit test for the generator; tell me and I'll create them.
