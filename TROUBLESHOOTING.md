# 🚨 Guide de débogage rapide - SmartVariants

## Problèmes identifiés lors de votre test

### ❌ **Problème 1 : Table manquante**
- **Table :** `llx_product_attribute_combination_2_val`
- **Impact :** Empêche la liaison entre combinaisons et valeurs d'attributs

### ❌ **Problème 2 : URL configuration incorrecte** 
- **Erreur 404 :** `/smartvariants/admin/admin.php` au lieu de `/custom/smartvariants/admin.php`
- **Impact :** Page de configuration inaccessible

## 🛠️ Solutions rapides

### **Solution automatique (Recommandée)**

1. **Mettre à jour les fichiers depuis GitHub :**
```bash
cd /home/diamanti/www/doli/custom/smartvariants
git pull origin main
```

2. **Lancer la réparation automatique :**
```
https://diamant-industrie.com/doli/custom/smartvariants/repair.php
```

3. **Suivre les instructions du script de réparation**

### **Solution manuelle**

Si la solution automatique ne fonctionne pas :

#### Étape 1 : Créer la table manquante

```sql
CREATE TABLE IF NOT EXISTS llx_product_attribute_combination_2_val (
    fk_prod_combination int(11) NOT NULL,
    fk_prod_attr int(11) NOT NULL,
    fk_prod_attr_val int(11) NOT NULL,
    KEY fk_prod_combination (fk_prod_combination),
    KEY fk_prod_attr (fk_prod_attr),
    KEY fk_prod_attr_val (fk_prod_attr_val)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### Étape 2 : Réinitialiser le module

1. **Configuration → Modules/Applications**
2. **Désactiver** SmartVariants
3. **Réactiver** SmartVariants
4. **Tester** la page de configuration

#### Étape 3 : Vérifier le module Variants

1. **Configuration → Modules/Applications**
2. Chercher **"Variants"** ou **"Variantes"**
3. **Activer** si nécessaire

## 🧪 Scripts de diagnostic disponibles

### **Test complet :**
```
https://diamant-industrie.com/doli/custom/smartvariants/test_installation.php
```

### **Diagnostic détaillé :**
```
https://diamant-industrie.com/doli/custom/smartvariants/diagnostic_table.php
```

### **Réparation automatique :**
```
https://diamant-industrie.com/doli/custom/smartvariants/repair.php
```

## ✅ Vérification finale

Après avoir appliqué les corrections :

1. **Page de configuration accessible :**
   - Configuration → SmartVariants → ⚙️
   - URL doit être : `/custom/smartvariants/admin.php`

2. **Test complet réussi :**
   - 16/16 tests passés
   - Aucune erreur

3. **Test fonctionnel :**
   - Créer une commande
   - Sélectionner un produit avec variantes
   - Vérifier que l'interface SmartVariants apparaît

## 🆘 Si les problèmes persistent

### Logs à consulter :
- **Configuration → Journal système**
- **Console navigateur (F12)**
- **Fichier :** `/home/diamanti/www/doli/documents/dolibarr.log`

### Informations à fournir :
- Version exacte de Dolibarr
- Résultat du script `diagnostic_table.php`
- Messages d'erreur des logs
- Capture d'écran des erreurs

### Contact :
- **GitHub Issues :** https://github.com/Patochiz/dolibarr-smart-variants/issues
- Incluez les logs et résultats de diagnostic

## 🎯 Résumé des actions

1. ✅ **git pull** pour mettre à jour
2. ✅ **repair.php** pour la réparation automatique
3. ✅ **test_installation.php** pour vérifier
4. ✅ Tester la page de configuration
5. ✅ Tester avec un produit à variantes

**Temps estimé :** 5-10 minutes