# TEST PHP

Realisé avec Symfony 5.4.9 et MySql 8.0.29 avec Docker pour démarrer le serveur.

## Ajouter le vendor

`composer install` dans `/project/`

## Démarrer le projet

Run `docker-compose up`.  rendez-vous sur `http://127.0.0.1:8081/` pour visualiser l'application
PhpMyAdmin sur `http://127.0.0.1:8080/`

## Déployer la base de données 

Run `php bin/console doctrine:migrations:migrate` dans `/project/`

## Pour ajouter les données de l'api

Rendez-vous sur `http://127.0.0.1:8081/data`.

## Quelques améliorations

- Créer un controller pour chaque Entité de l'application
- Rajouter des controles de suppression
- Commentaires
