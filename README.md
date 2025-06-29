# Dolibarr Smart Variants 🚀

Module Dolibarr pour la gestion intelligente des variantes de produits - **Version 1.0.0**

![Dolibarr](https://img.shields.io/badge/Dolibarr-20.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-green.svg)
![License](https://img.shields.io/badge/License-GPL--3.0-orange.svg)

## 🎯 Problématique résolue

Le système natif de Dolibarr affiche **TOUTES** les variantes de **TOUS** les produits lors de l'ajout d'une ligne, créant une interface confuse et difficile à utiliser.

**Smart Variants** révolutionne cette approche en :
- ✅ Affichant uniquement les attributs du produit sélectionné
- ✅ Créant une interface de sélection intuitive
- ✅ Générant automatiquement les variantes manquantes
- ✅ S'intégrant parfaitement dans le workflow existant

## 🌟 Fonctionnalités

### Interface intelligente
- **Sélection contextuelle** : Seuls les attributs du produit choisi s'affichent
- **Interface moderne** : Design responsive et accessible
- **Feedback visuel** : Messages clairs et animations fluides

### Automatisation
- **Création automatique** : Les variantes inexistantes sont créées à la volée
- **Recherche optimisée** : Vérification rapide de l'existence des combinaisons
- **Cache intelligent** : Performance optimisée pour les gros catalogues

### Compatibilité
- **Dolibarr 20.0.0** : Testé et validé
- **Multi-contextes** : Commandes, devis, factures, achats
- **Multi-thèmes** : Compatible avec tous les thèmes Dolibarr

## 🔧 Installation

### 1. Téléchargement

```bash
cd /home/diamanti/www/doli/custom/
git clone https://github.com/Patochiz/dolibarr-smart-variants.git smartvariants
```

### 2. Permissions

```bash
chmod -R 644 /home/diamanti/www/doli/custom/smartvariants/
chmod 755 /home/diamanti/www/doli/custom/smartvariants/
```

### 3. Activation

1. Allez dans **Configuration → Modules/Applications**
2. Cherchez "SmartVariants" dans la section "Produits"  
3. Cliquez sur **"Activer"**

### 4. Test de l'installation

Accédez à : `https://diamant-industrie.com/doli/custom/smartvariants/test_installation.php`

Le script vérifiera automatiquement :
- ✅ Présence de tous les fichiers
- ✅ Configuration de la base de données  
- ✅ Permissions utilisateur
- ✅ Fonctionnalités AJAX

## ⚙️ Configuration

### Page d'administration

Accès : **Configuration → Modules/Applications → SmartVariants → ⚙️**

Options disponibles :
- **Mode debug** : Logs détaillés pour le débogage
- **Création automatique** : Génération des variantes manquantes
- **Affichage des références** : Montrer les références dans les sélecteurs
- **Durée du cache** : Performance des requêtes attributs

### Variables de configuration

```php
// Activation du debug
$conf->global->MAIN_SMARTVARIANTS_DEBUG = 1;

// Création automatique des variantes
$conf->global->SMARTVARIANTS_AUTO_CREATE = 1;

// Cache des attributs (secondes)
$conf->global->SMARTVARIANTS_CACHE_DURATION = 3600;
```

## 🚀 Utilisation

### 1. Créer un produit avec variantes

1. **Produit parent** : Créez votre produit principal
2. **Attributs** : Définissez vos attributs (Couleur, Taille, etc.)
3. **Valeurs** : Ajoutez les valeurs (Rouge, Bleu, S, M, L, etc.)
4. **Combinaisons** : Générez les variantes via l'interface Dolibarr

### 2. Utiliser dans une commande

1. **Nouvelle commande** : Créez une commande client
2. **Sélection produit** : Commencez à taper le nom du produit parent
3. **Interface Smart Variants** : L'interface apparaît automatiquement
4. **Sélection attributs** : Choisissez vos options (couleur, taille, etc.)
5. **Ajout automatique** : La variante est ajoutée à la commande

### 3. Exemple concret

```
Produit : T-Shirt Basic
├── Couleur : Rouge, Bleu, Vert  
├── Taille : S, M, L, XL
└── Résultat : 12 variantes possibles

Interface Smart Variants :
┌─────────────────────────────┐
│ Sélection de variante       │
├─────────────────────────────┤
│ Couleur : [Rouge ▼]         │
│ Taille  : [M ▼]             │
├─────────────────────────────┤
│ [Ajouter] [Annuler]         │
└─────────────────────────────┘
```

## 🛠️ Architecture technique

### Structure des fichiers

```
smartvariants/
├── core/
│   ├── modules/
│   │   └── modSmartVariants.class.php     # Descripteur module
│   └── hooks/
│       └── smartvariants.class.php        # Hooks Dolibarr
├── css/
│   └── smartvariants.css                  # Interface utilisateur
├── js/
│   └── product_selector.js                # Logique client
├── ajax/
│   ├── get_product_attributes.php         # Récupération attributs
│   └── create_or_find_variant.php         # Gestion variantes
├── lang/
│   └── fr_FR/
│       └── smartvariants.lang             # Traductions
├── admin.php                              # Configuration
└── test_installation.php                  # Tests automatiques
```

### Points d'intégration

- **Hooks utilisés** : `formAddObjectLine`, `formObjectOptions`
- **Contextes ciblés** : `ordercard`, `propalcard`, `invoicecard`
- **APIs Dolibarr** : Produits, Attributs, Combinaisons
- **Technologies** : PHP 7.4+, JavaScript ES6, CSS3

## 🐛 Débogage

### Logs système

Activez le mode debug et consultez :
- **Interface** : Configuration → Journal système
- **Fichier** : `/home/diamanti/www/doli/documents/dolibarr.log`

### Console navigateur

Ouvrez F12 et vérifiez :
```javascript
// Configuration chargée
console.log(window.smartVariantsConfig);

// Fonction disponible  
console.log(typeof initSmartVariantSelector);

// Erreurs AJAX
// Network tab pour voir les requêtes
```

### Tests AJAX

Test direct de l'endpoint :
```bash
curl -X POST "https://diamant-industrie.com/doli/custom/smartvariants/ajax/get_product_attributes.php" \
     -d "product_id=123&token=abc" \
     -H "Content-Type: application/x-www-form-urlencoded"
```

## 📋 Prérequis

### Serveur
- **Dolibarr** : 20.0.0+
- **PHP** : 7.4+ 
- **MySQL** : 5.7+
- **JavaScript** : ES6+

### Permissions Dolibarr
- **Produits** : Lecture/Écriture
- **Administration** : Activation modules (admin uniquement)

### Modules Dolibarr requis
- ✅ **Products** : Module produits de base
- ✅ **Product attributes** : Système de variantes natif

## 🔄 Compatibilité

### Versions testées
- ✅ **Dolibarr 20.0.0** : Entièrement compatible
- ✅ **OVH Mutualisé** : Testé en production
- ✅ **Mobile** : Interface responsive

### Navigateurs supportés
- ✅ Chrome 90+
- ✅ Firefox 88+  
- ✅ Safari 14+
- ✅ Edge 90+

## 🤝 Contribution

### Signaler un bug
1. Activez le mode debug
2. Reproduisez le problème
3. Créez une issue avec :
   - Version Dolibarr
   - Logs d'erreur
   - Étapes de reproduction

### Proposer une amélioration
1. Fork du repository
2. Créez une branche feature
3. Testez vos modifications
4. Créez une Pull Request

## 📄 Licence

Ce projet est sous licence **GPL-3.0** - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🙏 Remerciements

- **Équipe Dolibarr** : Pour l'excellent framework
- **Communauté** : Pour les retours et suggestions
- **OVH** : Pour l'hébergement de test

---

**💡 Questions ?** Ouvrez une [issue](https://github.com/Patochiz/dolibarr-smart-variants/issues) ou consultez la [documentation](https://github.com/Patochiz/dolibarr-smart-variants/wiki).

**🚀 Prêt à améliorer votre gestion de variantes ?** [Téléchargez Smart Variants maintenant !](https://github.com/Patochiz/dolibarr-smart-variants/releases)