# Formulaires d'inscription Sleeve KE

Ce fichier explique comment utiliser les formulaires d'inscription de Sleeve KE avec les shortcodes WordPress.

## Shortcodes disponibles

### 1. Formulaire d'inscription Employeur
```
[sleeve_ke_employer_registration]
```

### 2. Formulaire d'inscription Candidat
```
[sleeve_ke_candidate_registration]
```

## Options personnalisables

### Redirection après inscription
```
[sleeve_ke_employer_registration redirect_url="/dashboard-employeur"]
[sleeve_ke_candidate_registration redirect_url="/profile-candidat"]
```

### Titre personnalisé
```
[sleeve_ke_employer_registration title="Créer un compte employeur"]
[sleeve_ke_candidate_registration title="Rejoignez notre communauté"]
```

### Description personnalisée
```
[sleeve_ke_employer_registration description="Gérez vos offres d'emploi et trouvez les meilleurs talents"]
[sleeve_ke_candidate_registration description="Découvrez des opportunités d'emploi passionnantes"]
```

### Combinaison d'options
```
[sleeve_ke_employer_registration 
    title="Inscription Entreprise" 
    description="Créez votre compte pour publier des offres d'emploi" 
    redirect_url="/bienvenue-employeur"]

[sleeve_ke_candidate_registration 
    title="Inscription Candidat" 
    description="Trouvez l'emploi de vos rêves" 
    redirect_url="/bienvenue-candidat"]
```

## Fonctionnalités incluses

### Formulaire Employeur
- **Informations de compte** : Email, mot de passe, confirmation
- **Informations entreprise** : Nom, secteur d'activité, taille, site web
- **Détails de contact** : Téléphone, adresse complète
- **Description de l'entreprise**
- **Conditions d'utilisation et politique de confidentialité**

### Formulaire Candidat
- **Informations de compte** : Email, mot de passe, confirmation
- **Informations personnelles** : Prénom, nom, téléphone
- **Informations professionnelles** : Titre du poste recherché, niveau d'expérience
- **Localisation** : Ville, disponibilité pour déménager
- **CV et présentation**
- **Conditions d'utilisation et politique de confidentialité**

## Intégration dans WordPress

### 1. Pages dédiées
Créez des pages WordPress et ajoutez les shortcodes :

**Page "Inscription Employeur" :**
```
[sleeve_ke_employer_registration 
    title="Recrutez les meilleurs talents" 
    description="Créez votre compte entreprise et commencez à publier vos offres d'emploi dès aujourd'hui"
    redirect_url="/dashboard-employeur"]
```

**Page "Inscription Candidat" :**
```
[sleeve_ke_candidate_registration 
    title="Trouvez votre prochain emploi" 
    description="Rejoignez des milliers de candidats et accédez aux meilleures opportunités"
    redirect_url="/profile-candidat"]
```

### 2. Widget ou sidebar
Les shortcodes peuvent être utilisés dans les widgets texte.

### 3. Éditeur de blocs (Gutenberg)
Utilisez le bloc "Shortcode" et insérez le code correspondant.

## Notifications automatiques

Les formulaires sont intégrés avec le système de notifications Sleeve KE :

- **Email de bienvenue** envoyé automatiquement après inscription
- **Notification admin** pour les nouvelles inscriptions
- **Instructions de première connexion**

## Sécurité et validation

- **Validation côté client** avec JavaScript
- **Validation côté serveur** avec PHP
- **Protection CSRF** avec nonces WordPress
- **Sanitisation des données**
- **Vérification d'email unique**
- **Politique de mot de passe sécurisé**

## CSS et personnalisation

Le fichier `assets/css/sleeve-ke-registration.css` contient tous les styles.

### Classes CSS principales :
- `.sleeve-ke-registration-form` : Container principal
- `.employer-registration` : Styles spécifiques employeur
- `.candidate-registration` : Styles spécifiques candidat
- `.registration-header` : En-tête du formulaire
- `.form-section` : Sections du formulaire
- `.form-control` : Champs de saisie

### Personnalisation des couleurs :
```css
/* Couleur principale employeur : vert */
.employer-registration .registration-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

/* Couleur principale candidat : bleu */
.candidate-registration .registration-header {
    background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
}
```

## Gestion des erreurs

Les formulaires gèrent automatiquement :
- **Emails déjà utilisés**
- **Mots de passe non conformes**
- **Champs obligatoires manquants**
- **Erreurs de base de données**
- **Messages d'erreur traduits en français**

## Hooks et actions disponibles

### Actions déclenchées :
```php
// Nouvel employeur inscrit
do_action('sleeve_ke_new_employer_registered', $user_id);

// Nouveau candidat inscrit
do_action('sleeve_ke_new_candidate_registered', $user_id);

// Après traitement du formulaire
do_action('sleeve_ke_registration_form_processed', $user_id, $user_type);
```

### Filtres disponibles :
```php
// Personnaliser les champs requis
apply_filters('sleeve_ke_registration_required_fields', $required_fields, $user_type);

// Modifier la redirection
apply_filters('sleeve_ke_registration_redirect_url', $redirect_url, $user_type);

// Personnaliser les méta-données utilisateur
apply_filters('sleeve_ke_registration_user_meta', $user_meta, $user_type);
```

## Exemple d'utilisation avancée

```php
// Dans le fichier functions.php de votre thème
add_action('sleeve_ke_new_employer_registered', function($user_id) {
    // Logique personnalisée après inscription employeur
    update_user_meta($user_id, 'account_status', 'pending_approval');
    
    // Envoyer email de validation manuel
    wp_mail(
        get_option('admin_email'),
        'Nouvel employeur à valider',
        "Un nouvel employeur s'est inscrit : " . get_userdata($user_id)->user_email
    );
});

add_filter('sleeve_ke_registration_redirect_url', function($url, $user_type) {
    if ($user_type === 'employer') {
        return site_url('/validation-compte-employeur');
    }
    return $url;
}, 10, 2);
```

## Support responsive

Les formulaires sont entièrement responsifs et s'adaptent à tous les écrans :
- **Desktop** : Formulaire sur 2 colonnes
- **Tablet** : Formulaire sur 1 colonne
- **Mobile** : Interface optimisée tactile