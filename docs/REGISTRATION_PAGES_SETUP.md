# ✅ GUIDE RAPIDE : CRÉER LES PAGES D'INSCRIPTION

## 🎯 Pages à créer dans WordPress

### 📄 **PAGE 1 : Register as Employer**

**Dans WordPress : Pages → Ajouter**

| Champ | Valeur |
|-------|--------|
| **Titre** | `Register as Employer` |
| **Slug (Permalien)** | `employer-registration` ⚠️ **IMPORTANT** |
| **Contenu** | `[sleeve_ke_employer_registration]` |
| **Template** | Full Width (si disponible) |
| **Statut** | Publier ✅ |

---

### 📄 **PAGE 2 : Register as Candidate**

**Dans WordPress : Pages → Ajouter**

| Champ | Valeur |
|-------|--------|
| **Titre** | `Register as Candidate` |
| **Slug (Permalien)** | `candidate-registration` ⚠️ **IMPORTANT** |
| **Contenu** | `[sleeve_ke_candidate_registration]` |
| **Template** | Full Width (si disponible) |
| **Statut** | Publier ✅ |

---

### 📄 **PAGE 3 : Dashboard** (Si pas encore créée)

**Dans WordPress : Pages → Ajouter**

| Champ | Valeur |
|-------|--------|
| **Titre** | `Dashboard` |
| **Slug (Permalien)** | `dashboard` |
| **Contenu** | `[sleeve_ke_dashboard]` |
| **Template** | Full Width |
| **Statut** | Publier ✅ |

---

## 🎨 Nouveau Design du Dashboard

Le dashboard affiche maintenant un écran de connexion amélioré avec :

### ✨ **Fonctionnalités :**

1. **🔐 Icône de Sécurité** : Visuel attrayant
2. **Message Clair** : "Please login or register to view your dashboard"
3. **Bouton Login** : Grand bouton bleu avec icône
4. **Diviseur "OR"** : Séparation visuelle
5. **Deux Cartes d'Inscription** :
   - 🏢 **Employer** : "Post jobs & hire talent"
   - 👤 **Candidate** : "Find your dream job"

### 🎯 **Expérience Utilisateur :**

```
┌─────────────────────────────────────┐
│           🔐                        │
│   Welcome to Sleeve KE              │
│   Please login or register to       │
│   view your dashboard               │
│                                     │
│   [🔓 Log In]                       │
│                                     │
│   ────── OR ──────                  │
│                                     │
│   New to Sleeve KE? Create an      │
│   account:                         │
│                                     │
│   ┌──────────┐  ┌──────────┐       │
│   │    🏢    │  │    👤    │       │
│   │ Register │  │ Register │       │
│   │    as    │  │    as    │       │
│   │ Employer │  │Candidate │       │
│   │          │  │          │       │
│   │Post jobs │  │Find your │       │
│   │& hire    │  │dream job │       │
│   │talent    │  │          │       │
│   └──────────┘  └──────────┘       │
└─────────────────────────────────────┘
```

---

## ✅ **Checklist de Configuration**

- [ ] Page "employer-registration" créée avec shortcode
- [ ] Page "candidate-registration" créée avec shortcode
- [ ] Page "dashboard" créée avec shortcode
- [ ] Toutes les pages publiées (pas en brouillon)
- [ ] Permaliens régénérés (Réglages → Permaliens → Enregistrer)
- [ ] Test : Visiter `/dashboard` déconnecté
- [ ] Vérifier que les 3 options s'affichent (Login + 2 registrations)
- [ ] Test : Cliquer sur "Register as Employer" → redirige vers `/employer-registration`
- [ ] Test : Cliquer sur "Register as Candidate" → redirige vers `/candidate-registration`
- [ ] Test : S'inscrire et vérifier la connexion automatique

---

## 🔗 **URLs à Tester**

| Page | URL | Doit Afficher |
|------|-----|---------------|
| Dashboard | `/dashboard` | Écran de connexion (si déconnecté) |
| Employer Registration | `/employer-registration` | Formulaire d'inscription employeur |
| Candidate Registration | `/candidate-registration` | Formulaire d'inscription candidat |
| Jobs | `/jobs` | Liste des emplois |

---

## 🎯 **Flux Utilisateur**

### **Pour un Nouvel Employeur :**
```
1. Visite /dashboard
2. Voit "Please login or register to view your dashboard"
3. Clique sur "Register as Employer" 🏢
4. Remplit le formulaire
5. ✅ Connecté automatiquement
6. ✅ Redirigé vers le dashboard employeur
```

### **Pour un Nouveau Candidat :**
```
1. Visite /dashboard
2. Voit "Please login or register to view your dashboard"
3. Clique sur "Register as Candidate" 👤
4. Remplit le formulaire
5. ✅ Connecté automatiquement
6. ✅ Redirigé vers le dashboard candidat
```

### **Pour un Utilisateur Existant :**
```
1. Visite /dashboard
2. Voit "Please login or register to view your dashboard"
3. Clique sur "Log In" 🔓
4. Entre ses identifiants sur wp-login.php
5. ✅ Redirigé vers /dashboard
6. ✅ Dashboard affiché selon son rôle
```

---

## 🐛 **Dépannage**

### **❌ Problème : "Page not found" (404)**

**Solution :**
1. Allez dans **Réglages** → **Permaliens**
2. Cliquez sur **Enregistrer** (sans rien changer)
3. Cela régénère les règles de réécriture
4. Réessayez

### **❌ Problème : Shortcode s'affiche en texte**

**Solution :**
- Vérifiez que le plugin Sleeve KE est activé
- Allez dans **Extensions** → Activez "Sleeve KE"

### **❌ Problème : Boutons ne fonctionnent pas**

**Solution :**
- Vérifiez que les slugs sont exacts : `employer-registration` et `candidate-registration`
- Pas d'espaces, pas de majuscules
- Les pages doivent être publiées

### **❌ Problème : Design ne s'affiche pas correctement**

**Solution :**
- Videz le cache du navigateur (Ctrl + F5)
- Si vous utilisez un plugin de cache, videz-le
- Désactivez temporairement les autres plugins pour tester

---

## 🎨 **Personnalisation Optionnelle**

### **Ajouter un Logo**

Sur la page Dashboard, avant le shortcode :

```html
<div style="text-align:center; margin-bottom: 30px;">
    <img src="URL_DE_VOTRE_LOGO" alt="Sleeve KE" style="max-width: 200px;">
</div>

[sleeve_ke_dashboard]
```

### **Ajouter un Texte d'Accueil**

Sur les pages d'inscription :

```html
<div style="background: #f0f7ff; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
    <h3>🎉 Join Thousands of Professionals!</h3>
    <p>Create your account in less than 2 minutes.</p>
</div>

[sleeve_ke_employer_registration]
```

---

## 📊 **Métriques de Succès**

Après configuration, vous devriez voir :

- ✅ Taux de complétion d'inscription > 80%
- ✅ Temps moyen d'inscription < 3 minutes
- ✅ 0 erreur 404 sur les pages
- ✅ Design responsive sur mobile

---

**🎉 Si tout fonctionne, votre système d'inscription est prêt !**

**Version:** 1.0.0  
**Date:** Novembre 2025
