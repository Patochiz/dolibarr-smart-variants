# Contexte technique - Dolibarr Smart Variants

## Environnement de déploiement

### Serveur OVH Mutualisé
- **Configuration Dolibarr** :
  - `dolibarr_main_url_root = https://diamant-industrie.com:443/doli`
  - `dolibarr_main_url_root_alt = /custom`
  - `dolibarr_main_document_root = /home/diamanti/www/doli`
  - `dolibarr_main_document_root_alt = /home/diamanti/www/doli/custom`
  - `dolibarr_main_data_root = /home/diamanti/www/doli/documents`

### Version Dolibarr
- **Version** : 20.0.0
- **Modules concernés** : Produits, Attributs de produits, Commandes, Devis

## Problématique initiale

### Système natif Dolibarr
- ✅ **Avantages** : Système de variantes fonctionnel
- ❌ **Problème** : Affichage global de TOUTES les variantes pour TOUS les produits
- ❌ **Impact** : Interface confuse avec beaucoup de produits

### Besoin utilisateur
- Sélectionner un produit parent dans une commande
- Voir uniquement les attributs DE CE PRODUIT
- Créer/sélectionner la variante rapidement
- Intégration transparente dans le workflow

## Architecture technique choisie

### Approche non-invasive
- **Pas de modification** de la structure BDD Dolibarr
- **Utilisation des hooks** pour s'intégrer proprement
- **Module custom** dans `/custom/smartvariants/`
- **Compatibilité** avec les mises à jour Dolibarr

### Technologies utilisées
- **PHP** : Hooks et logique serveur
- **JavaScript/jQuery** : Interface utilisateur dynamique
- **AJAX** : Communication asynchrone
- **CSS** : Intégration visuelle

## Flux de données

### Tables Dolibarr utilisées
```sql
-- Produits parents et enfants
llx_product

-- Attributs de produits (couleur, taille, etc.)
llx_product_attribute 

-- Valeurs d'attributs (rouge, bleu, S, M, L, etc.)
llx_product_attribute_value

-- Combinaisons de variantes
llx_product_attribute_combination

-- Liens combinaisons <-> valeurs
llx_product_attribute_combination_2_val
```

### Logique métier
1. **Détection** : Le produit sélectionné a-t-il des variantes ?
2. **Récupération** : Quels sont SES attributs spécifiques ?
3. **Affichage** : Interface de sélection dédiée
4. **Recherche** : Cette combinaison existe-t-elle ?
5. **Action** : Utiliser l'existante OU créer une nouvelle
6. **Intégration** : Ajouter à la commande

## Points d'intégration Dolibarr

### Hooks utilisés
- `formAddObjectLine` : Intercept ajout de ligne
- `printObjectLine` : Éventuellement pour l'affichage

### Contextes ciblés
- `ordercard` : Commandes clients
- `propalcard` : Devis/Propositions
- `invoicecard` : Factures
- `supplierproposalcard` : Commandes fournisseurs

### Fichiers Dolibarr impactés (lecture seule)
- `commande/card.php`
- `comm/propal/card.php`
- `compta/facture/card.php`
- `fourn/commande/card.php`

## Contraintes techniques

### Limitations serveur mutualisé
- Pas d'accès shell complet
- Respect des quotas de ressources
- Optimisation des requêtes SQL

### Compatibilité
- Respect de l'architecture Dolibarr
- Maintien de la compatibilité mobile
- Support des thèmes Dolibarr

## Sécurité

### Permissions
- Vérification des droits utilisateur
- Validation des données d'entrée
- Protection CSRF

### Validation
- Contrôle des ID produits
- Validation des attributs
- Vérification de l'existence des données
