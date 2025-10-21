# Shortcode d'affichage des emplois - Sleeve KE

## 🎯 Shortcode principal

```
[sleeve_ke_jobs]
```

## 🔧 Options disponibles

### Disposition et affichage
```
[sleeve_ke_jobs 
    columns="3" 
    posts_per_page="12" 
    layout="grid"]
```

### Contrôles d'interface
```
[sleeve_ke_jobs 
    show_filters="true" 
    show_search="true" 
    show_pagination="true"]
```

### Affichage des informations
```
[sleeve_ke_jobs 
    show_company_logo="true" 
    show_salary="true" 
    show_date="true"]
```

### Filtres par défaut
```
[sleeve_ke_jobs 
    job_type="full-time" 
    location="Paris" 
    featured_only="true"]
```

### Tri et ordre
```
[sleeve_ke_jobs 
    orderby="date" 
    order="DESC"]
```

## 📋 Paramètres détaillés

| Paramètre | Valeur par défaut | Options | Description |
|-----------|-------------------|---------|-------------|
| `columns` | `3` | `1`, `2`, `3`, `4` | Nombre de colonnes en vue grille |
| `posts_per_page` | `12` | `1-50` | Nombre d'emplois par page |
| `layout` | `grid` | `grid`, `list` | Disposition initiale |
| `show_filters` | `true` | `true`, `false` | Afficher les filtres |
| `show_search` | `true` | `true`, `false` | Afficher la recherche |
| `show_pagination` | `true` | `true`, `false` | Afficher la pagination |
| `show_company_logo` | `true` | `true`, `false` | Afficher logo entreprise |
| `show_salary` | `true` | `true`, `false` | Afficher salaire |
| `show_date` | `true` | `true`, `false` | Afficher date de publication |
| `job_type` | ` ` | `full-time`, `part-time`, `contract`, `freelance`, `internship` | Filtrer par type d'emploi |
| `location` | ` ` | Texte libre | Filtrer par localisation |
| `category` | ` ` | Slug de catégorie | Filtrer par catégorie |
| `featured_only` | `false` | `true`, `false` | Emplois en vedette uniquement |
| `orderby` | `date` | `date`, `title`, `menu_order` | Critère de tri |
| `order` | `DESC` | `ASC`, `DESC` | Ordre de tri |

## 💡 Exemples d'utilisation

### 1. Grille simple 3 colonnes
```
[sleeve_ke_jobs]
```

### 2. Liste complète 1 colonne
```
[sleeve_ke_jobs columns="1" layout="list"]
```

### 3. Grille 4 colonnes, plus d'emplois
```
[sleeve_ke_jobs columns="4" posts_per_page="20"]
```

### 4. Emplois temps plein uniquement
```
[sleeve_ke_jobs job_type="full-time" featured_only="true"]
```

### 5. Page dédiée avec recherche seulement
```
[sleeve_ke_jobs show_filters="false" show_search="true" columns="2"]
```

### 6. Widget sidebar (vue compacte)
```
[sleeve_ke_jobs 
    columns="1" 
    posts_per_page="5" 
    show_filters="false" 
    show_pagination="false"
    layout="list"]
```

### 7. Page emplois par région
```
[sleeve_ke_jobs 
    location="Lyon" 
    columns="3" 
    posts_per_page="15"
    show_search="false"]
```

### 8. Emplois en vedette (homepage)
```
[sleeve_ke_jobs 
    featured_only="true" 
    columns="4" 
    posts_per_page="8"
    show_filters="false"
    show_pagination="false"]
```

## 🎨 Personnalisation CSS

### Classes principales
- `.sleeve-ke-jobs-container` : Container principal
- `.jobs-filters-section` : Section filtres et recherche
- `.jobs-grid` : Grille des emplois
- `.job-card` : Carte individuelle d'emploi
- `.layout-grid` / `.layout-list` : Classes de disposition

### Exemple de personnalisation
```css
/* Changer les couleurs de la grille */
.sleeve-ke-jobs-container .job-card {
    border-color: #your-color;
}

.sleeve-ke-jobs-container .btn-primary {
    background-color: #your-primary-color;
}

/* Modifier l'espacement */
.jobs-grid.columns-3 {
    gap: 30px;
}

/* Style pour les emplois en vedette */
.job-card.featured {
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    border-color: #ffd700;
}
```

## 🔍 Fonctionnalités de recherche

### Champs de recherche
- **Mot-clé** : Recherche dans titre, description, entreprise
- **Localisation** : Recherche par ville, région

### Filtres disponibles
- **Type d'emploi** : Temps plein, partiel, contrat, freelance, stage
- **Expérience** : Débutant, intermédiaire, senior, dirigeant
- **Salaire minimum** : Tranches prédéfinies
- **Télétravail** : Possible, hybride, présentiel uniquement
- **Date de publication** : Aujourd'hui, 7 jours, 30 jours

### Fonctionnalités avancées
- ✅ **Recherche en temps réel** avec debouncing
- ✅ **URL persistante** (partage de recherches)
- ✅ **Pagination AJAX** sans rechargement
- ✅ **Sauvegarde des préférences** (vue grille/liste)
- ✅ **Gestion d'état** avec historique navigateur

## 📱 Responsive design

### Points de rupture
- **Desktop** (> 992px) : Colonnes complètes
- **Tablet** (768-992px) : Réduction intelligente des colonnes
- **Mobile** (< 768px) : 1 colonne, interface tactile

### Adaptation automatique
```
4 colonnes → 3 colonnes (tablet) → 1 colonne (mobile)
3 colonnes → 2 colonnes (tablet) → 1 colonne (mobile)
2 colonnes → 1 colonne (mobile)
```

## ⚡ Performances

### Optimisations incluses
- **Chargement conditionnel** : Assets seulement sur pages avec shortcode
- **AJAX intelligent** : Rechargement partiel uniquement
- **Cache navigateur** : Préférences utilisateur sauvegardées
- **Images lazy loading** : Logos entreprises
- **Pagination efficace** : Limitation des requêtes

### Bonnes pratiques
```
[sleeve_ke_jobs posts_per_page="12"]  // ✅ Recommandé
[sleeve_ke_jobs posts_per_page="50"]  // ⚠️ Peut ralentir
[sleeve_ke_jobs posts_per_page="5"]   // ✅ Pour widgets
```

## 🎛️ Hooks et filtres

### Actions déclenchées
```php
// Après chargement des emplois
do_action('sleeve_ke_jobs_loaded', $jobs_data);

// Changement de disposition
do_action('sleeve_ke_layout_changed', $layout);

// Sauvegarde d'emploi
do_action('sleeve_ke_job_saved', $job_id, $user_id);
```

### Filtres disponibles
```php
// Modifier les arguments de requête
apply_filters('sleeve_ke_jobs_query_args', $args, $atts);

// Personnaliser l'affichage des cartes
apply_filters('sleeve_ke_job_card_html', $html, $job_id, $atts);

// Modifier les options de filtres
apply_filters('sleeve_ke_job_filter_options', $options, $filter_type);
```

### Exemples d'utilisation
```php
// Dans functions.php de votre thème
add_filter('sleeve_ke_jobs_query_args', function($args, $atts) {
    // Exclure certains emplois
    $args['meta_query'][] = array(
        'key' => 'exclude_from_listing',
        'compare' => 'NOT EXISTS'
    );
    return $args;
}, 10, 2);

add_action('sleeve_ke_job_saved', function($job_id, $user_id) {
    // Envoyer notification personnalisée
    wp_mail(
        get_userdata($user_id)->user_email,
        'Emploi sauvegardé',
        'Vous avez sauvegardé l\'emploi : ' . get_the_title($job_id)
    );
});
```

## 🚀 Intégration avec le thème

### Page dédiée emplois
```php
// page-emplois.php
get_header(); ?>

<div class="container">
    <h1>Offres d'emploi</h1>
    <p>Découvrez toutes nos opportunités professionnelles</p>
    
    <?php echo do_shortcode('[sleeve_ke_jobs columns="3" posts_per_page="18"]'); ?>
</div>

<?php get_footer();
```

### Widget emplois récents
```php
// Dans sidebar.php ou widget
echo do_shortcode('[sleeve_ke_jobs 
    columns="1" 
    posts_per_page="3" 
    show_filters="false" 
    show_pagination="false"
    orderby="date"]');
```

## 🔧 Dépannage

### Problèmes courants

**Shortcode ne s'affiche pas :**
- Vérifier que le plugin est activé
- Contrôler les permissions de fichiers
- Vérifier les erreurs PHP dans les logs

**Styles CSS non appliqués :**
- Vider le cache WordPress
- Vérifier les conflits avec le thème
- Contrôler l'ordre de chargement des styles

**AJAX ne fonctionne pas :**
- Vérifier que jQuery est chargé
- Contrôler la console développeur
- Vérifier les nonces et permissions

**Recherche lente :**
- Réduire `posts_per_page`
- Optimiser la base de données
- Utiliser un plugin de cache

### Debug mode
```php
// Activer le debug dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Vérifier les logs dans /wp-content/debug.log
```