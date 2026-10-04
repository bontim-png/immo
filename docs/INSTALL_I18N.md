# i18n installation

1. Replace the application files with the contents of this ZIP.
2. Run `sql/v40_i18n_database_values.sql` once against the existing Immo database.
3. Do not import or recreate the database schema.

The property editor reads property types and features dynamically from `property_types` and `features`. Their labels are resolved through the database i18n keys. Existing values are not changed.

For a future database-driven feature, add the feature normally to `features` and add translations for its `name_key` in `i18n_translations` for `en`, `fr`, and `nl`. If its `name_key` is absent from the translations, the editor will fall back to the feature code rather than inventing a translation.
