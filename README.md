# Projet SI

## Installation et Déploiement

1.  ```bash
    Nous avons configuré l'utilisation de l'image Docker de PostgreSQL dans le fichier docker-compose.yml et éventuellement dans le Dockerfile, puis nous avons ajouté cette ligne dans le fichier .env pour définir la connexion à la base de données :
    DATABASE_URL="postgresql://symfony:symfony@database:5432/symfony_db?serverVersion=15&charset=utf8"
    ```  
2. Construire et démarrer les conteneurs Docker :
   ```bash
   docker compose build
   docker compose up -d
   ```

3. Accéder au conteneur :
   ```bash
   docker exec -ti SI bash
   ```

4. Installer les dépendances Symfony :
   ```bash
   symfony composer install
   ```

5. Supprimer les fichiers du dossier `migrations`, sauf `.gitignore`.

6. Générer une nouvelle migration :
   ```bash
   symfony console make:migration
   ```

7. Appliquer les migrations :
   ```bash
   symfony console doctrine:migrations:migrate
   ```
   
8. Lancer le projet :
   ```bash
   symfony server:start --no-tls --listen-ip=0.0.0.0 --d
   ```
Si besoin terminal en mode postgres sql :
 1. docker exec -ti symfony_postgres sh
 2. su - postgres
 ou aussi c'est possible avec cette commande : 
 1. docker exec -it symfony_postgres psql -U symfony -d symfony_db