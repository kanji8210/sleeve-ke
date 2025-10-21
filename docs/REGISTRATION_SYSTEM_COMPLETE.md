# Système de formulaires d'inscription - Sleeve KE

## 📋 Vue d'ensemble

Les formulaires d'inscription Sleeve KE permettent aux employeurs et candidats de s'inscrire directement depuis le frontend de votre site WordPress via des shortcodes simples.

## ✅ Fonctionnalités implémentées

### 🎨 Interface utilisateur
- ✅ Formulaires responsives avec design moderne
- ✅ Styles différenciés (vert pour employeurs, bleu pour candidats)
- ✅ Animation et transitions CSS
- ✅ Support mobile optimisé

### 🔧 Fonctionnalités techniques
- ✅ Shortcodes WordPress `[sleeve_ke_employer_registration]` et `[sleeve_ke_candidate_registration]`
- ✅ Validation en temps réel avec JavaScript
- ✅ Vérification d'email en AJAX
- ✅ Indicateur de force de mot de passe
- ✅ Upload de fichiers (CV) avec validation
- ✅ Protection CSRF avec nonces WordPress

### 🔐 Sécurité
- ✅ Sanitisation de toutes les données
- ✅ Validation côté serveur et client
- ✅ Protection contre les attaques CSRF
- ✅ Vérification des types de fichiers
- ✅ Limitation de taille des fichiers (5MB)

### 📧 Intégration notifications
- ✅ Emails de bienvenue automatiques
- ✅ Notifications admin pour nouvelles inscriptions
- ✅ Hooks personnalisés pour extensions

### 🎯 Gestion des rôles
- ✅ Création automatique des comptes utilisateurs
- ✅ Attribution des rôles (employer/candidate)
- ✅ Stockage des métadonnées utilisateur
- ✅ Redirection post-inscription personnalisable

## 📁 Fichiers créés

```
sleeve-ke/
├── public/
│   └── class-sleeve-ke-registration-forms.php    (985 lignes - Classe principale)
├── assets/
│   ├── css/
│   │   └── sleeve-ke-registration.css             (680 lignes - Styles complets)
│   └── js/
│       └── sleeve-ke-registration.js              (420 lignes - JavaScript interactif)
├── docs/
│   └── REGISTRATION_FORMS.md                      (Documentation complète)
└── examples/
    └── registration-forms-test.md                 (Guide de test)
```

## 🚀 Installation et activation

### 1. Intégration automatique
Les formulaires sont automatiquement chargés quand le plugin Sleeve KE est activé. Aucune configuration supplémentaire n'est requise.

### 2. Vérification de l'intégration
Les modifications suivantes ont été apportées à `sleeve-ke.php` :
```php
// Chargement automatique de la classe sur le frontend
if ( ! is_admin() ) {
    require_once SLEEVE_KE_PLUGIN_DIR . 'public/class-sleeve-ke-registration-forms.php';
    add_action( 'init', function() {
        new Sleeve_KE_Registration_Forms();
    } );
}
```

### 3. Assets automatiques
- CSS et JavaScript se chargent uniquement sur les pages contenant les shortcodes
- Optimisation des performances avec chargement conditionnel
- Version cache-busting avec `SLEEVE_KE_VERSION`

## 📝 Utilisation des shortcodes

### Shortcode employeur
```
[sleeve_ke_employer_registration]
```

### Shortcode candidat
```
[sleeve_ke_candidate_registration]
```

### Options disponibles
```
[sleeve_ke_employer_registration 
    title="Titre personnalisé" 
    description="Description personnalisée"
    redirect_url="/page-de-redirection"]
```

## 🎨 Personnalisation CSS

### Variables principales
```css
/* Couleurs employeur */
.employer-registration {
    --primary-color: #28a745;
    --hover-color: #218838;
}

/* Couleurs candidat */
.candidate-registration {
    --primary-color: #007bff;
    --hover-color: #0056b3;
}
```

### Classes importantes
- `.sleeve-ke-registration-form` : Container principal
- `.registration-header` : En-tête avec titre et description
- `.form-section` : Sections du formulaire
- `.form-control` : Champs de saisie
- `.btn-primary` : Boutons principaux

## ⚡ Fonctionnalités JavaScript

### Validation en temps réel
- Vérification format email
- Contrôle disponibilité email (AJAX)
- Validation numéro téléphone français
- Indicateur force mot de passe
- Confirmation mot de passe

### Améliorations UX
- Animation des erreurs
- États de chargement
- Compteur de caractères
- Aperçu des fichiers uploadés
- Scroll automatique vers les erreurs

## 🔌 Hooks et filtres

### Actions déclenchées
```php
do_action('sleeve_ke_new_employer_registered', $user_id);
do_action('sleeve_ke_new_candidate_registered', $user_id);
do_action('sleeve_ke_registration_form_processed', $user_id, $user_type);
```

### Filtres disponibles
```php
apply_filters('sleeve_ke_registration_required_fields', $fields, $user_type);
apply_filters('sleeve_ke_registration_redirect_url', $url, $user_type);
apply_filters('sleeve_ke_registration_user_meta', $meta, $user_type);
```

## 🗄️ Base de données

### Tables utilisées
- `wp_users` : Comptes utilisateurs
- `wp_usermeta` : Métadonnées utilisateur

### Métadonnées candidat
- `user_phone` : Téléphone
- `user_city` : Ville
- `job_title` : Poste recherché
- `experience_level` : Niveau d'expérience
- `willing_to_relocate` : Disponible pour déménager
- `bio` : Présentation

### Métadonnées employeur
- `company_name` : Nom entreprise
- `company_industry` : Secteur d'activité
- `company_size` : Taille entreprise
- `company_website` : Site web
- `company_phone` : Téléphone
- `company_address` : Adresse
- `company_city` : Ville
- `company_country` : Pays
- `company_description` : Description

## 🧪 Tests et validation

### Tests automatisés
1. **Validation côté client** : JavaScript
2. **Validation côté serveur** : PHP
3. **Sécurité CSRF** : Nonces WordPress
4. **Upload de fichiers** : Taille et type
5. **Disponibilité email** : AJAX en temps réel

### Tests manuels
1. Créer les pages de test (voir `registration-forms-test.md`)
2. Tester tous les champs obligatoires
3. Vérifier les emails de notification
4. Contrôler la création des comptes
5. Valider les redirections

## 🔧 Dépannage

### Problèmes courants

**Shortcode ne s'affiche pas :**
- Vérifier que le plugin est activé
- Contrôler les permissions de fichiers
- Vérifier les erreurs PHP dans les logs

**CSS/JS ne se charge pas :**
- Vider le cache WordPress
- Vérifier les URLs des assets
- Contrôler les permissions de fichiers

**Emails non envoyés :**
- Vérifier la configuration SMTP
- Contrôler les paramètres de notifications
- Tester la fonction `wp_mail()`

**Erreurs JavaScript :**
- Ouvrir la console développeur
- Vérifier jQuery est chargé
- Contrôler les conflits avec d'autres plugins

### Debug
```php
// Dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 🚀 Prochaines étapes

### Améliorations possibles
1. **Interface admin** : Gestion des inscriptions en attente
2. **Validation email** : Confirmation par email avant activation
3. **Champs personnalisés** : Configuration des champs par admin
4. **Import/Export** : Sauvegarde des données utilisateur
5. **Statistiques** : Dashboard avec métriques d'inscription

### Intégrations avancées
1. **reCAPTCHA** : Protection anti-spam
2. **Social login** : Connexion via réseaux sociaux
3. **API REST** : Endpoints pour applications mobiles
4. **Webhooks** : Intégration avec services externes

## 📞 Support

Pour toute question ou problème :
1. Consulter la documentation dans `/docs/`
2. Vérifier les exemples dans `/examples/`
3. Contrôler les logs WordPress
4. Tester avec un thème par défaut

---

**Système créé par GitHub Copilot pour Sleeve KE**  
**Version :** 1.0.0  
**Compatibilité :** WordPress 5.8+ / PHP 7.4+  
**Dernière mise à jour :** Décembre 2024