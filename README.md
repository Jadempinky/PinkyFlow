
# PinkyFlow

PinkyFlow est un framework PHP modulaire conçu pour fournir une base flexible et facile à utiliser pour la création d'applications web. Il offre une gamme de modules, notamment l'authentification des utilisateurs, la gestion des paniers d'achats, les commentaires et les avis, et bien plus encore. Le framework est conçu pour être facilement configurable, même pour les utilisateurs ayant peu d'expérience en codage.

## Fonctionnalités

- **Conception modulaire** : Activez ou désactivez les modules selon vos besoins via un simple fichier de configuration.
- **Authentification utilisateur** : Inscription, connexion et gestion des sessions utilisateur.
- **Module de Shopping** : Gestion des produits, panier d'achat, liste de souhaits et favoris.
- **Module de Commentaires** : Ajoutez des commentaires et des avis sur les produits.
- **Configuration facile** : Un fichier `config.php` simple pour ajuster les paramètres sans toucher au code principal.
- **Configuration automatique** : Crée automatiquement les fichiers de configuration et les tables de base de données nécessaires.

## Table des Matières

- [Exigences](#exigences)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
  - [Initialisation du Framework](#initialisation-du-framework)
  - [Inscription d'un utilisateur](#inscription-dun-utilisateur)
  - [Gestion des produits](#gestion-des-produits)
  - [Commentaires et avis](#commentaires-et-avis)
- [Contribuer](#contribuer)
- [Licence](#licence)

## Exigences

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur (ou base de données compatible)
- Serveur web (Apache, Nginx, etc.)
- Composer (recommandé pour la gestion des dépendances et le chargement automatique)

## Installation

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/votrenomutilisateur/PinkyFlow.git
   ```

2. **Naviguer dans le répertoire du projet**

   ```bash
   cd PinkyFlow
   ```

3. **Installer les dépendances (optionnel mais recommandé)**

   Si vous utilisez Composer pour la gestion des dépendances :

   ```bash
   composer install
   ```

4. **Configurer le serveur web**

   Configurez votre serveur web pour qu'il serve le répertoire du projet. Assurez-vous que le répertoire racine du document est configuré correctement.

## Configuration

PinkyFlow utilise un fichier `config.php` pour les paramètres de configuration. Le framework génère automatiquement ce fichier s'il n'existe pas.

1. **Générer le fichier `config.php`**

   Le framework créera un fichier `config.php` avec les paramètres par défaut lors de la première exécution. Vous pouvez également le créer manuellement :

   ```php
   <?php
   // config.php

   // Variables de configuration
   $enableDatabase = true;       // Activer ou désactiver le module de base de données
   $enableUserModule = true;     // Activer ou désactiver l'authentification des utilisateurs
   $enableShoppingModule = true; // Activer ou désactiver les fonctionnalités de shopping
   $enableCommentModule = true;  // Activer ou désactiver le système de commentaires

   // Informations d'identification de la base de données
   $dbHost = 'localhost'; // Hôte de la base de données
   $dbUser = 'root';      // Nom d'utilisateur de la base de données
   $dbPass = '';          // Mot de passe de la base de données
   $dbName = 'pinkyflow'; // Nom de la base de données

   ?>
   ```

2. **Modifier le fichier `config.php`**

   Ouvrez le fichier `config.php` dans un éditeur de texte et ajustez les paramètres selon vos besoins. Assurez-vous de définir correctement les informations d'identification de votre base de données.

## Utilisation

### Initialisation du Framework

Incluez le script d'initialisation dans vos fichiers PHP pour accéder aux fonctionnalités du framework.

```php
<?php
// Inclure le fichier principal PinkyFlow
require_once 'path/to/PinkyFlow.php';

// Charger les objets PinkyFlow
$PinkyFlowObjects = pinkyflow_load_objects();

// Accéder aux objets initialisés
$db = $PinkyFlowObjects['PinkyFlowDB'] ?? null;
$user = $PinkyFlowObjects['PinkyFlowUser'] ?? null;
$shop = $PinkyFlowObjects['PinkyFlowShop'] ?? null;
$comment = $PinkyFlowObjects['PinkyFlowComment'] ?? null;
?>
```

### Inscription d'un utilisateur

Pour inscrire un nouvel utilisateur :

```php
<?php
if ($user) {
    try {
        $username = 'newuser';
        $password = 'securepassword123';
        $user->register($username, $password);
        echo 'Utilisateur inscrit avec succès !';
    } catch (Exception $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
?>
```

### Gestion des produits

Ajouter un nouveau produit :

```php
<?php
if ($shop) {
    $productData = [
        'name' => 'Produit Exemple',
        'description' => 'Ceci est un produit exemple.',
        'price' => 19.99,
        // Ajouter d'autres champs de produit si nécessaire
    ];
    $shop->addProduct($productData);
    echo 'Produit ajouté avec succès !';
}
?>
```

### Commentaires et avis

Ajouter un commentaire et une note à un produit :

```php
<?php
if ($comment && $user && $user->isLoggedIn()) {
    $productId = 'produit123';
    $commentText = 'Super produit !';
    $rating = 5;
    try {
        $comment->addComment($productId, $commentText, null, $rating);
        echo 'Commentaire ajouté avec succès !';
    } catch (Exception $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
?>
```

## Contribuer

Les contributions sont les bienvenues ! Suivez ces étapes :

1. Forker le dépôt.
2. Créer une nouvelle branche pour votre fonctionnalité ou correction de bug.
3. Apportez vos modifications et validez-les avec des messages clairs.
4. Soumettez une pull request sur la branche `main`.

Assurez-vous que votre code suit les normes de codage du projet et inclut des tests appropriés.

## Licence

Ce projet est sous licence [MIT License](LICENSE).

---

*Remarque : Remplacez `votrenomutilisateur` dans l'URL de clonage Git par votre nom d'utilisateur GitHub. Assurez-vous de mettre à jour tous les chemins ou espaces réservés avec les valeurs réelles de votre projet.*
