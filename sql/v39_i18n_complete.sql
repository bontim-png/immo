-- Immo i18n additions: complete the visible admin/property/auth UI.
-- Safe to run repeatedly. Existing translations are preserved; only missing key/language pairs are inserted.

SET NAMES utf8mb4;

INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.translation_key, x.translation_value
FROM i18n_languages l
JOIN (
  SELECT 'en' AS code, 'common.platform' AS translation_key, 'Platform' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'common.platform' AS translation_key, 'Plateforme' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'common.platform' AS translation_key, 'Platform' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'common.danger_zone' AS translation_key, 'DANGER ZONE' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'common.danger_zone' AS translation_key, 'ZONE DANGEREUSE' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'common.danger_zone' AS translation_key, 'GEVARENZONE' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'common.clear' AS translation_key, 'Clear' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'common.clear' AS translation_key, 'Effacer' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'common.clear' AS translation_key, 'Wissen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'employees.kicker' AS translation_key, 'PLATFORM' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'employees.kicker' AS translation_key, 'PLATEFORME' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'employees.kicker' AS translation_key, 'PLATFORM' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'employees.directory_kicker' AS translation_key, 'DIRECTORY' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'employees.directory_kicker' AS translation_key, 'ANNUAIRE' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'employees.directory_kicker' AS translation_key, 'DIRECTORY' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'employees.account_kicker' AS translation_key, 'ACCOUNT' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'employees.account_kicker' AS translation_key, 'COMPTE' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'employees.account_kicker' AS translation_key, 'ACCOUNT' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'employees.access_kicker' AS translation_key, 'ACCESS' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'employees.access_kicker' AS translation_key, 'ACCÈS' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'employees.access_kicker' AS translation_key, 'TOEGANG' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'employees.status_kicker' AS translation_key, 'STATUS' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'employees.status_kicker' AS translation_key, 'STATUT' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'employees.status_kicker' AS translation_key, 'STATUS' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'employees.platform' AS translation_key, 'Platform' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'employees.platform' AS translation_key, 'Plateforme' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'employees.platform' AS translation_key, 'Platform' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'roles.office_employee' AS translation_key, 'Office Employee' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'roles.office_employee' AS translation_key, 'Employé d’agence' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'roles.office_employee' AS translation_key, 'Kantoormedewerker' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'offices.office_kicker' AS translation_key, 'OFFICE' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'offices.office_kicker' AS translation_key, 'AGENCE' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'offices.office_kicker' AS translation_key, 'KANTOOR' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'offices.location_kicker' AS translation_key, 'LOCATION' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'offices.location_kicker' AS translation_key, 'LOCALISATION' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'offices.location_kicker' AS translation_key, 'LOCATIE' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'offices.settings_kicker' AS translation_key, 'SETTINGS' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'offices.settings_kicker' AS translation_key, 'PARAMÈTRES' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'offices.settings_kicker' AS translation_key, 'INSTELLINGEN' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'offices.status_kicker' AS translation_key, 'OFFICE STATUS' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'offices.status_kicker' AS translation_key, 'STATUT DE L’AGENCE' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'offices.status_kicker' AS translation_key, 'KANTOORSTATUS' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.house' AS translation_key, 'House' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.house' AS translation_key, 'Maison' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.house' AS translation_key, 'Huis' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.villa' AS translation_key, 'Villa' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.villa' AS translation_key, 'Villa' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.villa' AS translation_key, 'Villa' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.apartment' AS translation_key, 'Apartment' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.apartment' AS translation_key, 'Appartement' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.apartment' AS translation_key, 'Appartement' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.land' AS translation_key, 'Land' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.land' AS translation_key, 'Terrain' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.land' AS translation_key, 'Grond' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.commercial' AS translation_key, 'Commercial' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.commercial' AS translation_key, 'Local commercial' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.commercial' AS translation_key, 'Commercieel' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.building' AS translation_key, 'Building' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.building' AS translation_key, 'Immeuble' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.building' AS translation_key, 'Gebouw' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.farm' AS translation_key, 'Farm' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.farm' AS translation_key, 'Ferme' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.farm' AS translation_key, 'Boerderij' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'property_type.other' AS translation_key, 'Other' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'property_type.other' AS translation_key, 'Autre' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'property_type.other' AS translation_key, 'Overig' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.transactions' AS translation_key, 'Transactions' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.transactions' AS translation_key, 'Transactions' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.transactions' AS translation_key, 'Transacties' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.published' AS translation_key, 'Published' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.published' AS translation_key, 'Publié' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.published' AS translation_key, 'Gepubliceerd' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.add_pool_dimensions' AS translation_key, 'Add pool dimensions' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.add_pool_dimensions' AS translation_key, 'Ajouter les dimensions de la piscine' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.add_pool_dimensions' AS translation_key, 'Afmetingen zwembad toevoegen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.add_dimensions' AS translation_key, 'Add dimensions' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.add_dimensions' AS translation_key, 'Ajouter les dimensions' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.add_dimensions' AS translation_key, 'Afmetingen toevoegen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_save_failed' AS translation_key, 'Save failed' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_save_failed' AS translation_key, 'Échec de l’enregistrement' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_save_failed' AS translation_key, 'Opslaan mislukt' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_retry' AS translation_key, 'Retry' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_retry' AS translation_key, 'Réessayer' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_retry' AS translation_key, 'Opnieuw proberen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_unsaved' AS translation_key, 'Unsaved changes' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_unsaved' AS translation_key, 'Modifications non enregistrées' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_unsaved' AS translation_key, 'Niet-opgeslagen wijzigingen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_saved_now' AS translation_key, 'Saved just now' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_saved_now' AS translation_key, 'Enregistré à l’instant' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_saved_now' AS translation_key, 'Zojuist opgeslagen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_saving' AS translation_key, 'Saving…' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_saving' AS translation_key, 'Enregistrement…' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_saving' AS translation_key, 'Opslaan…' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_enter_postcode_city' AS translation_key, 'Enter a postcode or city' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_enter_postcode_city' AS translation_key, 'Saisissez un code postal ou une ville' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_enter_postcode_city' AS translation_key, 'Voer een postcode of plaats in' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_valid_postcode' AS translation_key, 'Enter a valid 5-digit postcode' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_valid_postcode' AS translation_key, 'Saisissez un code postal valide à 5 chiffres' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_valid_postcode' AS translation_key, 'Voer een geldige postcode van 5 cijfers in' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_loading_area' AS translation_key, 'Loading area…' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_loading_area' AS translation_key, 'Chargement de la zone…' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_loading_area' AS translation_key, 'Gebied laden…' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_postcode_area' AS translation_key, 'Postcode area' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_postcode_area' AS translation_key, 'Zone du code postal' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_postcode_area' AS translation_key, 'Postcodegebied' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_approximate_area' AS translation_key, 'Approximate area' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_approximate_area' AS translation_key, 'Zone approximative' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_approximate_area' AS translation_key, 'Benaderd gebied' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_ten_km_area' AS translation_key, '10 km area around' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_ten_km_area' AS translation_key, 'Zone de 10 km autour de' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_ten_km_area' AS translation_key, 'Gebied van 10 km rond' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_area_unavailable' AS translation_key, 'Area unavailable' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_area_unavailable' AS translation_key, 'Zone indisponible' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_area_unavailable' AS translation_key, 'Gebied niet beschikbaar' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_postcode_unavailable' AS translation_key, 'Postcode area unavailable' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_postcode_unavailable' AS translation_key, 'Zone du code postal indisponible' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_postcode_unavailable' AS translation_key, 'Postcodegebied niet beschikbaar' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.js_city_unavailable' AS translation_key, 'City unavailable' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.js_city_unavailable' AS translation_key, 'Ville indisponible' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.js_city_unavailable' AS translation_key, 'Plaats niet beschikbaar' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'properties.pool_dimensions_required' AS translation_key, 'Please enter pool length and width.' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'properties.pool_dimensions_required' AS translation_key, 'Saisissez la longueur et la largeur de la piscine.' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'properties.pool_dimensions_required' AS translation_key, 'Voer de lengte en breedte van het zwembad in.' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.login_title' AS translation_key, 'Login' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.login_title' AS translation_key, 'Connexion' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.login_title' AS translation_key, 'Inloggen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.platform_desc' AS translation_key, 'Real estate management platform' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.platform_desc' AS translation_key, 'Plateforme de gestion immobilière' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.platform_desc' AS translation_key, 'Vastgoedbeheerplatform' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.welcome' AS translation_key, 'Welcome back' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.welcome' AS translation_key, 'Bon retour' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.welcome' AS translation_key, 'Welkom terug' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.signin_intro' AS translation_key, 'Sign in to continue to your workspace.' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.signin_intro' AS translation_key, 'Connectez-vous pour accéder à votre espace de travail.' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.signin_intro' AS translation_key, 'Log in om verder te gaan naar je werkruimte.' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.password' AS translation_key, 'Password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.password' AS translation_key, 'Mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.password' AS translation_key, 'Wachtwoord' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.show' AS translation_key, 'Show' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.show' AS translation_key, 'Afficher' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.show' AS translation_key, 'Tonen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.hide' AS translation_key, 'Hide' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.hide' AS translation_key, 'Masquer' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.hide' AS translation_key, 'Verbergen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.sign_in' AS translation_key, 'Sign in' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.sign_in' AS translation_key, 'Se connecter' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.sign_in' AS translation_key, 'Inloggen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.forgot' AS translation_key, 'Forgot your password?' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.forgot' AS translation_key, 'Mot de passe oublié ?' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.forgot' AS translation_key, 'Wachtwoord vergeten?' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.secure_access' AS translation_key, 'Secure access · Immo' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.secure_access' AS translation_key, 'Accès sécurisé · Immo' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.secure_access' AS translation_key, 'Veilige toegang · Immo' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.reset_title' AS translation_key, 'Reset password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.reset_title' AS translation_key, 'Réinitialiser le mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.reset_title' AS translation_key, 'Wachtwoord resetten' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.forgot_title' AS translation_key, 'Forgot your password?' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.forgot_title' AS translation_key, 'Mot de passe oublié ?' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.forgot_title' AS translation_key, 'Wachtwoord vergeten?' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.forgot_intro' AS translation_key, 'Enter your email address and we will send you a secure link to create a new password.' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.forgot_intro' AS translation_key, 'Saisissez votre adresse e-mail et nous vous enverrons un lien sécurisé pour créer un nouveau mot de passe.' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.forgot_intro' AS translation_key, 'Voer je e-mailadres in en we sturen je een beveiligde link om een nieuw wachtwoord aan te maken.' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.send_reset' AS translation_key, 'Send reset link' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.send_reset' AS translation_key, 'Envoyer le lien de réinitialisation' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.send_reset' AS translation_key, 'Resetlink versturen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.back_login' AS translation_key, 'Back to login' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.back_login' AS translation_key, 'Retour à la connexion' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.back_login' AS translation_key, 'Terug naar inloggen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.secure_recovery' AS translation_key, 'Secure password recovery · Immo' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.secure_recovery' AS translation_key, 'Récupération sécurisée du mot de passe · Immo' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.secure_recovery' AS translation_key, 'Veilige wachtwoordherstel · Immo' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.choose_password_title' AS translation_key, 'Choose new password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.choose_password_title' AS translation_key, 'Choisir un nouveau mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.choose_password_title' AS translation_key, 'Nieuw wachtwoord kiezen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.create_password' AS translation_key, 'Create a new password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.create_password' AS translation_key, 'Créer un nouveau mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.create_password' AS translation_key, 'Nieuw wachtwoord aanmaken' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.create_password_intro' AS translation_key, 'Choose a new password for your Immo account.' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.create_password_intro' AS translation_key, 'Choisissez un nouveau mot de passe pour votre compte Immo.' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.create_password_intro' AS translation_key, 'Kies een nieuw wachtwoord voor je Immo-account.' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.new_password' AS translation_key, 'New password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.new_password' AS translation_key, 'Nouveau mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.new_password' AS translation_key, 'Nieuw wachtwoord' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.confirm_password' AS translation_key, 'Confirm new password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.confirm_password' AS translation_key, 'Confirmer le nouveau mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.confirm_password' AS translation_key, 'Nieuw wachtwoord bevestigen' AS translation_value
  UNION ALL
  SELECT 'en' AS code, 'auth.set_password' AS translation_key, 'Set new password' AS translation_value
  UNION ALL
  SELECT 'fr' AS code, 'auth.set_password' AS translation_key, 'Définir le nouveau mot de passe' AS translation_value
  UNION ALL
  SELECT 'nl' AS code, 'auth.set_password' AS translation_key, 'Nieuw wachtwoord instellen' AS translation_value
) x ON x.code = l.code
WHERE NOT EXISTS (
  SELECT 1 FROM i18n_translations t
  WHERE t.language_id = l.id AND t.translation_key = x.translation_key
);

-- Compatibility keys from earlier i18n work packages.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.translation_key, x.translation_value
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'features.heating.title' translation_key,'Heating' translation_value UNION ALL
  SELECT 'fr','features.heating.title','Chauffage' UNION ALL
  SELECT 'nl','features.heating.title','Verwarming' UNION ALL
  SELECT 'en','features.heating.method','Heating method' UNION ALL
  SELECT 'fr','features.heating.method','Type de chauffage' UNION ALL
  SELECT 'nl','features.heating.method','Verwarmingsmethode' UNION ALL
  SELECT 'en','features.heating.select','Select heating' UNION ALL
  SELECT 'fr','features.heating.select','Choisir le chauffage' UNION ALL
  SELECT 'nl','features.heating.select','Kies verwarming' UNION ALL
  SELECT 'en','features.underfloor_heating','Underfloor heating' UNION ALL
  SELECT 'fr','features.underfloor_heating','Chauffage au sol' UNION ALL
  SELECT 'nl','features.underfloor_heating','Vloerverwarming' UNION ALL
  SELECT 'en','features.comfort','Comfort' UNION ALL
  SELECT 'fr','features.comfort','Confort' UNION ALL
  SELECT 'nl','features.comfort','Comfort' UNION ALL
  SELECT 'en','features.air_conditioning','Air conditioning' UNION ALL
  SELECT 'fr','features.air_conditioning','Climatisation' UNION ALL
  SELECT 'nl','features.air_conditioning','Airconditioning' UNION ALL
  SELECT 'en','features.select_air_conditioning','Select air conditioning' UNION ALL
  SELECT 'fr','features.select_air_conditioning','Choisir la climatisation' UNION ALL
  SELECT 'nl','features.select_air_conditioning','Kies airconditioning' UNION ALL
  SELECT 'en','features.air_conditioning_present','Air conditioning present' UNION ALL
  SELECT 'fr','features.air_conditioning_present','Climatisation présente' UNION ALL
  SELECT 'nl','features.air_conditioning_present','Airconditioning aanwezig' UNION ALL
  SELECT 'en','common.success','Success' UNION ALL
  SELECT 'fr','common.success','Succès' UNION ALL
  SELECT 'nl','common.success','Gelukt' UNION ALL
  SELECT 'en','common.error','Something went wrong' UNION ALL
  SELECT 'fr','common.error','Une erreur est survenue' UNION ALL
  SELECT 'nl','common.error','Er is iets misgegaan' UNION ALL
  SELECT 'en','common.cancel','Cancel' UNION ALL
  SELECT 'fr','common.cancel','Annuler' UNION ALL
  SELECT 'nl','common.cancel','Annuleren' UNION ALL
  SELECT 'en','employees.no_offices_title','No active office available' UNION ALL
  SELECT 'fr','employees.no_offices_title','Aucune agence active disponible' UNION ALL
  SELECT 'nl','employees.no_offices_title','Geen actief kantoor beschikbaar' UNION ALL
  SELECT 'en','employees.no_offices','Create or activate an office before adding an employee.' UNION ALL
  SELECT 'fr','employees.no_offices','Créez ou activez une agence avant d’ajouter un employé.' UNION ALL
  SELECT 'nl','employees.no_offices','Maak eerst een kantoor aan of activeer een kantoor voordat je een medewerker toevoegt.' UNION ALL
  SELECT 'en','properties.delete_title','Delete property?' UNION ALL
  SELECT 'fr','properties.delete_title','Supprimer le bien ?' UNION ALL
  SELECT 'nl','properties.delete_title','Object verwijderen?' UNION ALL
  SELECT 'en','properties.delete_warning','This property will be removed from the active property list. This action cannot be undone from this screen.' UNION ALL
  SELECT 'fr','properties.delete_warning','Ce bien sera retiré de la liste active. Cette action ne peut pas être annulée depuis cet écran.' UNION ALL
  SELECT 'nl','properties.delete_warning','Dit object wordt uit de actieve objectlijst verwijderd. Deze actie kan vanuit dit scherm niet ongedaan worden gemaakt.' UNION ALL
  SELECT 'en','properties.delete_access_error','You do not have permission to delete this property.' UNION ALL
  SELECT 'fr','properties.delete_access_error','Vous n’avez pas l’autorisation de supprimer ce bien.' UNION ALL
  SELECT 'nl','properties.delete_access_error','Je hebt geen rechten om dit object te verwijderen.' UNION ALL
  SELECT 'en','properties.back_to_properties','Back to properties' UNION ALL
  SELECT 'fr','properties.back_to_properties','Retour aux biens' UNION ALL
  SELECT 'nl','properties.back_to_properties','Terug naar objecten' UNION ALL
  SELECT 'en','properties.delete_error','The property could not be deleted. Please check your permissions and try again.' UNION ALL
  SELECT 'fr','properties.delete_error','Le bien n’a pas pu être supprimé. Vérifiez vos droits et réessayez.' UNION ALL
  SELECT 'nl','properties.delete_error','Het object kon niet worden verwijderd. Controleer je rechten en probeer het opnieuw.'
) x ON x.code=l.code
WHERE NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.translation_key);


-- Property description source note.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id, x.translation_key, x.translation_value
FROM i18n_languages l
JOIN (
  SELECT 'en' code,'properties.french_source' translation_key,'French source' translation_value UNION ALL
  SELECT 'fr','properties.french_source','Source française' UNION ALL
  SELECT 'nl','properties.french_source','Franse bron' UNION ALL
  SELECT 'en','properties.french_source_note' translation_key,'Content is stored in French; translations can be generated later during publication.' translation_value UNION ALL
  SELECT 'fr','properties.french_source_note','Le contenu est stocké en français ; les traductions pourront être générées ultérieurement lors de la publication.' UNION ALL
  SELECT 'nl','properties.french_source_note','De inhoud wordt in het Frans opgeslagen; vertalingen kunnen later bij publicatie worden gegenereerd.'
) x ON x.code=l.code
WHERE NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.translation_key);

-- Additional property-editor labels and tabs.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id,x.translation_key,x.translation_value FROM i18n_languages l JOIN (
  SELECT 'en' code,'properties.all_changes_saved' translation_key,'All changes saved' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.all_changes_saved' translation_key,'Toutes les modifications sont enregistrées' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.all_changes_saved' translation_key,'Alle wijzigingen zijn opgeslagen' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_general' translation_key,'General' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_general' translation_key,'Général' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_general' translation_key,'Algemeen' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_description' translation_key,'Description' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_description' translation_key,'Description' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_description' translation_key,'Beschrijving' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_features' translation_key,'Features' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_features' translation_key,'Caractéristiques' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_features' translation_key,'Kenmerken' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_photos' translation_key,'Photos' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_photos' translation_key,'Photos' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_photos' translation_key,'Foto’s' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_videos' translation_key,'Videos' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_videos' translation_key,'Vidéos' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_videos' translation_key,'Video’s' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_documents' translation_key,'Documents' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_documents' translation_key,'Documents' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_documents' translation_key,'Documenten' translation_value
  UNION ALL
  SELECT 'en' code,'properties.tab_location' translation_key,'Location' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.tab_location' translation_key,'Localisation' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.tab_location' translation_key,'Locatie' translation_value
  UNION ALL
  SELECT 'en' code,'properties.property_kicker' translation_key,'Property' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.property_kicker' translation_key,'Bien' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.property_kicker' translation_key,'Object' translation_value
  UNION ALL
  SELECT 'en' code,'properties.property_details' translation_key,'Property details' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.property_details' translation_key,'Détails du bien' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.property_details' translation_key,'Objectgegevens' translation_value
  UNION ALL
  SELECT 'en' code,'properties.value_kicker' translation_key,'Value' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.value_kicker' translation_key,'Valeur' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.value_kicker' translation_key,'Waarde' translation_value
  UNION ALL
  SELECT 'en' code,'common.close' translation_key,'Close' translation_value
  UNION ALL
  SELECT 'fr' code,'common.close' translation_key,'Fermer' translation_value
  UNION ALL
  SELECT 'nl' code,'common.close' translation_key,'Sluiten' translation_value
  UNION ALL
  SELECT 'en' code,'common.apply' translation_key,'Apply' translation_value
  UNION ALL
  SELECT 'fr' code,'common.apply' translation_key,'Appliquer' translation_value
  UNION ALL
  SELECT 'nl' code,'common.apply' translation_key,'Toepassen' translation_value
  UNION ALL
  SELECT 'en' code,'properties.pool_dimensions_help' translation_key,'Enter the pool dimensions. Length and width are required; depth is optional. After saving, hover the Pool tile to see them at a glance.' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.pool_dimensions_help' translation_key,'Saisissez les dimensions de la piscine. La longueur et la largeur sont obligatoires ; la profondeur est facultative. Après l’enregistrement, survolez la tuile Piscine pour les voir rapidement.' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.pool_dimensions_help' translation_key,'Voer de afmetingen van het zwembad in. Lengte en breedte zijn verplicht; diepte is optioneel. Na het opslaan kun je over de zwembadtegel bewegen om ze direct te zien.' translation_value
  UNION ALL
  SELECT 'en' code,'common.length' translation_key,'Length' translation_value
  UNION ALL
  SELECT 'fr' code,'common.length' translation_key,'Longueur' translation_value
  UNION ALL
  SELECT 'nl' code,'common.length' translation_key,'Lengte' translation_value
  UNION ALL
  SELECT 'en' code,'common.width' translation_key,'Width' translation_value
  UNION ALL
  SELECT 'fr' code,'common.width' translation_key,'Largeur' translation_value
  UNION ALL
  SELECT 'nl' code,'common.width' translation_key,'Breedte' translation_value
  UNION ALL
  SELECT 'en' code,'common.depth' translation_key,'Depth' translation_value
  UNION ALL
  SELECT 'fr' code,'common.depth' translation_key,'Profondeur' translation_value
  UNION ALL
  SELECT 'nl' code,'common.depth' translation_key,'Diepte' translation_value
  UNION ALL
  SELECT 'en' code,'properties.save_dimensions' translation_key,'Save dimensions' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.save_dimensions' translation_key,'Enregistrer les dimensions' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.save_dimensions' translation_key,'Afmetingen opslaan' translation_value
  UNION ALL
  SELECT 'en' code,'properties.loading' translation_key,'Loading…' translation_value
  UNION ALL
  SELECT 'fr' code,'properties.loading' translation_key,'Chargement…' translation_value
  UNION ALL
  SELECT 'nl' code,'properties.loading' translation_key,'Laden…' translation_value
) x ON x.code=l.code WHERE NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.translation_key);

-- Common yes/no labels.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id,x.translation_key,x.translation_value FROM i18n_languages l JOIN (
  SELECT 'en' code,'common.yes' translation_key,'Yes' translation_value UNION ALL
  SELECT 'fr','common.yes','Oui' UNION ALL
  SELECT 'nl','common.yes','Ja' UNION ALL
  SELECT 'en','common.no','No' UNION ALL
  SELECT 'fr','common.no','Non' UNION ALL
  SELECT 'nl','common.no','Nee'
) x ON x.code=l.code WHERE NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.translation_key);

-- Alias keys used by older property_type rows.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id,x.translation_key,x.translation_value FROM i18n_languages l JOIN (
  SELECT 'en' code,'property_types.house' translation_key,'House' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.house' translation_key,'Maison' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.house' translation_key,'Huis' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.villa' translation_key,'Villa' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.villa' translation_key,'Villa' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.villa' translation_key,'Villa' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.apartment' translation_key,'Apartment' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.apartment' translation_key,'Appartement' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.apartment' translation_key,'Appartement' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.land' translation_key,'Land' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.land' translation_key,'Terrain' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.land' translation_key,'Grond' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.commercial' translation_key,'Commercial' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.commercial' translation_key,'Local commercial' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.commercial' translation_key,'Commercieel' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.building' translation_key,'Building' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.building' translation_key,'Immeuble' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.building' translation_key,'Gebouw' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.farm' translation_key,'Farm' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.farm' translation_key,'Ferme' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.farm' translation_key,'Boerderij' translation_value
  UNION ALL
  SELECT 'en' code,'property_types.other' translation_key,'Other' translation_value
  UNION ALL
  SELECT 'fr' code,'property_types.other' translation_key,'Autre' translation_value
  UNION ALL
  SELECT 'nl' code,'property_types.other' translation_key,'Overig' translation_value
) x ON x.code=l.code WHERE NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.translation_key);

-- Complete employee and office page translations.
INSERT INTO i18n_translations (language_id, translation_key, translation_value)
SELECT l.id,x.translation_key,x.translation_value FROM i18n_languages l JOIN (
  SELECT 'en' code,'common.actions' translation_key,'Actions' translation_value
  UNION ALL
  SELECT 'fr' code,'common.actions' translation_key,'Actions' translation_value
  UNION ALL
  SELECT 'nl' code,'common.actions' translation_key,'Acties' translation_value
  UNION ALL
  SELECT 'en' code,'common.name' translation_key,'Name' translation_value
  UNION ALL
  SELECT 'fr' code,'common.name' translation_key,'Nom' translation_value
  UNION ALL
  SELECT 'nl' code,'common.name' translation_key,'Naam' translation_value
  UNION ALL
  SELECT 'en' code,'common.email' translation_key,'Email' translation_value
  UNION ALL
  SELECT 'fr' code,'common.email' translation_key,'E-mail' translation_value
  UNION ALL
  SELECT 'nl' code,'common.email' translation_key,'E-mail' translation_value
  UNION ALL
  SELECT 'en' code,'common.role' translation_key,'Role' translation_value
  UNION ALL
  SELECT 'fr' code,'common.role' translation_key,'Rôle' translation_value
  UNION ALL
  SELECT 'nl' code,'common.role' translation_key,'Rol' translation_value
  UNION ALL
  SELECT 'en' code,'common.office' translation_key,'Office' translation_value
  UNION ALL
  SELECT 'fr' code,'common.office' translation_key,'Agence' translation_value
  UNION ALL
  SELECT 'nl' code,'common.office' translation_key,'Kantoor' translation_value
  UNION ALL
  SELECT 'en' code,'common.status' translation_key,'Status' translation_value
  UNION ALL
  SELECT 'fr' code,'common.status' translation_key,'Statut' translation_value
  UNION ALL
  SELECT 'nl' code,'common.status' translation_key,'Status' translation_value
  UNION ALL
  SELECT 'en' code,'common.location' translation_key,'Location' translation_value
  UNION ALL
  SELECT 'fr' code,'common.location' translation_key,'Localisation' translation_value
  UNION ALL
  SELECT 'nl' code,'common.location' translation_key,'Locatie' translation_value
  UNION ALL
  SELECT 'en' code,'common.employees' translation_key,'Employees' translation_value
  UNION ALL
  SELECT 'fr' code,'common.employees' translation_key,'Employés' translation_value
  UNION ALL
  SELECT 'nl' code,'common.employees' translation_key,'Medewerkers' translation_value
  UNION ALL
  SELECT 'en' code,'common.properties' translation_key,'Properties' translation_value
  UNION ALL
  SELECT 'fr' code,'common.properties' translation_key,'Biens' translation_value
  UNION ALL
  SELECT 'nl' code,'common.properties' translation_key,'Objecten' translation_value
  UNION ALL
  SELECT 'en' code,'common.edit' translation_key,'Edit' translation_value
  UNION ALL
  SELECT 'fr' code,'common.edit' translation_key,'Modifier' translation_value
  UNION ALL
  SELECT 'nl' code,'common.edit' translation_key,'Bewerken' translation_value
  UNION ALL
  SELECT 'en' code,'common.delete' translation_key,'Delete' translation_value
  UNION ALL
  SELECT 'fr' code,'common.delete' translation_key,'Supprimer' translation_value
  UNION ALL
  SELECT 'nl' code,'common.delete' translation_key,'Verwijderen' translation_value
  UNION ALL
  SELECT 'en' code,'common.active' translation_key,'Active' translation_value
  UNION ALL
  SELECT 'fr' code,'common.active' translation_key,'Actif' translation_value
  UNION ALL
  SELECT 'nl' code,'common.active' translation_key,'Actief' translation_value
  UNION ALL
  SELECT 'en' code,'common.inactive' translation_key,'Inactive' translation_value
  UNION ALL
  SELECT 'fr' code,'common.inactive' translation_key,'Inactif' translation_value
  UNION ALL
  SELECT 'nl' code,'common.inactive' translation_key,'Inactief' translation_value
  UNION ALL
  SELECT 'en' code,'common.cancel' translation_key,'Cancel' translation_value
  UNION ALL
  SELECT 'fr' code,'common.cancel' translation_key,'Annuler' translation_value
  UNION ALL
  SELECT 'nl' code,'common.cancel' translation_key,'Annuleren' translation_value
  UNION ALL
  SELECT 'en' code,'common.save_changes' translation_key,'Save changes' translation_value
  UNION ALL
  SELECT 'fr' code,'common.save_changes' translation_key,'Enregistrer les modifications' translation_value
  UNION ALL
  SELECT 'nl' code,'common.save_changes' translation_key,'Wijzigingen opslaan' translation_value
  UNION ALL
  SELECT 'en' code,'common.create' translation_key,'Create' translation_value
  UNION ALL
  SELECT 'fr' code,'common.create' translation_key,'Créer' translation_value
  UNION ALL
  SELECT 'nl' code,'common.create' translation_key,'Aanmaken' translation_value
  UNION ALL
  SELECT 'en' code,'common.dashboard' translation_key,'Dashboard' translation_value
  UNION ALL
  SELECT 'fr' code,'common.dashboard' translation_key,'Tableau de bord' translation_value
  UNION ALL
  SELECT 'nl' code,'common.dashboard' translation_key,'Dashboard' translation_value
  UNION ALL
  SELECT 'en' code,'common.sign_out' translation_key,'Sign out' translation_value
  UNION ALL
  SELECT 'fr' code,'common.sign_out' translation_key,'Déconnexion' translation_value
  UNION ALL
  SELECT 'nl' code,'common.sign_out' translation_key,'Uitloggen' translation_value
  UNION ALL
  SELECT 'en' code,'common.workspace' translation_key,'WORKSPACE' translation_value
  UNION ALL
  SELECT 'fr' code,'common.workspace' translation_key,'ESPACE DE TRAVAIL' translation_value
  UNION ALL
  SELECT 'nl' code,'common.workspace' translation_key,'WERKRUIMTE' translation_value
  UNION ALL
  SELECT 'en' code,'common.navigation' translation_key,'NAVIGATION' translation_value
  UNION ALL
  SELECT 'fr' code,'common.navigation' translation_key,'NAVIGATION' translation_value
  UNION ALL
  SELECT 'nl' code,'common.navigation' translation_key,'NAVIGATIE' translation_value
  UNION ALL
  SELECT 'en' code,'common.first_name' translation_key,'First name' translation_value
  UNION ALL
  SELECT 'fr' code,'common.first_name' translation_key,'Prénom' translation_value
  UNION ALL
  SELECT 'nl' code,'common.first_name' translation_key,'Voornaam' translation_value
  UNION ALL
  SELECT 'en' code,'common.last_name' translation_key,'Last name' translation_value
  UNION ALL
  SELECT 'fr' code,'common.last_name' translation_key,'Nom' translation_value
  UNION ALL
  SELECT 'nl' code,'common.last_name' translation_key,'Achternaam' translation_value
  UNION ALL
  SELECT 'en' code,'common.phone' translation_key,'Phone' translation_value
  UNION ALL
  SELECT 'fr' code,'common.phone' translation_key,'Téléphone' translation_value
  UNION ALL
  SELECT 'nl' code,'common.phone' translation_key,'Telefoon' translation_value
  UNION ALL
  SELECT 'en' code,'common.filter' translation_key,'Filter' translation_value
  UNION ALL
  SELECT 'fr' code,'common.filter' translation_key,'Filtrer' translation_value
  UNION ALL
  SELECT 'nl' code,'common.filter' translation_key,'Filter' translation_value
  UNION ALL
  SELECT 'en' code,'employees.manage_title' translation_key,'Manage your team' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.manage_title' translation_key,'Gérez votre équipe' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.manage_title' translation_key,'Beheer je team' translation_value
  UNION ALL
  SELECT 'en' code,'employees.subtitle' translation_key,'Manage users, roles and access for your offices.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.subtitle' translation_key,'Gérez les utilisateurs, rôles et accès de vos agences.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.subtitle' translation_key,'Beheer gebruikers, rollen en toegang voor je kantoren.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.create' translation_key,'New employee' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.create' translation_key,'Nouvel employé' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.create' translation_key,'Nieuwe medewerker' translation_value
  UNION ALL
  SELECT 'en' code,'employees.total' translation_key,'Total employees' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.total' translation_key,'Total des employés' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.total' translation_key,'Totaal medewerkers' translation_value
  UNION ALL
  SELECT 'en' code,'employees.all_offices' translation_key,'Across all offices' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.all_offices' translation_key,'Dans toutes les agences' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.all_offices' translation_key,'Over alle kantoren' translation_value
  UNION ALL
  SELECT 'en' code,'employees.office_team' translation_key,'In your office' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.office_team' translation_key,'Dans votre agence' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.office_team' translation_key,'In jouw kantoor' translation_value
  UNION ALL
  SELECT 'en' code,'employees.active_accounts' translation_key,'Active user accounts' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.active_accounts' translation_key,'Comptes utilisateurs actifs' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.active_accounts' translation_key,'Actieve gebruikersaccounts' translation_value
  UNION ALL
  SELECT 'en' code,'employees.roles' translation_key,'Roles' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.roles' translation_key,'Rôles' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.roles' translation_key,'Rollen' translation_value
  UNION ALL
  SELECT 'en' code,'employees.roles_used' translation_key,'Roles in use' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.roles_used' translation_key,'Rôles utilisés' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.roles_used' translation_key,'Gebruikte rollen' translation_value
  UNION ALL
  SELECT 'en' code,'employees.workspace' translation_key,'Workspace' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.workspace' translation_key,'Espace de travail' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.workspace' translation_key,'Werkruimte' translation_value
  UNION ALL
  SELECT 'en' code,'employees.platform_scope' translation_key,'Platform scope' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.platform_scope' translation_key,'Périmètre de la plateforme' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.platform_scope' translation_key,'Platformbereik' translation_value
  UNION ALL
  SELECT 'en' code,'employees.office_scope_label' translation_key,'Office workspace' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.office_scope_label' translation_key,'Espace de travail de l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.office_scope_label' translation_key,'Kantoorwerkruimte' translation_value
  UNION ALL
  SELECT 'en' code,'employees.directory' translation_key,'Employees' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.directory' translation_key,'Employés' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.directory' translation_key,'Medewerkers' translation_value
  UNION ALL
  SELECT 'en' code,'employees.empty' translation_key,'No employees yet' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.empty' translation_key,'Aucun employé pour le moment' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.empty' translation_key,'Nog geen medewerkers' translation_value
  UNION ALL
  SELECT 'en' code,'employees.empty_hint' translation_key,'Create your first employee to get started.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.empty_hint' translation_key,'Créez votre premier employé pour commencer.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.empty_hint' translation_key,'Maak je eerste medewerker aan om te beginnen.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.account_details' translation_key,'Account details' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.account_details' translation_key,'Détails du compte' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.account_details' translation_key,'Accountgegevens' translation_value
  UNION ALL
  SELECT 'en' code,'employees.access_settings' translation_key,'Access settings' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.access_settings' translation_key,'Paramètres d’accès' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.access_settings' translation_key,'Toegangsinstellingen' translation_value
  UNION ALL
  SELECT 'en' code,'employees.account_status' translation_key,'Account status' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.account_status' translation_key,'Statut du compte' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.account_status' translation_key,'Accountstatus' translation_value
  UNION ALL
  SELECT 'en' code,'employees.status_help' translation_key,'Inactive users cannot sign in to Prrepl.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.status_help' translation_key,'Les utilisateurs inactifs ne peuvent pas se connecter à Prrepl.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.status_help' translation_key,'Inactieve gebruikers kunnen niet inloggen op Prrepl.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.role_help' translation_key,'Employee access is controlled by the selected role.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.role_help' translation_key,'L’accès de l’employé est contrôlé par le rôle sélectionné.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.role_help' translation_key,'Toegang van medewerkers wordt bepaald door de geselecteerde rol.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.mobile' translation_key,'Mobile' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.mobile' translation_key,'Mobile' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.mobile' translation_key,'Mobiel' translation_value
  UNION ALL
  SELECT 'en' code,'employees.job_title' translation_key,'Job title' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.job_title' translation_key,'Fonction' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.job_title' translation_key,'Functie' translation_value
  UNION ALL
  SELECT 'en' code,'employees.bio' translation_key,'Bio' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.bio' translation_key,'Biographie' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.bio' translation_key,'Bio' translation_value
  UNION ALL
  SELECT 'en' code,'employees.language' translation_key,'Language' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.language' translation_key,'Langue' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.language' translation_key,'Taal' translation_value
  UNION ALL
  SELECT 'en' code,'employees.password' translation_key,'Password' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.password' translation_key,'Mot de passe' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.password' translation_key,'Wachtwoord' translation_value
  UNION ALL
  SELECT 'en' code,'employees.password_hint' translation_key,'Leave blank to keep current password' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.password_hint' translation_key,'Laissez vide pour conserver le mot de passe actuel' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.password_hint' translation_key,'Laat leeg om het huidige wachtwoord te behouden' translation_value
  UNION ALL
  SELECT 'en' code,'employees.password_new' translation_key,'Minimum 12 characters' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.password_new' translation_key,'12 caractères minimum' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.password_new' translation_key,'Minimaal 12 tekens' translation_value
  UNION ALL
  SELECT 'en' code,'employees.edit_title' translation_key,'Edit employee' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.edit_title' translation_key,'Modifier l’employé' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.edit_title' translation_key,'Medewerker bewerken' translation_value
  UNION ALL
  SELECT 'en' code,'employees.edit_hint' translation_key,'Update the employee details and access settings below.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.edit_hint' translation_key,'Mettez à jour les informations et les paramètres d’accès ci-dessous.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.edit_hint' translation_key,'Werk de medewerkergegevens en toegangsinstellingen hieronder bij.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.create_title' translation_key,'Create employee' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.create_title' translation_key,'Créer un employé' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.create_title' translation_key,'Medewerker aanmaken' translation_value
  UNION ALL
  SELECT 'en' code,'employees.create_hint' translation_key,'Complete the employee profile and access settings below.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.create_hint' translation_key,'Complétez le profil de l’employé et les paramètres d’accès ci-dessous.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.create_hint' translation_key,'Vul hieronder het medewerkerprofiel en de toegangsinstellingen in.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.create_subtitle' translation_key,'Create a new user and choose their access role.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.create_subtitle' translation_key,'Créez un nouvel utilisateur et choisissez son rôle d’accès.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.create_subtitle' translation_key,'Maak een nieuwe gebruiker aan en kies de toegangsrol.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.save' translation_key,'Save changes' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.save' translation_key,'Enregistrer les modifications' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.save' translation_key,'Wijzigingen opslaan' translation_value
  UNION ALL
  SELECT 'en' code,'employees.delete' translation_key,'Delete' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.delete' translation_key,'Supprimer' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.delete' translation_key,'Verwijderen' translation_value
  UNION ALL
  SELECT 'en' code,'employees.danger_title' translation_key,'Account actions' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.danger_title' translation_key,'Actions du compte' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.danger_title' translation_key,'Accountacties' translation_value
  UNION ALL
  SELECT 'en' code,'employees.danger_text' translation_key,'Deactivate access temporarily or permanently remove this employee from the workspace.' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.danger_text' translation_key,'Désactivez temporairement l’accès ou supprimez définitivement cet employé de l’espace de travail.' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.danger_text' translation_key,'Deactiveer toegang tijdelijk of verwijder deze medewerker permanent uit de werkruimte.' translation_value
  UNION ALL
  SELECT 'en' code,'employees.deactivate' translation_key,'Deactivate' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.deactivate' translation_key,'Désactiver' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.deactivate' translation_key,'Deactiveren' translation_value
  UNION ALL
  SELECT 'en' code,'employees.activate' translation_key,'Activate' translation_value
  UNION ALL
  SELECT 'fr' code,'employees.activate' translation_key,'Activer' translation_value
  UNION ALL
  SELECT 'nl' code,'employees.activate' translation_key,'Activeren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.manage' translation_key,'Manage your offices' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.manage' translation_key,'Gérez vos agences' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.manage' translation_key,'Beheer je kantoren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.subtitle' translation_key,'Manage offices, contact details and workspace settings.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.subtitle' translation_key,'Gérez les agences, leurs coordonnées et les paramètres de l’espace de travail.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.subtitle' translation_key,'Beheer kantoren, contactgegevens en werkruimte-instellingen.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.create' translation_key,'New office' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.create' translation_key,'Nouvelle agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.create' translation_key,'Nieuw kantoor' translation_value
  UNION ALL
  SELECT 'en' code,'offices.total' translation_key,'TOTAL OFFICES' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.total' translation_key,'TOTAL DES AGENCES' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.total' translation_key,'TOTAAL KANTOREN' translation_value
  UNION ALL
  SELECT 'en' code,'offices.total_hint' translation_key,'All active records' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.total_hint' translation_key,'Tous les enregistrements actifs' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.total_hint' translation_key,'Alle actieve records' translation_value
  UNION ALL
  SELECT 'en' code,'offices.active' translation_key,'ACTIVE' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.active' translation_key,'ACTIVES' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.active' translation_key,'ACTIEF' translation_value
  UNION ALL
  SELECT 'en' code,'offices.active_hint' translation_key,'Active workspaces' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.active_hint' translation_key,'Espaces de travail actifs' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.active_hint' translation_key,'Actieve werkruimtes' translation_value
  UNION ALL
  SELECT 'en' code,'offices.employees' translation_key,'EMPLOYEES' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.employees' translation_key,'EMPLOYÉS' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.employees' translation_key,'MEDEWERKERS' translation_value
  UNION ALL
  SELECT 'en' code,'offices.employees_hint' translation_key,'User accounts' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.employees_hint' translation_key,'Comptes utilisateurs' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.employees_hint' translation_key,'Gebruikersaccounts' translation_value
  UNION ALL
  SELECT 'en' code,'offices.properties' translation_key,'PROPERTIES' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.properties' translation_key,'BIENS' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.properties' translation_key,'OBJECTEN' translation_value
  UNION ALL
  SELECT 'en' code,'offices.properties_hint' translation_key,'Assigned properties' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.properties_hint' translation_key,'Biens attribués' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.properties_hint' translation_key,'Toegewezen objecten' translation_value
  UNION ALL
  SELECT 'en' code,'offices.directory' translation_key,'DIRECTORY' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.directory' translation_key,'ANNUAIRE' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.directory' translation_key,'DIRECTORY' translation_value
  UNION ALL
  SELECT 'en' code,'offices.all' translation_key,'Offices' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.all' translation_key,'Agences' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.all' translation_key,'Kantoren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.empty' translation_key,'No offices yet' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.empty' translation_key,'Aucune agence pour le moment' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.empty' translation_key,'Nog geen kantoren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.empty_hint' translation_key,'Create your first office to get started.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.empty_hint' translation_key,'Créez votre première agence pour commencer.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.empty_hint' translation_key,'Maak je eerste kantoor aan om te beginnen.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.title' translation_key,'Offices' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.title' translation_key,'Agences' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.title' translation_key,'Kantoren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.new' translation_key,'New office' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.new' translation_key,'Nouvelle agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.new' translation_key,'Nieuw kantoor' translation_value
  UNION ALL
  SELECT 'en' code,'offices.edit' translation_key,'Edit office' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.edit' translation_key,'Modifier l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.edit' translation_key,'Kantoor bewerken' translation_value
  UNION ALL
  SELECT 'en' code,'offices.create_hint' translation_key,'Create a new office and configure its workspace settings.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.create_hint' translation_key,'Créez une nouvelle agence et configurez ses paramètres.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.create_hint' translation_key,'Maak een nieuw kantoor aan en configureer de instellingen.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.edit_hint' translation_key,'Update the office details and workspace settings.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.edit_hint' translation_key,'Mettez à jour les informations de l’agence et ses paramètres.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.edit_hint' translation_key,'Werk de kantoorgegevens en instellingen bij.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.details' translation_key,'Office details' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.details' translation_key,'Détails de l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.details' translation_key,'Kantoorgegevens' translation_value
  UNION ALL
  SELECT 'en' code,'offices.code' translation_key,'Office code' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.code' translation_key,'Code agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.code' translation_key,'Kantoorcode' translation_value
  UNION ALL
  SELECT 'en' code,'offices.code_help' translation_key,'3 letters used for property references, e.g. CDS-00001.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.code_help' translation_key,'3 lettres utilisées pour les références des biens, par ex. CDS-00001.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.code_help' translation_key,'3 letters die voor objectreferenties worden gebruikt, bijv. CDS-00001.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.legal_name' translation_key,'Legal name' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.legal_name' translation_key,'Raison sociale' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.legal_name' translation_key,'Juridische naam' translation_value
  UNION ALL
  SELECT 'en' code,'offices.website' translation_key,'Website' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.website' translation_key,'Site web' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.website' translation_key,'Website' translation_value
  UNION ALL
  SELECT 'en' code,'offices.address' translation_key,'Address' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.address' translation_key,'Adresse' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.address' translation_key,'Adres' translation_value
  UNION ALL
  SELECT 'en' code,'offices.address1' translation_key,'Address line 1' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.address1' translation_key,'Adresse ligne 1' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.address1' translation_key,'Adresregel 1' translation_value
  UNION ALL
  SELECT 'en' code,'offices.address2' translation_key,'Address line 2' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.address2' translation_key,'Adresse ligne 2' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.address2' translation_key,'Adresregel 2' translation_value
  UNION ALL
  SELECT 'en' code,'offices.postcode' translation_key,'Postcode' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.postcode' translation_key,'Code postal' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.postcode' translation_key,'Postcode' translation_value
  UNION ALL
  SELECT 'en' code,'offices.country' translation_key,'Country code' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.country' translation_key,'Code pays' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.country' translation_key,'Landcode' translation_value
  UNION ALL
  SELECT 'en' code,'offices.settings' translation_key,'Workspace settings' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.settings' translation_key,'Paramètres de l’espace de travail' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.settings' translation_key,'Werkruimte-instellingen' translation_value
  UNION ALL
  SELECT 'en' code,'offices.language' translation_key,'Default language' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.language' translation_key,'Langue par défaut' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.language' translation_key,'Standaardtaal' translation_value
  UNION ALL
  SELECT 'en' code,'offices.timezone' translation_key,'Timezone' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.timezone' translation_key,'Fuseau horaire' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.timezone' translation_key,'Tijdzone' translation_value
  UNION ALL
  SELECT 'en' code,'offices.currency' translation_key,'Currency' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.currency' translation_key,'Devise' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.currency' translation_key,'Valuta' translation_value
  UNION ALL
  SELECT 'en' code,'offices.danger' translation_key,'Office actions' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.danger' translation_key,'Actions de l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.danger' translation_key,'Kantooracties' translation_value
  UNION ALL
  SELECT 'en' code,'offices.deactivate' translation_key,'Deactivate office' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.deactivate' translation_key,'Désactiver l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.deactivate' translation_key,'Kantoor deactiveren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.deactivate_hint' translation_key,'Prevent this office from being used as an active workspace.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.deactivate_hint' translation_key,'Empêcher l’utilisation de cette agence comme espace de travail actif.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.deactivate_hint' translation_key,'Voorkom dat dit kantoor als actieve werkruimte wordt gebruikt.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.activate' translation_key,'Activate office' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.activate' translation_key,'Activer l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.activate' translation_key,'Kantoor activeren' translation_value
  UNION ALL
  SELECT 'en' code,'offices.activate_hint' translation_key,'Make this office available again.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.activate_hint' translation_key,'Rendre cette agence à nouveau disponible.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.activate_hint' translation_key,'Maak dit kantoor weer beschikbaar.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.delete' translation_key,'Delete office' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.delete' translation_key,'Supprimer l’agence' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.delete' translation_key,'Kantoor verwijderen' translation_value
  UNION ALL
  SELECT 'en' code,'offices.delete_hint' translation_key,'Remove this office from the active office directory.' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.delete_hint' translation_key,'Retirer cette agence de l’annuaire actif.' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.delete_hint' translation_key,'Verwijder dit kantoor uit de actieve kantoorlijst.' translation_value
  UNION ALL
  SELECT 'en' code,'offices.status_kicker' translation_key,'OFFICE STATUS' translation_value
  UNION ALL
  SELECT 'fr' code,'offices.status_kicker' translation_key,'STATUT DE L’AGENCE' translation_value
  UNION ALL
  SELECT 'nl' code,'offices.status_kicker' translation_key,'KANTOORSTATUS' translation_value
) x ON x.code=l.code WHERE NOT EXISTS (SELECT 1 FROM i18n_translations t WHERE t.language_id=l.id AND t.translation_key=x.translation_key);
