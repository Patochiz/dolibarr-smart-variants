# Dolibarr Smart Variants

## Description
Module Dolibarr pour la gestion intelligente des variantes de produits par produit.

**Problème résolu** : Au lieu d'afficher toutes les variantes globalement, ce module permet de :
- Sélectionner un produit parent dans une commande/devis
- Afficher uniquement SES attributs de variantes
- Créer automatiquement une variante si elle n'existe pas
- Ajouter directement la variante au document

## Environnement
- **Dolibarr** : Version 20.0.0
- **Serveur** : OVH Mutualisé
- **Configuration** :
  - dolibarr_main_url_root = https://diamant-industrie.com/doli
  - dolibarr_main_document_root = /home/diamanti/www/doli
  - dolibarr_main_data_root = /home/diamanti/www/doli/documents

## Structure du projet
```
custom/smartvariants/
├── README.md
├── SPECIFICATIONS.md
├── ROADMAP.md
├── core/
│   └── hooks/
│       └── smartvariants.class.php
├── js/
│   └── product_selector.js
├── css/
│   └── smartvariants.css
├── ajax/
│   ├── get_product_attributes.php
│   └── create_or_find_variant.php
├── sql/
│   └── install.sql
└── conf/
    └── conf.php
```

## Installation
1. Cloner le projet dans `/home/diamanti/www/doli/custom/smartvariants/`
2. Activer le module dans Configuration > Modules
3. Configurer les paramètres si nécessaire

## Status du développement
- [x] Spécifications fonctionnelles
- [x] Architecture technique
- [ ] Implémentation des hooks
- [ ] Interface JavaScript
- [ ] Scripts AJAX
- [ ] Tests et validation

## Contact
Projet initié via Claude AI pour améliorer la gestion des variantes dans Dolibarr.
