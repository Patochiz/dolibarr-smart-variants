# Roadmap - Dolibarr Smart Variants

## Phase 1 : Foundation (En cours)

### ✅ Fait
- [x] Analyse du besoin et spécifications
- [x] Architecture technique
- [x] Structure du projet
- [x] Dépôt GitHub

### 🔄 En cours
- [ ] **Hook principal** (`core/hooks/smartvariants.class.php`)
  - Détection du contexte (commande, devis, etc.)
  - Injection de l'interface de sélection
  - Gestion des événements

- [ ] **Interface JavaScript** (`js/product_selector.js`)
  - Détection de sélection de produit
  - Affichage conditionnel du sélecteur
  - Validation des saisies

## Phase 2 : Core Logic

### Scripts AJAX
- [ ] **get_product_attributes.php**
  - Récupération des attributs d'un produit
  - Récupération des valeurs possibles
  - Gestion des cas d'erreur

- [ ] **create_or_find_variant.php**
  - Recherche de variante existante
  - Création automatique si nécessaire
  - Génération de références

### Base de données
- [ ] **Requêtes optimisées**
  - Récupération des attributs par produit
  - Recherche de combinaisons existantes
  - Création de nouvelles variantes

## Phase 3 : Interface & UX

### Interface utilisateur
- [ ] **CSS personnalisé** (`css/smartvariants.css`)
  - Style du sélecteur de variantes
  - Intégration avec le thème Dolibarr
  - Messages de feedback

- [ ] **Améliorations UX**
  - Messages de confirmation
  - Indicateurs de chargement
  - Gestion des erreurs utilisateur

## Phase 4 : Testing & Deployment

### Tests
- [ ] **Tests fonctionnels**
  - Sélection de produits avec/sans variantes
  - Création de nouvelles variantes
  - Réutilisation de variantes existantes

- [ ] **Tests d'intégration**
  - Commandes clients
  - Devis
  - Commandes fournisseurs

### Déploiement
- [ ] **Package final**
  - Documentation d'installation
  - Scripts de déploiement
  - Configuration par défaut

## Phase 5 : Améliorations futures

### Fonctionnalités avancées
- [ ] **Gestion des prix**
  - Calcul automatique des variations
  - Règles de prix par attribut

- [ ] **Gestion du stock**
  - Mise à jour automatique des stocks
  - Alertes de stock par variante

- [ ] **Import/Export**
  - Import en masse de variantes
  - Export des configurations

## Points de continuité pour les prochaines conversations

### Pour reprendre le développement :
1. **Mentionner** : "Je continue le projet dolibarr-smart-variants"
2. **Référencer** : Le dépôt GitHub https://github.com/Patochiz/dolibarr-smart-variants
3. **Préciser** : La phase en cours selon ce roadmap
4. **Contextualiser** : L'environnement OVH mutualisé + Dolibarr 20.0.0

### Informations clés à rappeler :
- **Objectif** : Sélection intelligente de variantes par produit
- **Workflow** : Parent → Attributs spécifiques → Création/sélection → Ajout
- **Architecture** : Hook + JavaScript + AJAX + pas de modif BDD
- **Périmètre** : Commandes, devis, factures (documents de vente/achat)

### Prochaines étapes prioritaires :
1. Implémentation du hook principal
2. Interface JavaScript de base
3. Premier script AJAX (get_product_attributes)
4. Tests sur l'environnement de dev
