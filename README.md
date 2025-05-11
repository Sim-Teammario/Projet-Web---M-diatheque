# 📚 Système de Gestion de Médiathèque

## 🚀 Présentation du Projet
Ce projet est un système complet de gestion de médiathèque développé selon une architecture client-serveur. Il comprend :

- **🖥️ Application Web Médiathèque** - Interface utilisateur permettant de gérer les médias, utilisateurs et emprunts.
- **🔗 API REST** - Backend fournissant les services de données pour l'application web.

💡 **Note importante** : Bien que ces deux applications soient incluses dans ce dépôt pour faciliter le déploiement et les tests, elles sont conçues pour fonctionner indépendamment et peuvent être hébergées sur des serveurs différents.

démo disponible : http://mediatheque.simontaveirne.engineer/

---

## 🎯 Fonctionnalités

### 🔐 Gestion des Utilisateurs
- Inscription et authentification.
- Profils utilisateurs avec historique d'emprunts.
- Rôles différenciés (**Admin** et **Utilisateur**).

### 📖 Gestion des Médias
- Catalogue de médias (**Livres, DVD, Jeux vidéo, Musique**).
- Recherche et filtrage par type ou mots-clés.
- CRUD des médias (*Ajout, modification et suppression* pour les administrateurs).

### 🔄 Gestion des Emprunts
- Emprunt et retour de médias.
- Suivi des échéances.
- Historique des emprunts par utilisateur.

### 📊 Statistiques
- Aperçu des statistiques générales.
- Médias populaires et récemment ajoutés.

### ⚙️ API (Backend)
- Endpoints REST pour **Utilisateurs, Médias et Emprunts**.
- Opérations **CRUD** complètes.
- Sécurisation basique des accès.

---

## 🏗️ Architecture Technique

### 🎨 Médiathèque (Frontend)
- **Langage** : PHP.
- **Architecture** : MVC.
- **Templates** : Twig.
- **Design** : CSS personnalisé + layout responsive.
- **JS** : Validation de formulaires et interactions utilisateur.

### 🔙 API (Backend)
- **Langage** : PHP.
- **Base de données** : MySQL.
- **Architecture** : REST API.
- **Format de données** : JSON.

---

## 📂 Structure du Projet
Mediatheque
```
/Mediatheque
├── assets/            # Ressources statiques (CSS, JS, images)
├── controllers/       # Contrôleurs de l'application
├── models/            # Modèles pour interagir avec l'API
├── templates/         # Templates Twig pour l'affichage
├── vendor/            # Dépendances (Twig et autres)
├── config.php         # Configuration de l'application
└── index.php          # Point d'entrée de l'application
```
API
```
/API
├── .htaccess          # Configuration pour le routage des URL
├── config.php         # Configuration de l'API et connexion à la base
├── index.php          # Routeur principal de l'API
├── users.php          # Endpoint pour la gestion des utilisateurs
├── media.php          # Endpoint pour la gestion des médias
├── loans.php          # Endpoint pour la gestion des emprunts
└── statistics.php     # Endpoint pour les statistiques
``` 


---

## 📌 Guide d'Installation

### 🔧 Prérequis
- Serveur web (Apache, Nginx).
- PHP **7.4+**.
- MySQL **5.7+**.
- Gestionnaire de paquets **Composer**.

### ⚙️ Installation
#### 1️⃣ Préparation de la Base de Données
- Créez une base **MySQL** nommée `mediatheque`.
- Importez le fichier `mediatheque.sql`.

#### 2️⃣ Configuration de l'API
- Modifiez `config.php` avec vos paramètres (**DB_HOST, DB_USER, etc.**).
- Configurez un **Virtual Host** (`api.local`).
- Ajustez votre fichier `hosts`.

#### 3️⃣ Configuration de la Médiathèque
- Placez le dossier `/Mediatheque` dans votre serveur.
- Exécutez `composer install`.
- Configurez l’URL de l’API dans `config.php`.

---

## 📝 Guide d'Utilisation

### 👤 Comptes Utilisateurs
**Administrateur :**  
🆔 `Admin`  
🔑 `P4S5W0rD`  

**Utilisateur Standard :**  
🆔 `user`  
🔑 `password123`

### ⚙️ Fonctionnalités Admin
- **Gérer les utilisateurs et emprunts.**
- **Ajouter, modifier et supprimer des médias.**
- **Consulter toutes les statistiques.**

### 🎭 Fonctionnalités Utilisateur
- Parcourir **le catalogue**.
- **Emprunter des médias disponibles**.
- **Retourner les médias empruntés**.
- Consulter **l'historique & statistiques personnelles**.

---

## 🛠️ Résolution des Problèmes

### 🔗 Connexion API
- Vérifiez l’URL de l’API (`config.php`).
- Testez directement l’API dans le navigateur.

### 🏷️ Encodage des Caractères
- Vérifiez **UTF-8** pour tous les fichiers.
- Assurez-vous que la BDD utilise **utf8mb4**.

### 🔑 Problèmes d'Authentification
- Réinitialisez les mots de passe dans **MySQL**.
- Consultez les logs PHP.

---

## 🎓 Crédits et Licence

🖋️ **Développeur :** Simon Taveirne  
📧 **Contact :** simon.taveirne@proton.me
© 2025 **Système de Gestion de Médiathèque** - Tous droits réservés.

