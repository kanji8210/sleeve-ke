# SYSTÈME DE CONNEXION - SLEEVE KE PLUGIN

## 📚 Vue d'ensemble

Le plugin Sleeve KE utilise le système d'authentification natif de WordPress avec des rôles personnalisés et des redirections intelligentes.

---

## 🔐 COMMENT FONCTIONNE LA CONNEXION

### 1. **Inscription Automatique avec Connexion**

Quand un utilisateur s'inscrit (employeur ou candidat), il est **automatiquement connecté** :

```php
// Dans class-sleeve-ke-registration-forms.php (ligne 821-822)
wp_set_current_user($user_id);      // Définit l'utilisateur actuel
wp_set_auth_cookie($user_id);       // Crée le cookie d'authentification
```

**Processus :**
1. Utilisateur remplit le formulaire d'inscription
2. Le plugin crée le compte WordPress (`wp_insert_user()`)
3. Assigne le rôle approprié (`employer` ou `candidate`)
4. Crée l'entrée dans la table custom (`wp_sleeve_employers` ou `wp_sleeve_candidates`)
5. **Connecte automatiquement l'utilisateur** avec `wp_set_auth_cookie()`
6. Redirige vers le dashboard approprié

---

### 2. **Pages de Connexion**

Le plugin utilise la page de connexion native de WordPress (`wp-login.php`).

**Liens de connexion dans le plugin :**

```php
// Lien simple vers la page de connexion
wp_login_url()

// Lien avec redirection après connexion
wp_login_url( get_permalink() )  // Redirige vers la page actuelle après login
```

**Où trouver les liens de connexion :**

- **Dashboard** (`[sleeve_ke_dashboard]`) : Affiche un bouton "Log In" si l'utilisateur n'est pas connecté
- **Formulaires d'inscription** : "Already have an account? Login here"
- **Profil employeur** : Message "Please log in to view this page"

---

### 3. **Vérification de la Connexion**

Le plugin vérifie si l'utilisateur est connecté avec :

```php
if ( ! is_user_logged_in() ) {
    // Afficher message ou rediriger
    return 'Please log in to access this content';
}
```

**Pages protégées qui nécessitent une connexion :**

✅ `[sleeve_ke_dashboard]` - Dashboard universel  
✅ `[sleeve_ke_employer_profile]` - Profil employeur  
✅ `[sleeve_ke_employer_applications]` - Applications employeur  
❌ `[sleeve_ke_jobs]` - Liste des jobs (publique)  
❌ `[sleeve_ke_employer_registration]` - Inscription (publique)  
❌ `[sleeve_ke_candidate_registration]` - Inscription (publique)

---

### 4. **Rôles et Permissions**

Le plugin définit 3 rôles personnalisés :

#### 🏢 **EMPLOYER** (Employeur)
```php
if ( in_array( 'employer', $current_user->roles ) ) {
    // Afficher dashboard employeur
}
```

**Permissions :**
- Poster des jobs
- Gérer ses propres jobs
- Voir les applications à ses jobs
- Planifier des entretiens
- Ne peut PAS voir les infos de contact des candidats

#### 👤 **CANDIDATE** (Candidat)
```php
if ( in_array( 'candidate', $current_user->roles ) ) {
    // Afficher dashboard candidat
}
```

**Permissions :**
- Postuler aux jobs
- Voir ses propres applications
- Sauvegarder des jobs
- Gérer son profil

#### 👑 **SLEVE_ADMIN** (Super Admin)
```php
if ( in_array( 'sleve_admin', $current_user->roles ) || 
     in_array( 'administrator', $current_user->roles ) ) {
    // Accès complet
}
```

**Permissions :**
- Toutes les permissions
- Gérer tous les jobs, candidats, employeurs
- Accès au backend WordPress

---

## 🔄 FLUX DE CONNEXION

### **Scénario 1 : Nouvel Utilisateur (Inscription)**

```
1. Visiteur arrive sur /employer-registration ou /candidate-registration
   ↓
2. Remplit le formulaire d'inscription
   ↓
3. Plugin crée le compte WordPress
   ↓
4. Assigne le rôle (employer ou candidate)
   ↓
5. Crée l'entrée dans la table personnalisée
   ↓
6. ⚡ CONNEXION AUTOMATIQUE avec wp_set_auth_cookie()
   ↓
7. Redirection vers dashboard approprié
```

### **Scénario 2 : Utilisateur Existant (Connexion Manuelle)**

```
1. Utilisateur clique sur "Log In"
   ↓
2. Redirigé vers /wp-login.php
   ↓
3. Entre username/email + password
   ↓
4. WordPress vérifie les credentials
   ↓
5. Si succès : Session créée avec cookie
   ↓
6. Redirection vers la page spécifiée (ou dashboard)
   ↓
7. Le plugin détecte le rôle et affiche le bon dashboard
```

### **Scénario 3 : Accès à une Page Protégée**

```
1. Utilisateur visite /dashboard (sans être connecté)
   ↓
2. Plugin vérifie : is_user_logged_in()
   ↓
3. ❌ Non connecté
   ↓
4. Affiche message : "Please log in"
   ↓
5. Bouton "Log In" avec redirection vers page actuelle
   ↓
6. Après connexion → Retour à /dashboard
   ↓
7. ✅ Connecté → Dashboard affiché selon le rôle
```

---

## 🛠️ CONFIGURATION DE LA CONNEXION

### **Option 1 : Utiliser le Dashboard Universel (RECOMMANDÉ)**

Créez une page "Dashboard" avec le shortcode `[sleeve_ke_dashboard]` :

```
Titre : Dashboard
Slug : dashboard
Contenu : [sleeve_ke_dashboard]
Template : Full Width
```

**Avantages :**
- Détecte automatiquement le rôle
- Affiche le bon dashboard (employeur/candidat/admin)
- Affiche un formulaire de connexion si non connecté
- Pas besoin de pages séparées

### **Option 2 : Redirection Après Connexion**

Pour rediriger tous les utilisateurs vers le dashboard après connexion, ajoutez ce code dans `functions.php` de votre thème :

```php
add_filter('login_redirect', function($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        // Rediriger vers le dashboard universel
        return home_url('/dashboard');
    }
    return $redirect_to;
}, 10, 3);
```

### **Option 3 : Redirection Basée sur le Rôle**

Pour des redirections différentes selon le rôle :

```php
add_filter('login_redirect', function($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('employer', $user->roles)) {
            return home_url('/employer-dashboard');
        } elseif (in_array('candidate', $user->roles)) {
            return home_url('/candidate-dashboard');
        }
    }
    return $redirect_to;
}, 10, 3);
```

---

## 🔒 SÉCURITÉ

### **1. Cookies d'Authentification**

WordPress utilise des cookies sécurisés pour maintenir la session :

```php
wp_set_auth_cookie($user_id);  // Crée le cookie d'authentification
```

**Cookies créés :**
- `wordpress_logged_in_[hash]` - Cookie de session
- `wordpress_[hash]` - Cookie d'authentification
- Durée : 14 jours par défaut (ou durée de session)

### **2. Nonces pour la Sécurité**

Tous les formulaires utilisent des nonces WordPress :

```php
wp_nonce_field('sleeve_ke_register_employer', 'sleeve_employer_nonce');
wp_verify_nonce($_POST['sleeve_employer_nonce'], 'sleeve_ke_register_employer');
```

### **3. Vérification des Permissions**

Avant d'afficher du contenu sensible :

```php
// Vérifier si connecté
if ( ! is_user_logged_in() ) {
    return 'Access denied';
}

// Vérifier le rôle
$current_user = wp_get_current_user();
if ( ! in_array('employer', $current_user->roles) ) {
    return 'Only employers can access this';
}
```

---

## 📱 URLS IMPORTANTES

### **Pages de Connexion/Déconnexion**

```php
// Page de connexion
wp_login_url()
// Exemple : https://yoursite.com/wp-login.php

// Page de connexion avec redirection
wp_login_url( home_url('/dashboard') )
// Redirige vers /dashboard après connexion

// Page de déconnexion
wp_logout_url()
// Exemple : https://yoursite.com/wp-login.php?action=logout

// Déconnexion avec redirection
wp_logout_url( home_url() )
// Redirige vers la home page après déconnexion
```

### **Pages Personnalisées**

Vous pouvez créer une page de connexion personnalisée en utilisant :

```php
// Shortcode personnalisé (à créer)
[sleeve_ke_login_form]

// Ou utiliser un plugin comme "Theme My Login"
```

---

## 🎯 ÉTAPES POUR TESTER

### **Test 1 : Inscription et Auto-Login**

1. Allez sur `/employer-registration`
2. Remplissez le formulaire
3. Cliquez sur "Register"
4. ✅ Vous devriez être **automatiquement connecté**
5. ✅ Redirigé vers le dashboard employeur

### **Test 2 : Connexion Manuelle**

1. Déconnectez-vous (si connecté)
2. Allez sur `/dashboard`
3. Cliquez sur "Log In"
4. Entrez vos identifiants sur `/wp-login.php`
5. ✅ Après connexion, retour sur `/dashboard`
6. ✅ Dashboard affiché selon votre rôle

### **Test 3 : Accès Protégé**

1. Déconnectez-vous
2. Essayez d'accéder à `/employer-applications`
3. ✅ Message "Access denied" ou "Please log in"
4. Connectez-vous avec un compte candidat
5. ✅ Message "Only employers can access"
6. Connectez-vous avec un compte employeur
7. ✅ Dashboard des applications affiché

---

## 🐛 DÉPANNAGE

### **Problème : "Pas connecté après l'inscription"**

**Causes possibles :**
- Cookies bloqués par le navigateur
- Plugin de cache qui empêche les cookies
- Conflit avec un autre plugin de sécurité

**Solution :**
```php
// Vérifier dans class-sleeve-ke-registration-forms.php ligne 821-822
wp_set_current_user($user_id);
wp_set_auth_cookie($user_id);  // Cette ligne crée le cookie
```

### **Problème : "Redirection vers mauvaise page après login"**

**Causes possibles :**
- Autre plugin modifie la redirection
- Theme modifie la redirection

**Solution :**
```php
// Ajouter dans functions.php avec priorité élevée
add_filter('login_redirect', function($redirect_to, $request, $user) {
    return home_url('/dashboard');
}, 99, 3);
```

### **Problème : "Session expirée trop rapidement"**

**Solution :**
```php
// Prolonger la durée du cookie (dans functions.php)
add_filter('auth_cookie_expiration', function($length) {
    return 30 * DAY_IN_SECONDS; // 30 jours au lieu de 14
}, 10, 1);
```

---

## 📊 RÉSUMÉ TECHNIQUE

| Fonctionnalité | Méthode WordPress | Fichier Plugin |
|----------------|-------------------|----------------|
| **Auto-login après inscription** | `wp_set_auth_cookie()` | `class-sleeve-ke-registration-forms.php:822` |
| **Vérifier si connecté** | `is_user_logged_in()` | Tous les shortcodes protégés |
| **Obtenir utilisateur actuel** | `wp_get_current_user()` | `class-sleeve-ke-dashboard.php:30` |
| **URL de connexion** | `wp_login_url()` | `class-sleeve-ke-dashboard.php:56` |
| **Vérifier le rôle** | `in_array('employer', $user->roles)` | Tous les dashboards |
| **Déconnexion** | `wp_logout_url()` | Menu WordPress natif |

---

## 🎓 RESSOURCES

- [WordPress Authentication API](https://developer.wordpress.org/plugins/users/authentication/)
- [WordPress Cookie Functions](https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/)
- [WordPress User Roles](https://wordpress.org/support/article/roles-and-capabilities/)

---

**Version:** 1.0.0  
**Dernière mise à jour:** Novembre 2025
