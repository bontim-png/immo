# Immo i18n v42 — replacement install

This ZIP replaces the previous i18n ZIPs. Do not install v39/v40/v41 separately.

## 1. Replace application files

Upload/replace the files from this ZIP in `/immobilier/`.

## 2. Run the SQL once

Run:

`sql/v42_i18n_complete.sql`

The SQL uses the actual schema columns:

- `i18n_translations.language_id`
- `i18n_translations.translation_key`
- `i18n_translations.translation_value`

It does **not** use `x.translation_key` or any other non-existent alias column. It is safe to run if some of the translations already exist because each row is guarded by `NOT EXISTS`.

## 3. What was fixed in v42

- Property type labels remain database-driven and are translated through `name_key` with code compatibility fallbacks.
- Feature labels remain database-driven and are translated through `name_key` with code compatibility fallbacks.
- Feature category labels are translated.
- Feature tiles are explicitly clickable again; their checkbox remains the source of truth.
- Leaflet map initialization is made more robust and re-runs when the Location tab is opened.
- Publication/status labels and status dropdown values are translated.
- Mandate type dropdown values are translated.
- Property header facts and completeness labels are translated.
- JavaScript no longer references an undefined PHP `ImmoI18n` value while rendering the page.
- All PHP files pass syntax validation.
- All extracted JavaScript blocks pass syntax validation.

## Future database features

New features can be added directly to `features`. The editor will pick them up automatically. For a new feature to have a real FR/EN/NL label, add translations for its `name_key` in `i18n_translations`.
