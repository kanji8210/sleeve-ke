# Exemples pratiques - Shortcode emplois Sleeve KE

## 🏠 Page d'accueil - Emplois en vedette

**Page :** `Accueil`
**Position :** Section "Opportunités d'emploi"
**Code :**

```html
<section class="featured-jobs">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 30px;">Emplois en vedette</h2>
        <p style="text-align: center; color: #666; margin-bottom: 40px;">
            Découvrez les meilleures opportunités sélectionnées par nos experts
        </p>
        
        [sleeve_ke_jobs 
            featured_only="true" 
            columns="4" 
            posts_per_page="8"
            show_filters="false"
            show_search="false"
            show_pagination="false"]
            
        <div style="text-align: center; margin-top: 30px;">
            <a href="/emplois" class="btn btn-primary">Voir tous les emplois</a>
        </div>
    </div>
</section>
```

## 💼 Page principale des emplois

**Page :** `Emplois` (slug: `/emplois`)
**Template :** Page complète
**Code :**

```html
<div class="jobs-page">
    <div class="page-header" style="text-align: center; padding: 40px 0; background: #f8f9fa;">
        <h1>Offres d'emploi</h1>
        <p style="font-size: 18px; color: #666; max-width: 600px; margin: 0 auto;">
            Explorez plus de 1000 offres d'emploi dans tous les secteurs. 
            Utilisez nos filtres pour trouver l'opportunité parfaite.
        </p>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        [sleeve_ke_jobs 
            columns="3" 
            posts_per_page="18" 
            show_filters="true" 
            show_search="true" 
            show_pagination="true"
            layout="grid"]
    </div>
</div>
```

## 🏢 Page par secteur d'activité

**Page :** `Emplois Informatique` (slug: `/emplois-informatique`)
**Code :**

```html
<div class="sector-jobs">
    <div class="page-header" style="background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%); color: white; padding: 50px 0; text-align: center;">
        <h1>Emplois en Informatique</h1>
        <p style="font-size: 18px; opacity: 0.9; max-width: 600px; margin: 0 auto;">
            Développement, cybersécurité, data science... Trouvez votre prochain défi tech !
        </p>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        [sleeve_ke_jobs 
            category="informatique" 
            columns="3" 
            posts_per_page="15"
            show_search="true"
            show_filters="true"]
    </div>
</div>
```

## 🌍 Page par région

**Page :** `Emplois à Lyon` (slug: `/emplois-lyon`)
**Code :**

```html
<div class="location-jobs">
    <div class="location-banner" style="background: url('/wp-content/uploads/lyon-banner.jpg') center/cover; height: 300px; position: relative;">
        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
            <div>
                <h1 style="font-size: 48px; margin: 0 0 15px 0;">Emplois à Lyon</h1>
                <p style="font-size: 20px; margin: 0;">
                    🎯 Plus de 200 offres d'emploi dans la région lyonnaise
                </p>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        [sleeve_ke_jobs 
            location="Lyon" 
            columns="3" 
            posts_per_page="15"
            show_search="false"
            show_filters="true"]
    </div>
</div>
```

## 📱 Widget sidebar - Emplois récents

**Widget :** Sidebar droite
**Titre :** "Dernières offres"
**Code :**

```html
<div class="recent-jobs-widget">
    <h3 style="margin-bottom: 20px;">📋 Dernières offres</h3>
    
    [sleeve_ke_jobs 
        columns="1" 
        posts_per_page="5" 
        show_filters="false" 
        show_search="false"
        show_pagination="false"
        show_company_logo="false"
        layout="list"
        orderby="date"
        order="DESC"]
        
    <div style="text-align: center; margin-top: 15px;">
        <a href="/emplois" style="font-size: 14px; color: #0073aa;">Voir toutes les offres →</a>
    </div>
</div>
```

## 💻 Page emplois télétravail

**Page :** `Télétravail` (slug: `/teletravail`)
**Code :**

```html
<div class="remote-jobs">
    <div class="page-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 50px 0; text-align: center;">
        <h1>💻 Emplois en télétravail</h1>
        <p style="font-size: 18px; opacity: 0.9; max-width: 700px; margin: 0 auto;">
            Travaillez depuis n'importe où ! Découvrez des opportunités 100% remote ou en mode hybride.
        </p>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        <!-- Onglets télétravail -->
        <div class="remote-tabs" style="display: flex; justify-content: center; margin-bottom: 30px; gap: 15px;">
            <button class="tab-btn active" data-remote="yes" style="padding: 10px 20px; border: 2px solid #28a745; background: #28a745; color: white; border-radius: 5px;">
                100% Télétravail
            </button>
            <button class="tab-btn" data-remote="hybrid" style="padding: 10px 20px; border: 2px solid #28a745; background: white; color: #28a745; border-radius: 5px;">
                Hybride
            </button>
        </div>
        
        <div id="remote-100">
            [sleeve_ke_jobs 
                remote_work="yes" 
                columns="3" 
                posts_per_page="12"
                show_filters="true"]
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Logique pour changer les filtres (nécessite JavaScript personnalisé)
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
```

## 🎓 Page emplois débutants

**Page :** `Emplois Débutants` (slug: `/emplois-debutants`)
**Code :**

```html
<div class="entry-level-jobs">
    <div class="page-header" style="background: #f8f9fa; padding: 40px 0; text-align: center;">
        <h1>🚀 Emplois pour débutants</h1>
        <p style="font-size: 18px; color: #666; max-width: 700px; margin: 0 auto;">
            Commencez votre carrière avec des opportunités conçues pour les profils juniors et en reconversion.
        </p>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        <!-- Section conseils -->
        <div class="tips-section" style="background: #e3f2fd; padding: 30px; border-radius: 8px; margin-bottom: 40px;">
            <h3>💡 Conseils pour postuler</h3>
            <ul style="margin: 15px 0;">
                <li>Mettez en avant vos projets personnels et formations</li>
                <li>Montrez votre motivation et votre capacité d'apprentissage</li>
                <li>N'hésitez pas à postuler même sans expérience directe</li>
            </ul>
        </div>
        
        [sleeve_ke_jobs 
            experience_level="entry" 
            columns="3" 
            posts_per_page="15"
            show_filters="true"
            job_type="full-time,part-time,internship"]
    </div>
</div>
```

## 📊 Dashboard employeur - Mes offres

**Page :** Dashboard employeur privé
**Code :**

```html
<div class="employer-dashboard">
    <h2>Mes offres d'emploi</h2>
    
    <div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background: #fff; border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="color: #28a745; font-size: 32px; margin: 0;">12</h3>
            <p style="margin: 5px 0 0 0; color: #666;">Offres actives</p>
        </div>
        <div class="stat-card" style="background: #fff; border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="color: #007bff; font-size: 32px; margin: 0;">148</h3>
            <p style="margin: 5px 0 0 0; color: #666;">Candidatures reçues</p>
        </div>
    </div>
    
    <!-- Affichage des offres de l'employeur connecté -->
    [sleeve_ke_jobs 
        columns="2" 
        posts_per_page="10" 
        show_filters="false"
        show_search="true"
        author="current_user"
        layout="list"]
</div>
```

## 🏆 Page emplois premium/featured

**Page :** `Emplois Premium` (slug: `/emplois-premium`)
**Code :**

```html
<div class="premium-jobs">
    <div class="premium-header" style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 50px 0; text-align: center;">
        <h1 style="color: #333;">⭐ Emplois Premium</h1>
        <p style="font-size: 18px; color: #666; max-width: 600px; margin: 0 auto;">
            Découvrez des opportunités exclusives sélectionnées par nos partenaires premium
        </p>
    </div>
    
    <div class="container" style="padding: 40px 20px;">
        <div class="premium-benefits" style="background: #fff9e6; border: 2px solid #ffd700; border-radius: 8px; padding: 30px; margin-bottom: 40px;">
            <h3>✨ Avantages des emplois premium</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <strong>🚀 Processus accéléré</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Réponse garantie sous 48h</p>
                </div>
                <div>
                    <strong>💰 Salaires attractifs</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Packages compétitifs</p>
                </div>
                <div>
                    <strong>🏢 Entreprises reconnues</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Partenaires certifiés</p>
                </div>
            </div>
        </div>
        
        [sleeve_ke_jobs 
            featured_only="true" 
            columns="3" 
            posts_per_page="12"
            show_filters="true"
            show_salary="true"
            orderby="menu_order"]
    </div>
</div>
```

## 📱 Page responsive mobile-first

**Page :** Optimisée mobile
**Code :**

```html
<div class="mobile-jobs">
    <!-- Header mobile optimisé -->
    <div class="mobile-header" style="padding: 20px; background: #f8f9fa; text-align: center;">
        <h1 style="font-size: 24px; margin: 0 0 10px 0;">Emplois</h1>
        <p style="font-size: 14px; color: #666; margin: 0;">Trouvez votre emploi idéal</p>
    </div>
    
    <!-- Vue optimisée pour mobile -->
    <div style="padding: 20px;">
        [sleeve_ke_jobs 
            columns="1" 
            posts_per_page="10" 
            layout="list"
            show_company_logo="true"
            show_filters="true"]
    </div>
</div>
```

## 🔗 Integration avec menu WordPress

**Menu :** Navigation principale
**Structure suggérée :**

```
Emplois (Page principale: /emplois)
├── Par secteur
│   ├── Informatique (/emplois-informatique)
│   ├── Marketing (/emplois-marketing)
│   └── Vente (/emplois-vente)
├── Par région
│   ├── Paris (/emplois-paris)
│   ├── Lyon (/emplois-lyon)
│   └── Marseille (/emplois-marseille)
├── Télétravail (/teletravail)
└── Débutants (/emplois-debutants)
```

Chaque page utilise le shortcode `[sleeve_ke_jobs]` avec des paramètres spécifiques pour filtrer le contenu approprié ! 🎯