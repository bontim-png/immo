-- Immo i18n additions for admin/property-edit.php
-- Safe to run repeatedly: existing values for these keys are updated.
-- Requires i18n_languages rows with codes: en, fr, nl.

INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.translation_key, x.translation_value
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'properties.location_intro' translation_key,'Property address and approximate public location.' translation_value UNION ALL
  SELECT 'fr','properties.location_intro','Adresse du bien et localisation publique approximative.' UNION ALL
  SELECT 'nl','properties.location_intro','Adres van het pand en geschatte openbare locatie.' UNION ALL
  SELECT 'en','properties.address','Address' UNION ALL
  SELECT 'fr','properties.address','Adresse' UNION ALL
  SELECT 'nl','properties.address','Adres' UNION ALL
  SELECT 'en','properties.property_location','Property location' UNION ALL
  SELECT 'fr','properties.property_location','Localisation du bien' UNION ALL
  SELECT 'nl','properties.property_location','Locatie van het pand' UNION ALL
  SELECT 'en','properties.location_note','If a postcode is entered, the map shows the geographic contour of that postcode area. No radius is used for a postcode. If there is no postcode, the city is shown with a 10 km radius. The exact property address is never displayed as a public marker.' UNION ALL
  SELECT 'fr','properties.location_note','Si un code postal est saisi, la carte affiche le contour géographique de cette zone. Aucun rayon n’est utilisé pour un code postal. Sans code postal, la ville est affichée avec un rayon de 10 km. L’adresse exacte du bien n’est jamais affichée comme marqueur public.' UNION ALL
  SELECT 'nl','properties.location_note','Als een postcode wordt ingevoerd, toont de kaart de geografische contour van het postcodegebied. Er wordt geen straal gebruikt voor een postcode. Zonder postcode wordt de stad weergegeven met een straal van 10 km. Het exacte adres van het pand wordt nooit als openbare markering weergegeven.' UNION ALL
  SELECT 'en','properties.public_area','Public area' UNION ALL
  SELECT 'fr','properties.public_area','Zone publique' UNION ALL
  SELECT 'nl','properties.public_area','Openbaar gebied' UNION ALL
  SELECT 'en','properties.location_map','Location map' UNION ALL
  SELECT 'fr','properties.location_map','Carte de localisation' UNION ALL
  SELECT 'nl','properties.location_map','Locatiekaart' UNION ALL
  SELECT 'en','properties.document','Document' UNION ALL
  SELECT 'fr','properties.document','Document' UNION ALL
  SELECT 'nl','properties.document','Document' UNION ALL
  SELECT 'en','properties.publishing','Publishing' UNION ALL
  SELECT 'fr','properties.publishing','Publication' UNION ALL
  SELECT 'nl','properties.publishing','Publicatie' UNION ALL
  SELECT 'en','properties.sold','Sold' UNION ALL
  SELECT 'fr','properties.sold','Vendu' UNION ALL
  SELECT 'nl','properties.sold','Verkocht' UNION ALL
  SELECT 'en','properties.platform','Platform' UNION ALL
  SELECT 'fr','properties.platform','Plateforme' UNION ALL
  SELECT 'nl','properties.platform','Platform' UNION ALL
  SELECT 'en','properties.completeness','Completeness' UNION ALL
  SELECT 'fr','properties.completeness','Complétude' UNION ALL
  SELECT 'nl','properties.completeness','Volledigheid' UNION ALL
  SELECT 'en','properties.comfort','Comfort' UNION ALL
  SELECT 'fr','properties.comfort','Confort' UNION ALL
  SELECT 'nl','properties.comfort','Comfort' UNION ALL
  SELECT 'en','properties.swimming_pool','Swimming pool' UNION ALL
  SELECT 'fr','properties.swimming_pool','Piscine' UNION ALL
  SELECT 'nl','properties.swimming_pool','Zwembad' UNION ALL
  SELECT 'en','properties.pool_dimensions','Pool dimensions' UNION ALL
  SELECT 'fr','properties.pool_dimensions','Dimensions de la piscine' UNION ALL
  SELECT 'nl','properties.pool_dimensions','Afmetingen zwembad' UNION ALL
  SELECT 'en','properties.pricing_specs','Pricing & specifications' UNION ALL
  SELECT 'fr','properties.pricing_specs','Prix et caractéristiques' UNION ALL
  SELECT 'nl','properties.pricing_specs','Prijs en specificaties' UNION ALL
  SELECT 'en','properties.details_intro','Core property information, pricing and specifications.' UNION ALL
  SELECT 'fr','properties.details_intro','Informations essentielles du bien, prix et caractéristiques.' UNION ALL
  SELECT 'nl','properties.details_intro','Belangrijke gegevens van het pand, prijs en specificaties.' UNION ALL
  SELECT 'en','properties.history_coming','Property history will be added here.' UNION ALL
  SELECT 'fr','properties.history_coming','L’historique du bien sera ajouté ici.' UNION ALL
  SELECT 'nl','properties.history_coming','De geschiedenis van het pand wordt hier toegevoegd.' UNION ALL
  SELECT 'en','properties.energy_coming','Energy information will be added here.' UNION ALL
  SELECT 'fr','properties.energy_coming','Les informations énergétiques seront ajoutées ici.' UNION ALL
  SELECT 'nl','properties.energy_coming','Energie-informatie wordt hier toegevoegd.' UNION ALL
  SELECT 'en','properties.transaction.sale','Sale' UNION ALL
  SELECT 'fr','properties.transaction.sale','Vente' UNION ALL
  SELECT 'nl','properties.transaction.sale','Verkoop' UNION ALL
  SELECT 'en','properties.transaction.rent','Rent' UNION ALL
  SELECT 'fr','properties.transaction.rent','Location' UNION ALL
  SELECT 'nl','properties.transaction.rent','Verhuur' UNION ALL
  SELECT 'en','properties.mandate_type.simple','Simple' UNION ALL
  SELECT 'fr','properties.mandate_type.simple','Simple' UNION ALL
  SELECT 'nl','properties.mandate_type.simple','Eenvoudig' UNION ALL
  SELECT 'en','properties.mandate_type.exclusive','Exclusive' UNION ALL
  SELECT 'fr','properties.mandate_type.exclusive','Exclusif' UNION ALL
  SELECT 'nl','properties.mandate_type.exclusive','Exclusief' UNION ALL
  SELECT 'en','properties.mandate_type.semi_exclusive','Semi-exclusive' UNION ALL
  SELECT 'fr','properties.mandate_type.semi_exclusive','Semi-exclusif' UNION ALL
  SELECT 'nl','properties.mandate_type.semi_exclusive','Semi-exclusief' UNION ALL
  SELECT 'en','properties.status.draft','Draft' UNION ALL
  SELECT 'fr','properties.status.draft','Brouillon' UNION ALL
  SELECT 'nl','properties.status.draft','Concept' UNION ALL
  SELECT 'en','properties.status.active','Active' UNION ALL
  SELECT 'fr','properties.status.active','Actif' UNION ALL
  SELECT 'nl','properties.status.active','Actief' UNION ALL
  SELECT 'en','properties.status.sold','Sold' UNION ALL
  SELECT 'fr','properties.status.sold','Vendu' UNION ALL
  SELECT 'nl','properties.status.sold','Verkocht' UNION ALL
  SELECT 'en','properties.status.rented','Rented' UNION ALL
  SELECT 'fr','properties.status.rented','Loué' UNION ALL
  SELECT 'nl','properties.status.rented','Verhuurd' UNION ALL
  SELECT 'en','properties.status.withdrawn','Withdrawn' UNION ALL
  SELECT 'fr','properties.status.withdrawn','Retiré' UNION ALL
  SELECT 'nl','properties.status.withdrawn','Ingetrokken'
) x ON x.code=l.code
ON DUPLICATE KEY UPDATE translation_value=VALUES(translation_value), updated_at=CURRENT_TIMESTAMP;
