-- v40: complete i18n for database-driven property types and features.
-- Run once after deploying this ZIP. Idempotent: existing language/key rows are preserved.

SET NAMES utf8mb4;

-- Property types: support both the canonical plural namespace and the older singular namespace.
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
  UNION ALL SELECT 'en','property_types.other','Other' UNION ALL SELECT 'fr','property_types.other','Autre' UNION ALL SELECT 'nl','property_types.other','Overig'
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

-- Database-driven feature labels. The property editor reads features dynamically from the features table.
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

-- Common database category values; unknown future categories can use features.category.<category>.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.k, x.v
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'features.category.comfort' k,'Comfort' v UNION ALL SELECT 'fr','features.category.comfort','Confort' UNION ALL SELECT 'nl','features.category.comfort','Comfort'
  UNION ALL SELECT 'en','features.category.outdoor','Outdoor' UNION ALL SELECT 'fr','features.category.outdoor','Extérieur' UNION ALL SELECT 'nl','features.category.outdoor','Buiten'
  UNION ALL SELECT 'en','features.category.interior','Interior' UNION ALL SELECT 'fr','features.category.interior','Intérieur' UNION ALL SELECT 'nl','features.category.interior','Interieur'
  UNION ALL SELECT 'en','features.category.security','Security' UNION ALL SELECT 'fr','features.category.security','Sécurité' UNION ALL SELECT 'nl','features.category.security','Beveiliging'
  UNION ALL SELECT 'en','features.category.other','Other' UNION ALL SELECT 'fr','features.category.other','Autre' UNION ALL SELECT 'nl','features.category.other','Overig'
) x ON x.code=l.code
WHERE l.is_active=1
  AND NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.k);

-- Small UI strings used by the database-driven feature tiles.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.k, x.v
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'properties.included' k,'Included' v UNION ALL SELECT 'fr','properties.included','Inclus' UNION ALL SELECT 'nl','properties.included','Inbegrepen'
  UNION ALL SELECT 'en','properties.add_dimensions' ,'Add dimensions' UNION ALL SELECT 'fr','properties.add_dimensions','Ajouter les dimensions' UNION ALL SELECT 'nl','properties.add_dimensions','Afmetingen toevoegen'
) x ON x.code=l.code
WHERE l.is_active=1
  AND NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.k);
