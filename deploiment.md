DEPLOIEMENT LOCAL
=======

Clone the repository
```
git clone https://github.com/Mist3rYan/jobService
```

Metter vous dans le dossier jobService
```
cd jobService
```

Installer les dependencies
```
composer install
```

Créer la base de donnée
Dans le fichier .env modifiez l'url de la databse avec votre database :
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/jobService?serverVersion=10.4.27-MariaDB&charset=utf8mb4"
```
Puis, créer la bdd et la migration
```
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```
Et charger les fixtures avec 
```
php bin/console doctrine:fixtures:load
```
toutes les fixtures ont pour mot de passe: password  

---
---
DEPLOIEMENT HEROKU
=======

Dans le terminal se mettre à la racine du projet symfony
Se connecter à heroku par navigateur
```
heroku login
```
Ou se connecter à heroku en console 
```
heroku login -i
```
Ensuite crèer une application
```
heroku create nomapplication
```
Nous avons ensuite une adresse web qui redirige sur l'application et un depot GIT heroku.


Crèer un fichier Procfile et coller cette ligne
```
web: heroku-php-apache2 public/
```

Configurer la variable d'environement
```
heroku config:set APP_ENV=prod
heroku config:set APP_SECRET=$(php -r 'echo bin2hex(random_bytes(16));')
```

Pour la bdd, aller sur le site heroku.
Dans l'application aller sur l'onglet resources.
Rechercher et installer JawsDB Maria.
Sélectionner le plan gratuit et valider.
Aller dans settings de l'application sur le site heroku.
Puis configurer une variable d'environnement.
DATABASE_URL et lui donner la valeur de la variable JAWSDB_MARIA_URL
Pensez à commenter les variables dans le fichier .env si elles existent.


Ensuite push sur Heroku
``` 
git add Procfile
git commit -m "Added Procfile"
git push heroku master
```
Consulter le log en cas d'erreur. Si besoin installer les dépendances manquantes.

Puis faire la migration de la bdd
```
heroku run php bin/console doctrine:migrations:migrate
```

Pour la réécriture d'URL
```
composer require symfony/apache-pack
git add composer.json composer.lock symfony.lock public/.htaccess
git commit -m "apache-pack"
git push heroku master
```

Ensuite dans le terminal ecrivez la ligne de code suivante et répondez yes
```
heroku run php bin/console doctrine:fixtures:load
```

Ensuite modifié tout de suite le mot de passe admin
Pour se connecter
Adresse email: admin@admin.fr
Mot de passe: password
Puis mon compte -> Modifier -> Changer mot de passe



Ouvrir la page de l'application
```
heroku open
```
Avec le comtpe admin vous avez accès a toutes les datas.

Tout est prêt !!!

ATTENTION POUR LES MAILS LORSQU'UNE CANDIDATURE EST VALIDEE LES MAILS SONT EN ATTENTES
POUR LES ENVOYER
```
heroku run php bin/console messenger:consume
```
