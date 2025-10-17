# 📧 Système de Notifications Email - Sleeve KE

## Vue d'ensemble

Le système de notifications email de Sleeve KE fournit un système complet de gestion des emails automatiques pour toutes les interactions du job board, incluant les candidatures, les mises à jour de statut, les inscriptions, et les notifications administratives.

## 🚀 Fonctionnalités Principales

### 📊 **Interface d'Administration Complète**
- **Logs d'emails** : Historique détaillé de tous les emails envoyés
- **Paramètres de notification** : Configuration granulaire des types d'emails
- **Templates d'email** : Gestion des modèles d'email personnalisables
- **Tests d'email** : Envoi d'emails de test pour validation

### 📧 **Types de Notifications Automatiques**

#### Pour les Candidats
- ✅ **Confirmation de candidature** : Email immédiat après soumission
- 🔄 **Mises à jour de statut** : Notifications lors des changements (approuvé, rejeté, entretien, etc.)
- 🎯 **Alertes emploi** : Nouveaux emplois correspondant au profil (optionnel)
- 👋 **Email de bienvenue** : Lors de l'inscription

#### Pour les Employeurs
- 📝 **Nouvelle candidature** : Notification immédiate de réception
- ✅ **Emploi approuvé** : Confirmation de publication d'emploi
- 👤 **Changement de statut compte** : Mises à jour du compte employeur
- 👋 **Email de bienvenue** : Lors de l'inscription

#### Pour les Administrateurs
- 🆕 **Nouvelle candidature** : Alerte de nouvelle candidature
- 🏢 **Nouvel employeur** : Notification d'inscription employeur
- 👥 **Nouveau candidat** : Notification d'inscription candidat
- 💼 **Nouvel emploi** : Alerte de publication d'emploi

### 🎨 **Templates d'Email Professionnels**
- **Design responsive** compatible mobile
- **Variables dynamiques** pour personnalisation
- **Branding cohérent** avec identité Sleeve KE
- **Structure HTML professionnelle**

### 📈 **Système de Logs et Statistiques**
- **Historique complet** des emails envoyés
- **Statuts de livraison** (envoyé, échoué, en attente)
- **Taux de succès** et métriques de performance
- **Messages d'erreur détaillés** pour le debugging

## 🛠️ Utilisation Technique

### Installation et Configuration

1. **Activation automatique** : Le système s'active avec le plugin
2. **Configuration** : Aller dans `Sleeve KE > Notifications > Settings`
3. **Personnalisation** : Configurer l'email expéditeur et les types de notifications

### Méthodes Principales

#### Envoi Direct de Notification
```php
// Envoi d'une notification directe
sleeve_ke_send_notification( $type, $recipient, $variables );

// Exemple
sleeve_ke_send_notification( 
    'application_received_candidate', 
    'candidate@email.com', 
    array(
        'candidate_name' => 'John Doe',
        'job_title' => 'Software Developer',
        'company_name' => 'Tech Corp',
        'application_date' => date('F j, Y')
    ) 
);
```

#### Déclencheurs Automatiques
```php
// Déclencher notification de nouvelle candidature
sleeve_ke_trigger_new_application( $application_id );

// Déclencher notification d'inscription employeur
sleeve_ke_trigger_new_employer( $employer_id );

// Déclencher notification d'inscription candidat
sleeve_ke_trigger_new_candidate( $candidate_id );

// Déclencher notification d'emploi publié
sleeve_ke_trigger_job_posted( $job_id );
```

### Templates Disponibles

| Template | Description | Variables |
|----------|-------------|-----------|
| `application_received_candidate` | Confirmation candidature pour candidat | `candidate_name`, `job_title`, `company_name`, `application_date` |
| `application_received_employer` | Nouvelle candidature pour employeur | `employer_name`, `job_title`, `candidate_name`, `application_date` |
| `application_status_update` | Mise à jour statut candidature | `candidate_name`, `job_title`, `status`, `status_message` |
| `job_posted_admin` | Nouvel emploi pour admin | `job_title`, `company_name`, `employer_name`, `post_date` |
| `employer_registered` | Bienvenue employeur | `employer_name`, `company_name`, `login_url` |
| `candidate_registered` | Bienvenue candidat | `candidate_name`, `login_url`, `jobs_url` |
| `job_alert` | Alerte emploi pour candidat | `candidate_name`, `job_count`, `jobs_list`, `unsubscribe_url` |

## 🔧 Configuration des Paramètres

### Paramètres Globaux
- **Activer les notifications** : Interrupteur principal
- **Email expéditeur** : Adresse email source
- **Nom expéditeur** : Nom affiché comme expéditeur

### Notifications Administrateur
- ✅ Nouvelle candidature soumise
- ✅ Nouvel employeur inscrit
- ✅ Nouveau candidat inscrit
- ✅ Nouvel emploi publié

### Notifications Candidat
- ✅ Confirmation de candidature reçue
- ✅ Mises à jour de statut candidature
- ⚪ Alertes emploi (désactivé par défaut)

### Notifications Employeur
- ✅ Nouvelle candidature sur leurs emplois
- ✅ Emploi approuvé
- ✅ Changements statut compte

## 📊 Interface d'Administration

### Onglet "Email Logs"
- **Tableau chronologique** des emails envoyés
- **Filtres** par type, statut, destinataire
- **Actions** : Voir contenu, renvoyer email échoué
- **Statistiques** de performance (30 derniers jours)

### Onglet "Settings"
- **Configuration générale** du système
- **Activation/désactivation** par type de notification
- **Paramètres expéditeur** (email et nom)

### Onglet "Email Templates"
- **Aperçu des templates** disponibles
- **Édition** des modèles (à venir)
- **Prévisualisation** des emails

### Onglet "Test Emails"
- **Envoi d'emails de test** pour validation
- **Sélection du template** à tester
- **Destinataire configurable**

## 🎯 Exemples d'Intégration

### Formulaire Frontend avec Notifications
```php
// Exemple d'intégration dans un thème
function handle_job_application() {
    // Traitement candidature
    $application_id = save_application_to_database();
    
    // Déclencher notifications automatiques
    sleeve_ke_trigger_new_application( $application_id );
}

// Shortcode pour formulaire avec notifications
echo do_shortcode('[sleeve_ke_job_application job_id="123"]');
```

### Hook Personnalisé
```php
// Ajouter des notifications personnalisées
add_action( 'sleeve_ke_new_application_submitted', function( $application_id ) {
    // Votre logique personnalisée
    // Les notifications standard sont déjà envoyées automatiquement
});
```

## 🛡️ Sécurité et Performance

### Sécurité
- **Nonces WordPress** pour tous les formulaires
- **Sanitisation** de toutes les données utilisateur
- **Vérification des permissions** administrateur
- **Protection contre spam** via hooks WordPress

### Performance
- **Envoi asynchrone** possible via WP Cron
- **Limite de taille** des logs automatique
- **Optimisation AJAX** pour interface admin
- **Cache des templates** pour performance

### Logging et Debug
- **Logs système** via `error_log()` WordPress
- **Messages d'erreur détaillés** en interface admin
- **Mode debug** avec informations étendues
- **Historique complet** pour audit

## 🔄 Workflow Complet

### Nouvelle Candidature
1. **Candidat soumet** formulaire → `sleeve_ke_trigger_new_application()`
2. **Email confirmation** envoyé au candidat
3. **Email notification** envoyé à l'employeur
4. **Email admin** envoyé si activé
5. **Logs enregistrés** pour audit

### Mise à Jour Statut
1. **Employeur/Admin change** statut candidature
2. **AJAX trigger** → `ajax_update_application_status()`
3. **Email notification** envoyé au candidat avec message personnalisé
4. **Log mis à jour** avec nouveau statut

### Inscription Utilisateur
1. **Nouvel utilisateur** s'inscrit (employeur/candidat)
2. **Hook trigger** → `sleeve_ke_trigger_new_employer/candidate()`
3. **Email bienvenue** envoyé à l'utilisateur
4. **Notification admin** envoyée si activée

## 📱 Compatibilité

- ✅ **WordPress 5.8+**
- ✅ **PHP 7.4+**
- ✅ **Emails HTML responsive**
- ✅ **Clients email majeurs** (Gmail, Outlook, Apple Mail)
- ✅ **Serveurs SMTP** standard
- ✅ **Plugins d'email** WordPress (WP Mail SMTP, etc.)

## 🎨 Personnalisation

### Variables Templates
Utilisez `{variable_name}` dans vos templates :
- `{candidate_name}` - Nom du candidat
- `{job_title}` - Titre de l'emploi
- `{company_name}` - Nom de l'entreprise
- `{application_date}` - Date de candidature
- `{status}` - Statut actuel
- `{login_url}` - URL de connexion
- Et bien d'autres...

### Styles CSS
Les emails incluent des styles inline pour compatibilité maximale, mais peuvent être personnalisés via les templates.

## 🚀 Prochaines Fonctionnalités

- 📝 **Éditeur de templates** WYSIWYG
- 📊 **Analytics détaillées** d'engagement
- 🔄 **Templates conditionnels** basés sur données
- 📱 **Notifications push** (optionnel)
- 🌍 **Multi-langue** avancée
- 📈 **A/B testing** templates
- 🤖 **Intégration AI** pour contenu personnalisé

---

Le système de notifications de Sleeve KE transforme votre job board en une plateforme de communication automatisée et professionnelle, garantissant que tous les acteurs restent informés à chaque étape du processus de recrutement ! 🎉