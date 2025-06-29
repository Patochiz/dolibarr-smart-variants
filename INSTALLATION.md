# Guide d'installation - Dolibarr Smart Variants

## Prérequis

- **Dolibarr** : Version 20.0.0 ou supérieure
- **Modules activés** :
  - Produits et services
  - Attributs de produits
  - Au moins un module de vente (Commandes, Devis, etc.)

## Installation

### 1. Téléchargement du module

```bash
# Aller dans le répertoire custom de Dolibarr
cd /home/diamanti/www/doli/custom/

# Cloner le projet
git clone https://github.com/Patochiz/dolibarr-smart-variants.git smartvariants

# Ou télécharger et décompresser l'archive
wget https://github.com/Patochiz/dolibarr-smart-variants/archive/main.zip
unzip main.zip
mv dolibarr-smart-variants-main smartvariants
```

### 2. Vérification des permissions

```bash
# Donner les bonnes permissions
chown -R diamanti:diamanti /home/diamanti/www/doli/custom/smartvariants/
chmod -R 755 /home/diamanti/www/doli/custom/smartvariants/
```

### 3. Configuration Dolibarr

1. **Connexion admin** : Se connecter à Dolibarr avec un compte administrateur

2. **Activation du module** :
   - Aller dans `Configuration > Modules/Applications`
   - Chercher "Smart Variants" dans la liste
   - Cliquer sur "Activer"

3. **Configuration des permissions** :
   - Aller dans `Configuration > Utilisateurs & Groupes`
   - Éditer les groupes/utilisateurs concernés
   - Donner les permissions nécessaires :
     - Lire les produits
     - Créer/modifier les produits (pour la création automatique de variantes)

### 4. Test de l'installation

1. **Créer un produit parent** avec des attributs :
   - Aller dans `Produits/Services > Nouveau produit`
   - Créer un produit (ex: "T-shirt")
   - Dans `Attributs et variantes`, associer des attributs (Couleur, Taille)

2. **Tester dans une commande** :
   - Créer une nouvelle commande client
   - Sélectionner le produit parent
   - Vérifier que l'interface Smart Variants s'affiche
   - Sélectionner les attributs et ajouter à la commande

## Configuration avancée

### Variables de configuration

Ajouter dans `conf/conf.php` (optionnel) :

```php
// Debug mode pour Smart Variants
$dolibarr_main_smartvariants_debug = 1;

// Préfixe pour les références de variantes
$dolibarr_main_smartvariants_ref_prefix = 'VAR-';

// Mode de création automatique
$dolibarr_main_smartvariants_auto_create = 1;
```

### Personnalisation CSS

Pour adapter le style à votre thème :

```css
/* Dans custom/smartvariants/css/smartvariants.css */

/* Couleurs personnalisées */
.smart-variant-container {
    border-color: #votre-couleur;
    background-color: #votre-bg;
}

#add-variant-btn {
    background-color: #votre-couleur-principale;
}
```

## Dépannage

### Le module n'apparaît pas

1. Vérifier les permissions des fichiers
2. Vérifier que le répertoire est bien `custom/smartvariants/`
3. Vider le cache Dolibarr (`Configuration > Autre > Purger cache`)

### L'interface ne s'affiche pas

1. Vérifier que jQuery est chargé
2. Ouvrir la console du navigateur pour voir les erreurs
3. Vérifier les permissions AJAX (`ajax/` doit être accessible)

### Erreurs de création de variantes

1. Vérifier les permissions utilisateur sur les produits
2. Vérifier la configuration des attributs de produits
3. Activer le mode debug pour voir les logs

### Logs de débogage

Pour activer les logs détaillés :

```php
// Dans conf/conf.php
$dolibarr_main_smartvariants_debug = 1;

// Les logs apparaîtront dans :
// documents/dolibarr.log
```

## Mise à jour

```bash
# Aller dans le répertoire du module
cd /home/diamanti/www/doli/custom/smartvariants/

# Sauvegarder les modifications locales
git stash

# Mettre à jour
git pull origin main

# Restaurer les modifications si nécessaire
git stash pop
```

## Désinstallation

1. **Désactiver le module** dans Dolibarr
2. **Supprimer le répertoire** :
   ```bash
   rm -rf /home/diamanti/www/doli/custom/smartvariants/
   ```
3. **Nettoyer le cache** Dolibarr

## Support

En cas de problème :

1. Consulter les [Issues GitHub](https://github.com/Patochiz/dolibarr-smart-variants/issues)
2. Vérifier la documentation Dolibarr sur les modules custom
3. Créer une nouvelle issue avec :
   - Version de Dolibarr
   - Logs d'erreur
   - Configuration serveur
   - Étapes pour reproduire le problème
