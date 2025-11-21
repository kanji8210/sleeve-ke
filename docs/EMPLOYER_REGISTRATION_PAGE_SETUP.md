# 📄 GUIDE : CRÉER LA PAGE D'INSCRIPTION EMPLOYEUR

## ✅ Étapes pour créer la page d'inscription employeur

### 1️⃣ **Créer la page dans WordPress**

1. Allez dans votre dashboard WordPress
2. Cliquez sur **Pages** → **Ajouter**
3. Remplissez les informations suivantes :

**Titre de la page :**
```
Register as Employer
```

**Slug de la page (Permalien) :** 
```
employer-registration
```
⚠️ **IMPORTANT** : Le slug DOIT être exactement `employer-registration` car c'est ce qui est référencé dans le code du dashboard.

**Contenu de la page :**
```
[sleeve_ke_employer_registration]
```

**Template :** 
- Sélectionnez **Full Width** (si disponible dans votre thème)
- Ou laissez le template par défaut

4. Cliquez sur **Publier**

---

### 2️⃣ **Vérifier que la page fonctionne**

Visitez : `https://votresite.com/employer-registration`

Vous devriez voir :
- ✅ Formulaire d'inscription avec tous les champs
- ✅ Champs : Nom d'utilisateur, Email, Mot de passe, Nom de l'entreprise, etc.
- ✅ Bouton "Register as Employer"

---

### 3️⃣ **Créer également la page d'inscription candidat**

Répétez le processus pour les candidats :

**Titre :** `Register as Candidate`  
**Slug :** `candidate-registration`  
**Contenu :** `[sleeve_ke_candidate_registration]`

---

## 🔗 Liens depuis le Dashboard

Le dashboard `[sleeve_ke_dashboard]` a déjà des liens automatiques vers ces pages :

### **Pour les visiteurs non connectés :**
```php
// Dans class-sleeve-ke-dashboard.php ligne 63-67
<a href="<?php echo home_url( '/employer-registration' ); ?>">
    Register as Employer
</a>
<a href="<?php echo home_url( '/candidate-registration' ); ?>">
    Register as Candidate
</a>
```

Ces boutons apparaissent automatiquement quand un visiteur non connecté visite la page Dashboard.

---

## 🎯 Accès à la page d'inscription

### **Option 1 : Depuis le Dashboard**
1. Visiteur non connecté va sur `/dashboard`
2. Voit le message "Don't have an account?"
3. Clique sur "Register as Employer"
4. → Redirigé vers `/employer-registration`

### **Option 2 : Lien direct dans le menu**
Ajoutez la page au menu WordPress :
1. **Apparence** → **Menus**
2. Cochez "Register as Employer"
3. Ajoutez au menu
4. Sauvegardez

### **Option 3 : Widget ou Sidebar**
Ajoutez un lien HTML :
```html
<a href="/employer-registration" class="register-btn">
    Register Your Company
</a>
```

---

## 📋 Checklist Complète

- [ ] Page "Register as Employer" créée avec slug `employer-registration`
- [ ] Shortcode `[sleeve_ke_employer_registration]` ajouté
- [ ] Page publiée (pas en brouillon)
- [ ] Page testée en visitant l'URL
- [ ] Formulaire s'affiche correctement
- [ ] Page "Dashboard" créée avec `[sleeve_ke_dashboard]`
- [ ] Liens fonctionnent depuis le dashboard
- [ ] Page "Register as Candidate" créée (optionnel mais recommandé)

---

## 🎨 Personnalisation (Optionnel)

### **Ajouter un texte d'introduction**
Sur la page d'inscription, vous pouvez ajouter du texte AVANT le shortcode :

```
<h2>Join Sleeve KE as an Employer</h2>
<p>Post jobs, manage applications, and find the best talent in Kenya.</p>

[sleeve_ke_employer_registration]
```

### **Ajouter un bloc de bénéfices**
```html
<div style="margin-bottom: 30px;">
    <h3>Why Register?</h3>
    <ul>
        <li>✅ Post unlimited jobs</li>
        <li>✅ Access qualified candidates</li>
        <li>✅ Manage applications easily</li>
        <li>✅ Schedule interviews directly</li>
    </ul>
</div>

[sleeve_ke_employer_registration]
```

---

## 🐛 Dépannage

### **Problème : Page 404 Not Found**
**Solution :**
1. Allez dans **Réglages** → **Permaliens**
2. Cliquez sur **Enregistrer les modifications** (sans rien changer)
3. Cela régénère les règles de réécriture d'URL
4. Réessayez d'accéder à la page

### **Problème : Le shortcode s'affiche en texte**
**Solution :**
- Le plugin n'est pas activé
- Allez dans **Extensions** et activez "Sleeve KE"

### **Problème : Le formulaire ne s'affiche pas**
**Solution :**
1. Vérifiez que le shortcode est bien `[sleeve_ke_employer_registration]` (pas d'espace)
2. Vérifiez que vous êtes en mode "Visuel" et pas "HTML"
3. Essayez de publier et rafraîchir la page

### **Problème : Liens du dashboard ne fonctionnent pas**
**Solution :**
- Vérifiez que le slug est exactement `employer-registration`
- Vérifiez que la page est publiée (pas en brouillon)
- Videz le cache si vous utilisez un plugin de cache

---

## 📊 Pages Recommandées à Créer

| Titre | Slug | Shortcode |
|-------|------|-----------|
| **Dashboard** | `dashboard` | `[sleeve_ke_dashboard]` |
| **Register as Employer** | `employer-registration` | `[sleeve_ke_employer_registration]` |
| **Register as Candidate** | `candidate-registration` | `[sleeve_ke_candidate_registration]` |
| **Browse Jobs** | `jobs` | `[sleeve_ke_jobs]` |
| **Employer Profile** | `employer-profile` | `[sleeve_ke_employer_profile]` |
| **My Applications** | `employer-applications` | `[sleeve_ke_employer_applications]` |

---

## ✅ Test Final

1. **Déconnectez-vous** de WordPress (si connecté)
2. Visitez `/dashboard`
3. Vous devriez voir le message "Don't have an account?"
4. Cliquez sur **"Register as Employer"**
5. ✅ Vous devriez être redirigé vers `/employer-registration`
6. ✅ Le formulaire d'inscription devrait s'afficher
7. Testez l'inscription avec des données fictives
8. ✅ Après inscription, vous devriez être automatiquement connecté et redirigé

---

**Si tout fonctionne, la configuration est terminée ! 🎉**

**Version:** 1.0.0  
**Dernière mise à jour:** Novembre 2025
