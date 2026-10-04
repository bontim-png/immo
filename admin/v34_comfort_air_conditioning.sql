-- v34: add air conditioning as a feature.
INSERT INTO features (code, name_key, category, sort_order, is_active, created_at, updated_at)
SELECT 'air_conditioning', 'features.air_conditioning', 'comfort', 91, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM features WHERE code='air_conditioning');
