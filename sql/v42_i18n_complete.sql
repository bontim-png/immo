-- v42: complete i18n seed for database-driven property types/features and property-editor UI.
-- Safe to run after v41 or on a fresh database. Existing language/key rows are preserved.
-- IMPORTANT: uses the actual i18n_translations columns: language_id, translation_key, translation_value.

SET NAMES utf8mb4;

INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.k, x.v
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'property_types.house' k,'House' v UNION ALL SELECT 'fr','property_types.house','Maison' UNION ALL SELECT 'nl','property_types.house','Huis'
  UNION ALL SELECT 'en','property_types.villa','Villa' UNION ALL SELECT 'fr','property_types.villa','Villa' UNION ALL SELECT 'nl','property_types.villa','Villa'
  UNION ALL SELECT 'en','property_types.apartment','Apartment' UNION ALL SELECT 'fr','property_types.apartment','Appartement' UNION ALL SELECT 'nl','property_types.apartment','Appartement'
  UNION ALL SELECT 'en','property_types.land','Land' UNION ALL SELECT 'fr','property_types.land','Terrain' UNION ALL SELECT 'nl','property_types.land','Grond'
  UNION ALL SELECT 'en','property_types.commercial','Commercial' UNION ALL SELECT 'fr','property_types.commercial','Local commercial' UNION ALL SELECT 'nl','property_types.commercial','Commercieel'
  UNION ALL SELECT 'en','property_types.building','Building' UNION ALL SELECT 'fr','property_types.building','Immeuble' UNION ALL SELECT 'nl','property_types.building','Gebouw'
  UNION ALL SELECT 'en','property_types.farm','Farm' UNION ALL SELECT 'fr','property_types.farm','Ferme' UNION ALL SELECT 'nl','property_types.farm','Boerderij'
  UNION ALL SELECT 'en','property_types.other' ,'Other' UNION ALL SELECT 'fr','property_types.other','Autre' UNION ALL SELECT 'nl','property_types.other','Overig'
  UNION ALL SELECT 'en','property_type.house','House' UNION ALL SELECT 'fr','property_type.house','Maison' UNION ALL SELECT 'nl','property_type.house','Huis'
  UNION ALL SELECT 'en','property_type.villa','Villa' UNION ALL SELECT 'fr','property_type.villa','Villa' UNION ALL SELECT 'nl','property_type.villa','Villa'
  UNION ALL SELECT 'en','property_type.apartment','Apartment' UNION ALL SELECT 'fr','property_type.apartment','Appartement' UNION ALL SELECT 'nl','property_type.apartment','Appartement'
  UNION ALL SELECT 'en','property_type.land','Land' UNION ALL SELECT 'fr','property_type.land','Terrain' UNION ALL SELECT 'nl','property_type.land','Grond'
  UNION ALL SELECT 'en','property_type.commercial','Commercial' UNION ALL SELECT 'fr','property_type.commercial','Local commercial' UNION ALL SELECT 'nl','property_type.commercial','Commercieel'
  UNION ALL SELECT 'en','property_type.building','Building' UNION ALL SELECT 'fr','property_type.building','Immeuble' UNION ALL SELECT 'nl','property_type.building','Gebouw'
  UNION ALL SELECT 'en','property_type.farm','Farm' UNION ALL SELECT 'fr','property_type.farm','Ferme' UNION ALL SELECT 'nl','property_type.farm','Boerderij'
  UNION ALL SELECT 'en','property_type.other','Other' UNION ALL SELECT 'fr','property_type.other','Autre' UNION ALL SELECT 'nl','property_type.other','Overig'
) x ON x.code=l.code
WHERE l.is_active=1
AND NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.k);

INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.k, x.v
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'features.pool' k,'Pool' v UNION ALL SELECT 'fr','features.pool','Piscine' UNION ALL SELECT 'nl','features.pool','Zwembad'
  UNION ALL SELECT 'en','features.garden','Garden' UNION ALL SELECT 'fr','features.garden','Jardin' UNION ALL SELECT 'nl','features.garden','Tuin'
  UNION ALL SELECT 'en','features.terrace','Terrace' UNION ALL SELECT 'fr','features.terrace','Terrasse' UNION ALL SELECT 'nl','features.terrace','Terras'
  UNION ALL SELECT 'en','features.garage','Garage' UNION ALL SELECT 'fr','features.garage','Garage' UNION ALL SELECT 'nl','features.garage','Garage'
  UNION ALL SELECT 'en','features.parking','Parking' UNION ALL SELECT 'fr','features.parking','Parking' UNION ALL SELECT 'nl','features.parking','Parkeerplaats'
  UNION ALL SELECT 'en','features.fireplace','Fireplace' UNION ALL SELECT 'fr','features.fireplace','Cheminée' UNION ALL SELECT 'nl','features.fireplace','Open haard'
  UNION ALL SELECT 'en','features.air_conditioning','Air conditioning' UNION ALL SELECT 'fr','features.air_conditioning','Climatisation' UNION ALL SELECT 'nl','features.air_conditioning','Airconditioning'
  UNION ALL SELECT 'en','features.gite','Gîte' UNION ALL SELECT 'fr','features.gite','Gîte' UNION ALL SELECT 'nl','features.gite','Gîte'
  UNION ALL SELECT 'en','features.annex','Annex' UNION ALL SELECT 'fr','features.annex','Dépendance' UNION ALL SELECT 'nl','features.annex','Bijgebouw'
  UNION ALL SELECT 'en','features.renovated','Renovated' UNION ALL SELECT 'fr','features.renovated','Rénové' UNION ALL SELECT 'nl','features.renovated','Gerenoveerd'
  UNION ALL SELECT 'en','features.heating_oil','Oil heating' UNION ALL SELECT 'fr','features.heating_oil','Chauffage au fioul' UNION ALL SELECT 'nl','features.heating_oil','Stookolieverwarming'
  UNION ALL SELECT 'en','features.heating_wood','Wood heating' UNION ALL SELECT 'fr','features.heating_wood','Chauffage au bois' UNION ALL SELECT 'nl','features.heating_wood','Houtverwarming'
  UNION ALL SELECT 'en','features.heating_gas','Gas heating' UNION ALL SELECT 'fr','features.heating_gas','Chauffage au gaz' UNION ALL SELECT 'nl','features.heating_gas','Gasverwarming'
  UNION ALL SELECT 'en','features.heating_heat_pump','Heat pump' UNION ALL SELECT 'fr','features.heating_heat_pump','Pompe à chaleur' UNION ALL SELECT 'nl','features.heating_heat_pump','Warmtepomp'
  UNION ALL SELECT 'en','features.underfloor_heating','Underfloor heating' UNION ALL SELECT 'fr','features.underfloor_heating','Chauffage au sol' UNION ALL SELECT 'nl','features.underfloor_heating','Vloerverwarming'
  UNION ALL SELECT 'en','features.comfort','Comfort' UNION ALL SELECT 'fr','features.comfort','Confort' UNION ALL SELECT 'nl','features.comfort','Comfort'
  UNION ALL SELECT 'en','features.category.other','Other' UNION ALL SELECT 'fr','features.category.other','Autre' UNION ALL SELECT 'nl','features.category.other','Overig'
  UNION ALL SELECT 'en','features.category.comfort','Comfort' UNION ALL SELECT 'fr','features.category.comfort','Confort' UNION ALL SELECT 'nl','features.category.comfort','Comfort'
  UNION ALL SELECT 'en','features.category.outdoor','Outdoor' UNION ALL SELECT 'fr','features.category.outdoor','Extérieur' UNION ALL SELECT 'nl','features.category.outdoor','Buiten'
  UNION ALL SELECT 'en','features.category.interior','Interior' UNION ALL SELECT 'fr','features.category.interior','Intérieur' UNION ALL SELECT 'nl','features.category.interior','Interieur'
  UNION ALL SELECT 'en','features.category.security','Security' UNION ALL SELECT 'fr','features.category.security','Sécurité' UNION ALL SELECT 'nl','features.category.security','Beveiliging'
  UNION ALL SELECT 'en','feature.pool','Pool' UNION ALL SELECT 'fr','feature.pool','Piscine' UNION ALL SELECT 'nl','feature.pool','Zwembad'
  UNION ALL SELECT 'en','feature.garden','Garden' UNION ALL SELECT 'fr','feature.garden','Jardin' UNION ALL SELECT 'nl','feature.garden','Tuin'
  UNION ALL SELECT 'en','feature.terrace','Terrace' UNION ALL SELECT 'fr','feature.terrace','Terrasse' UNION ALL SELECT 'nl','feature.terrace','Terras'
  UNION ALL SELECT 'en','feature.garage','Garage' UNION ALL SELECT 'fr','feature.garage','Garage' UNION ALL SELECT 'nl','feature.garage','Garage'
  UNION ALL SELECT 'en','feature.parking','Parking' UNION ALL SELECT 'fr','feature.parking','Parking' UNION ALL SELECT 'nl','feature.parking','Parkeerplaats'
  UNION ALL SELECT 'en','feature.fireplace','Fireplace' UNION ALL SELECT 'fr','feature.fireplace','Cheminée' UNION ALL SELECT 'nl','feature.fireplace','Open haard'
  UNION ALL SELECT 'en','feature.air_conditioning','Air conditioning' UNION ALL SELECT 'fr','feature.air_conditioning','Climatisation' UNION ALL SELECT 'nl','feature.air_conditioning','Airconditioning'
  UNION ALL SELECT 'en','feature.gite','Gîte' UNION ALL SELECT 'fr','feature.gite','Gîte' UNION ALL SELECT 'nl','feature.gite','Gîte'
  UNION ALL SELECT 'en','feature.annex','Annex' UNION ALL SELECT 'fr','feature.annex','Dépendance' UNION ALL SELECT 'nl','feature.annex','Bijgebouw'
  UNION ALL SELECT 'en','feature.renovated','Renovated' UNION ALL SELECT 'fr','feature.renovated','Rénové' UNION ALL SELECT 'nl','feature.renovated','Gerenoveerd'
) x ON x.code=l.code
WHERE l.is_active=1
AND NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.k);

-- Property editor status, publication and mandate values.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.k, x.v
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'properties.status.draft' k,'Draft' v UNION ALL SELECT 'fr','properties.status.draft','Brouillon' UNION ALL SELECT 'nl','properties.status.draft','Concept'
  UNION ALL SELECT 'en','properties.status.active','Active' UNION ALL SELECT 'fr','properties.status.active','Actif' UNION ALL SELECT 'nl','properties.status.active','Actief'
  UNION ALL SELECT 'en','properties.status.sold','Sold' UNION ALL SELECT 'fr','properties.status.sold','Vendu' UNION ALL SELECT 'nl','properties.status.sold','Verkocht'
  UNION ALL SELECT 'en','properties.status.rented','Rented' UNION ALL SELECT 'fr','properties.status.rented','Loué' UNION ALL SELECT 'nl','properties.status.rented','Verhuurd'
  UNION ALL SELECT 'en','properties.status.withdrawn','Withdrawn' UNION ALL SELECT 'fr','properties.status.withdrawn','Retiré' UNION ALL SELECT 'nl','properties.status.withdrawn','Ingetrokken'
  UNION ALL SELECT 'en','properties.mandate_type.simple','Simple' UNION ALL SELECT 'fr','properties.mandate_type.simple','Simple' UNION ALL SELECT 'nl','properties.mandate_type.simple','Eenvoudig'
  UNION ALL SELECT 'en','properties.mandate_type.exclusive','Exclusive' UNION ALL SELECT 'fr','properties.mandate_type.exclusive','Exclusif' UNION ALL SELECT 'nl','properties.mandate_type.exclusive','Exclusief'
  UNION ALL SELECT 'en','properties.mandate_type.semi_exclusive','Semi-exclusive' UNION ALL SELECT 'fr','properties.mandate_type.semi_exclusive','Semi-exclusif' UNION ALL SELECT 'nl','properties.mandate_type.semi_exclusive','Semi-exclusief'
  UNION ALL SELECT 'en','properties.transaction.sale','Sale' UNION ALL SELECT 'fr','properties.transaction.sale','Vente' UNION ALL SELECT 'nl','properties.transaction.sale','Verkoop'
  UNION ALL SELECT 'en','properties.transaction.rent','Rent' UNION ALL SELECT 'fr','properties.transaction.rent','Location' UNION ALL SELECT 'nl','properties.transaction.rent','Verhuur'
  UNION ALL SELECT 'en','properties.publication.published_badge','Published' UNION ALL SELECT 'fr','properties.publication.published_badge','Publié' UNION ALL SELECT 'nl','properties.publication.published_badge','Gepubliceerd'
  UNION ALL SELECT 'en','properties.publication.draft_badge','Draft' UNION ALL SELECT 'fr','properties.publication.draft_badge','Brouillon' UNION ALL SELECT 'nl','properties.publication.draft_badge','Concept'
  UNION ALL SELECT 'en','properties.publication.featured_badge','Featured' UNION ALL SELECT 'fr','properties.publication.featured_badge','À la une' UNION ALL SELECT 'nl','properties.publication.featured_badge','Uitgelicht'
  UNION ALL SELECT 'en','properties.publication.not_featured_badge','Not featured' UNION ALL SELECT 'fr','properties.publication.not_featured_badge','Non mis en avant' UNION ALL SELECT 'nl','properties.publication.not_featured_badge','Niet uitgelicht'
  UNION ALL SELECT 'en','properties.publication.not_published','Not published yet' UNION ALL SELECT 'fr','properties.publication.not_published','Pas encore publié' UNION ALL SELECT 'nl','properties.publication.not_published','Nog niet gepubliceerd'
  UNION ALL SELECT 'en','properties.completeness','Completeness' UNION ALL SELECT 'fr','properties.completeness','Complétude' UNION ALL SELECT 'nl','properties.completeness','Volledigheid'
  UNION ALL SELECT 'en','properties.completeness.details','Details' UNION ALL SELECT 'fr','properties.completeness.details','Détails' UNION ALL SELECT 'nl','properties.completeness.details','Details'
  UNION ALL SELECT 'en','properties.completeness.description','Description' UNION ALL SELECT 'fr','properties.completeness.description','Description' UNION ALL SELECT 'nl','properties.completeness.description','Beschrijving'
  UNION ALL SELECT 'en','properties.completeness.features','Features' UNION ALL SELECT 'fr','properties.completeness.features','Caractéristiques' UNION ALL SELECT 'nl','properties.completeness.features','Kenmerken'
  UNION ALL SELECT 'en','properties.completeness.photos','Photos' UNION ALL SELECT 'fr','properties.completeness.photos','Photos' UNION ALL SELECT 'nl','properties.completeness.photos','Foto’s'
  UNION ALL SELECT 'en','properties.completeness.documents','Documents' UNION ALL SELECT 'fr','properties.completeness.documents','Documents' UNION ALL SELECT 'nl','properties.completeness.documents','Documenten'
  UNION ALL SELECT 'en','properties.completeness.published','Published' UNION ALL SELECT 'fr','properties.completeness.published','Publié' UNION ALL SELECT 'nl','properties.completeness.published','Gepubliceerd'
  UNION ALL SELECT 'en','properties.fact.living','Living area' UNION ALL SELECT 'fr','properties.fact.living','Surface habitable' UNION ALL SELECT 'nl','properties.fact.living','Woonoppervlak'
  UNION ALL SELECT 'en','properties.fact.land','Land' UNION ALL SELECT 'fr','properties.fact.land','Terrain' UNION ALL SELECT 'nl','properties.fact.land','Terrein'
  UNION ALL SELECT 'en','properties.fact.bedrooms','Bedrooms' UNION ALL SELECT 'fr','properties.fact.bedrooms','Chambres' UNION ALL SELECT 'nl','properties.fact.bedrooms','Slaapkamers'
  UNION ALL SELECT 'en','properties.fact.bathrooms','Bathrooms' UNION ALL SELECT 'fr','properties.fact.bathrooms','Salles de bains' UNION ALL SELECT 'nl','properties.fact.bathrooms','Badkamers'
  UNION ALL SELECT 'en','properties.fact.location','Location' UNION ALL SELECT 'fr','properties.fact.location','Localisation' UNION ALL SELECT 'nl','properties.fact.location','Locatie'
  UNION ALL SELECT 'en','properties.new_property','New property' UNION ALL SELECT 'fr','properties.new_property','Nouveau bien' UNION ALL SELECT 'nl','properties.new_property','Nieuw object'
  UNION ALL SELECT 'en','properties.add_dimensions','Add dimensions' UNION ALL SELECT 'fr','properties.add_dimensions','Ajouter les dimensions' UNION ALL SELECT 'nl','properties.add_dimensions','Afmetingen toevoegen'
  UNION ALL SELECT 'en','properties.included','Included' UNION ALL SELECT 'fr','properties.included','Inclus' UNION ALL SELECT 'nl','properties.included','Inbegrepen'
  UNION ALL SELECT 'en','properties.js_map_unavailable','Map unavailable' UNION ALL SELECT 'fr','properties.js_map_unavailable','Carte indisponible' UNION ALL SELECT 'nl','properties.js_map_unavailable','Kaart niet beschikbaar'
  UNION ALL SELECT 'en','properties.js_enter_postcode_city','Enter a postcode or city' UNION ALL SELECT 'fr','properties.js_enter_postcode_city','Saisissez un code postal ou une ville' UNION ALL SELECT 'nl','properties.js_enter_postcode_city','Voer een postcode of plaats in'
  UNION ALL SELECT 'en','properties.js_valid_postcode','Enter a valid 5-digit postcode' UNION ALL SELECT 'fr','properties.js_valid_postcode','Saisissez un code postal valide à 5 chiffres' UNION ALL SELECT 'nl','properties.js_valid_postcode','Voer een geldige postcode van 5 cijfers in'
  UNION ALL SELECT 'en','properties.js_loading_area','Loading area…' UNION ALL SELECT 'fr','properties.js_loading_area','Zone en cours de chargement…' UNION ALL SELECT 'nl','properties.js_loading_area','Gebied wordt geladen…'
  UNION ALL SELECT 'en','properties.js_postcode_area','Postcode area' UNION ALL SELECT 'fr','properties.js_postcode_area','Zone du code postal' UNION ALL SELECT 'nl','properties.js_postcode_area','Postcodegebied'
  UNION ALL SELECT 'en','properties.js_postcode_unavailable','Postcode area unavailable' UNION ALL SELECT 'fr','properties.js_postcode_unavailable','Zone du code postal indisponible' UNION ALL SELECT 'nl','properties.js_postcode_unavailable','Postcodegebied niet beschikbaar'
  UNION ALL SELECT 'en','properties.js_city_unavailable','City unavailable' UNION ALL SELECT 'fr','properties.js_city_unavailable','Ville indisponible' UNION ALL SELECT 'nl','properties.js_city_unavailable','Plaats niet beschikbaar'
  UNION ALL SELECT 'en','properties.js_ten_km_area','10 km area around' UNION ALL SELECT 'fr','properties.js_ten_km_area','Zone de 10 km autour de' UNION ALL SELECT 'nl','properties.js_ten_km_area','Gebied van 10 km rond'
) x ON x.code=l.code
WHERE l.is_active=1
AND NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.k);

-- These strings are used as fallbacks by the editor JavaScript.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.k, x.v
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'common.yes' k,'Yes' v UNION ALL SELECT 'fr','common.yes','Oui' UNION ALL SELECT 'nl','common.yes','Ja'
  UNION ALL SELECT 'en','common.no','No' UNION ALL SELECT 'fr','common.no','Non' UNION ALL SELECT 'nl','common.no','Nee'
  UNION ALL SELECT 'en','properties.pool_dimensions_required' ,'Please enter pool length and width.' UNION ALL SELECT 'fr','properties.pool_dimensions_required','Veuillez saisir la longueur et la largeur de la piscine.' UNION ALL SELECT 'nl','properties.pool_dimensions_required','Voer de lengte en breedte van het zwembad in.'
  UNION ALL SELECT 'en','properties.add_pool_dimensions','Add pool dimensions' UNION ALL SELECT 'fr','properties.add_pool_dimensions','Ajouter les dimensions de la piscine' UNION ALL SELECT 'nl','properties.add_pool_dimensions','Afmetingen van het zwembad toevoegen'
) x ON x.code=l.code
WHERE l.is_active=1
AND NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.k);
