# AppliSGSFrais

Application web de gestion des frais du laboratoire GSB.

## clé ssh avec git
1. génerez une clé dans **git Basch** avec la commande `ssh-keygen -o`
2. Afficher votre clé publique: `cat ~/.ssh/id_ed25519.pub`
3. Ajouter cette clé dans github:<br>
    a. Dans le coin supérieur droit d’une page, cliquez sur votre photo de profil, puis sur Paramètres.<br>
    b. Dans la section « Accès » de la barre latérale, cliquez sur Clés SSH et GPG.<br>
    c. Cliquez sur Nouvelle clé SSH ou Ajouter une clé SSH.<br>
    d. Dans le champ « Clé », collez votre clé publique.

## Installation en local
1. Importez la base de données en exécutant les scripts `sql` du dossier `ScriptsSQL`.
2. Modifiez le mot de passe de l'utilisateur `sql` dans le fichier `ScriptsSQL/gsbfrais_bduser.sql`.
3. Modifiez le mot de passe de l'utilisateur `sql` dans le fichier `config.php`.
4. Servez le dossier `www` à l'aide d'un serveur web (apache, nginx, etc.).

## Mise en production
1. Assurez-vous que seul le dossier `www` est servi par le serveur web.
2. Modifiez le mot de passe de l'utilisateur `sql` dans le fichier `config.php` pour des raisons de sécurité.
3. Effectuez toutes les mises à jour nécessaires pour sécuriser l'application avant de la déployer en production.

N'hésitez pas à consulter la documentation d'installation pour obtenir des informations détaillées sur la configuration et l'utilisation de l'application.