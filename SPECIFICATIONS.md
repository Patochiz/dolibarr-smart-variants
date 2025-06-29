# Spécifications fonctionnelles - Dolibarr Smart Variants

## Contexte
**Problème actuel** : Dans Dolibarr 20.0.0, lors de la création de variantes, tous les attributs de tous les produits sont affichés, rendant l'interface confuse.

**Solution proposée** : Workflow intelligent pour la sélection de variantes par produit lors de l'ajout dans les documents (commandes, devis, factures).

## Workflow cible

### 1. Sélection du produit parent
- L'utilisateur sélectionne un produit dans une commande/devis
- Le système détecte si ce produit a des variantes configurées

### 2. Affichage des attributs spécifiques
- Si le produit a des variantes : affichage d'un sélecteur avec UNIQUEMENT ses attributs
- Si pas de variantes : comportement normal de Dolibarr

### 3. Création/sélection intelligente
- L'utilisateur saisit les valeurs d'attributs
- Le système cherche si cette combinaison existe déjà
- **Si existe** : ajoute la variante existante à la commande
- **Si n'existe pas** : crée automatiquement la variante puis l'ajoute

### 4. Intégration transparente
- La ligne est ajoutée au document comme une ligne normale
- L'interface se remet en mode standard

## Périmètre technique

### Modules Dolibarr concernés
- **Produits et services** (gestion des variantes)
- **Commandes clients**
- **Devis/Propositions commerciales**
- **Commandes fournisseurs**
- **Factures**

### Points d'intégration
- Hook `formAddObjectLine` pour intercepter l'ajout de lignes
- Interface JavaScript pour la sélection dynamique
- Scripts AJAX pour la logique métier
- Base de données existante (pas de modification de structure)

## Règles de gestion

### Création de variantes automatique
1. **Nomenclature** : REF_PARENT-ATTR1-ATTR2 (ex: PROD001-RED-L)
2. **Prix** : Hérite du parent + variation si configurée
3. **Stock** : Géré indépendamment
4. **Statut** : Active par défaut

### Gestion des conflits
- Si la variante existe avec des attributs différents : proposer la mise à jour
- Si erreur de création : message d'erreur explicite

### Permissions
- Respecter les droits Dolibarr existants
- Seuls les utilisateurs autorisés à créer des produits peuvent créer des variantes

## Interface utilisateur

### Écran de sélection
```
┌─────────────────────────────────────┐
│ Produit sélectionné : [PROD001]     │
│ ┌─ Attributs de variante ─────────┐ │
│ │ Couleur : [Rouge ▼]             │ │
│ │ Taille  : [L ▼]                 │ │
│ │ Matière : [Coton ▼]             │ │
│ └─────────────────────────────────┘ │
│ [Ajouter à la commande]             │
└─────────────────────────────────────┘
```

### États de l'interface
1. **Masqué** : Produit sans variante sélectionné
2. **Affiché** : Produit avec variantes sélectionné
3. **En cours** : Création/recherche de variante
4. **Succès** : Variante ajoutée, interface remise à zéro

## Critères de succès
- Réduction du temps de saisie des commandes avec variantes
- Diminution des erreurs de sélection
- Création automatique et cohérente des variantes
- Intégration transparente dans le workflow existant
