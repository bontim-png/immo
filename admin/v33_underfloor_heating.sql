INSERT INTO features (code,name_key,category,sort_order,is_active,created_at,updated_at)
SELECT 'underfloor_heating','features.underfloor_heating','comfort',90,1,NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM features WHERE code='underfloor_heating');
