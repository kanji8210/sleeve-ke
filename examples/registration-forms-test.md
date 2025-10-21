# Test des formulaires d'inscription - Sleeve KE

Ce fichier contient des exemples pratiques pour tester les formulaires d'inscription.

## 1. Page d'inscription Employeur

Créez une nouvelle page WordPress avec le contenu suivant :

**Titre de la page :** `Inscription Employeur`
**Slug :** `inscription-employeur`
**Contenu :**

```html
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">Recrutez les meilleurs talents</h1>
    <p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 40px;">
        Rejoignez plus de 1000 entreprises qui font confiance à Sleeve KE pour leurs recrutements.
    </p>
    
    [sleeve_ke_employer_registration 
        title="Créer votre compte entreprise" 
        description="Publiez vos offres d'emploi et accédez à notre base de candidats qualifiés"
        redirect_url="/dashboard-employeur"]
</div>
```

## 2. Page d'inscription Candidat

Créez une nouvelle page WordPress avec le contenu suivant :

**Titre de la page :** `Inscription Candidat`
**Slug :** `inscription-candidat`
**Contenu :**

```html
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">Trouvez l'emploi de vos rêves</h1>
    <p style="text-align: center; font-size: 18px; color: #666; margin-bottom: 40px;">
        Accédez à des milliers d'offres d'emploi et connectez-vous avec les meilleurs employeurs.
    </p>
    
    [sleeve_ke_candidate_registration 
        title="Créer votre profil candidat" 
        description="Construisez votre profil professionnel et postulez aux meilleures opportunités"
        redirect_url="/profile-candidat"]
</div>
```

## 3. Page de choix d'inscription

Créez une page d'accueil pour orienter les utilisateurs :

**Titre de la page :** `Rejoignez-nous`
**Slug :** `rejoignez-nous`
**Contenu :**

```html
<div style="max-width: 1200px; margin: 0 auto; padding: 40px 20px; text-align: center;">
    <h1 style="margin-bottom: 20px;">Rejoignez la communauté Sleeve KE</h1>
    <p style="font-size: 18px; color: #666; margin-bottom: 50px;">
        Que vous soyez à la recherche de talents ou d'opportunités, nous avons la solution pour vous.
    </p>
    
    <div style="display: flex; gap: 40px; justify-content: center; flex-wrap: wrap;">
        <!-- Card Employeur -->
        <div style="flex: 1; max-width: 400px; padding: 30px; border: 2px solid #28a745; border-radius: 10px; background: #f8fff9;">
            <div style="font-size: 48px; margin-bottom: 20px;">🏢</div>
            <h3 style="color: #28a745; margin-bottom: 15px;">Je suis un employeur</h3>
            <p style="margin-bottom: 25px; color: #666;">
                Publiez vos offres d'emploi, gérez vos candidatures et trouvez les profils parfaits pour votre entreprise.
            </p>
            <a href="/inscription-employeur" 
               style="display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: 600;">
                S'inscrire comme employeur
            </a>
        </div>
        
        <!-- Card Candidat -->
        <div style="flex: 1; max-width: 400px; padding: 30px; border: 2px solid #007bff; border-radius: 10px; background: #f8fbff;">
            <div style="font-size: 48px; margin-bottom: 20px;">👤</div>
            <h3 style="color: #007bff; margin-bottom: 15px;">Je suis un candidat</h3>
            <p style="margin-bottom: 25px; color: #666;">
                Créez votre profil, explorez des milliers d'offres et postulez aux emplois qui vous correspondent.
            </p>
            <a href="/inscription-candidat" 
               style="display: inline-block; background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: 600;">
                S'inscrire comme candidat
            </a>
        </div>
    </div>
</div>
```

## 4. Test des fonctionnalités

### Validation en temps réel
1. **Email** : Tapez un email invalide → message d'erreur immédiat
2. **Email existant** : Utilisez un email déjà enregistré → "Cette adresse email est déjà utilisée"
3. **Mot de passe** : Indicateur de force en temps réel avec suggestions
4. **Confirmation mot de passe** : Vérification automatique de correspondance

### Validation du formulaire
1. **Champs obligatoires** : Laissez vides → messages d'erreur
2. **Format email** : Email invalide → "Veuillez saisir une adresse email valide"
3. **Téléphone** : Format invalide → "Numéro de téléphone invalide"
4. **Conditions** : Non cochées → "Vous devez accepter les conditions d'utilisation"

### Envoi de fichiers
1. **Taille** : Fichier > 5MB → "Le fichier est trop volumineux"
2. **Format CV** : Fichier non PDF/DOC/DOCX → "Format de fichier non supporté"
3. **Affichage** : Nom et taille du fichier affichés après sélection

### États visuels
1. **Chargement** : Bouton avec spinner pendant soumission
2. **Erreurs** : Champs en rouge avec messages
3. **Succès** : Page de confirmation avec actions

## 5. Test des notifications

Après inscription, vérifiez :
1. **Email de bienvenue** envoyé à l'utilisateur
2. **Notification admin** (si configurée)
3. **Redirection** vers l'URL spécifiée

## 6. Vérification en base de données

### Tables à vérifier :
- `wp_users` : Nouvel utilisateur créé
- `wp_usermeta` : Métadonnées (rôle, infos supplémentaires)

### Champs meta candidat :
- `user_phone`
- `user_city`
- `job_title`
- `experience_level`
- `willing_to_relocate`
- `bio`

### Champs meta employeur :
- `company_name`
- `company_industry`
- `company_size`
- `company_website`
- `company_phone`
- `company_address`
- `company_city`
- `company_country`
- `company_description`

## 7. Tests de sécurité

1. **CSRF** : Tentative de soumission sans nonce → Échec
2. **Injection** : Tentative d'injection de code → Échappé/Sanitisé
3. **Upload** : Tentative d'upload de fichier malveillant → Bloqué

## 8. CSS responsive

Testez sur différentes tailles d'écran :
- **Desktop** (> 768px) : Formulaire 2 colonnes
- **Tablet** (768px) : Formulaire 1 colonne
- **Mobile** (< 480px) : Interface tactile optimisée

## 9. Hooks personnalisés

Testez les actions et filtres :

```php
// Dans functions.php de votre thème
add_action('sleeve_ke_new_employer_registered', function($user_id) {
    error_log('Nouvel employeur inscrit : ' . $user_id);
});

add_action('sleeve_ke_new_candidate_registered', function($user_id) {
    error_log('Nouveau candidat inscrit : ' . $user_id);
});

add_filter('sleeve_ke_registration_redirect_url', function($url, $user_type) {
    return $url . '?welcome=1';
}, 10, 2);
```

## 10. Debug et dépannage

### Log des erreurs :
Activez `WP_DEBUG` dans `wp-config.php` :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Console JavaScript :
Ouvrez les outils développeur pour voir les erreurs JS.

### Vérification AJAX :
Dans l'onglet Network, vérifiez les requêtes vers `admin-ajax.php`.

### Performance :
Les assets ne se chargent que sur les pages avec shortcodes.