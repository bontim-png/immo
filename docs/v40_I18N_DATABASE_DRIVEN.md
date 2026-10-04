# v40 — Database-driven i18n

The property editor does not hardcode the available property types or features. It reads active records from:

- `property_types`
- `features`
- `property_features`

The UI now resolves the displayed label from the record's `name_key`, with compatibility fallbacks for the existing `property_types.*`, `property_type.*`, `features.*` and `feature.*` namespaces.

Feature categories are also translated through `features.category.<category>` when a translation exists.

This means new database features continue to appear automatically. To make a newly added feature multilingual, add its `name_key` translations for `en`, `fr`, and `nl` in `i18n_translations`.

The SQL file in this release seeds the known current property types/features and compatibility keys without changing the feature/property-type records themselves.
