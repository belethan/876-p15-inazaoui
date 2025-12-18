# Ina Zaoui - Symfony 7. 4

## Présentation

Ce dépôt contient l’application Inazaoui, refondue et optimisée sous Symfony 7.4 (LTS). Le projet initial, développé sous Symfony 5.4, présentait des problèmes de performance (notamment un effet N+1 sur la page Invités). La refonte vise à améliorer la scalabilité, la maintenabilité et la performance globale de l’application.

Les choix techniques et les optimisations mises en place sont documentés afin de faciliter la reprise du projet par un nouveau développeur.

## Prérequis

PHP : >= 8.2 (8.4 recommandé)

* Composer : >= 2.x
* MySQL : >= 8.0
* Node.js (optionnel, si assets front)
* Symfony CLI (optionnel)
* Extensions PHP requises :
* pdo_mysql
* intl
* opcache

### 1. Cloner le dépôt
   `git clone https://github.com/OpenClassrooms-Student-Center/876-p15-inazaoui.git
   cd 876-p15-inazaoui`
### 2. Installer les dépendances PHP
   `composer install`
### 3. Configuration de l’environnement

### Créer le fichier .env.local :

`cp .env .env.local`

### Exemple de configuration :

APP_ENV=dev
APP_DEBUG=1
DATABASE_URL="mysql://user:password@127.0.0.1:3306/inazaoui?serverVersion=8.0&charset=utf8mb4"
#### 4. Base de données

Créer la base :

`php bin/console doctrine:database:create`

Appliquer le schéma (migrations) :

`php bin/console doctrine:migrations:migrate`

Charger les données de test (fixtures) :

`php bin/console doctrine:fixtures:load`

## Lancer l’application
Serveur PHP intégré (recommandé)
php -S 127.0.0.1:8000 -t public

Accès :

http://127.0.0.1:8000
Profiler Symfony

En environnement dev, le Web Debug Toolbar et le Profiler Symfony sont actifs par défaut.

Accès direct :
http://127.0.0.1:8000

## Lancer l’ensemble des tests
* php bin/phpunit
* Générer un rapport de couverture
* XDEBUG_MODE=coverage php bin/phpunit --coverage-html var/coverage

Le rapport HTML est accessible dans :

`var/coverage/index.html`

## Choix d’implémentation clés

* Suppression de l’effet N+1 via des requêtes Doctrine optimisées (JOIN FETCH)
* Séparation claire des responsabilités (Controller / Repository / Templates)
* Utilisation du Profiler Symfony comme outil principal d’analyse
* Optimisation des performances avant tout via la couche Data, non via Twig

## Performance

Les performances ont été mesurées à l’aide du Profiler Symfony en environnement dev, puis comparées avec un environnement prod.

### Principaux indicateurs analysés :

* Temps total d’exécution
* Nombre de requêtes SQL
* Temps Doctrine
* Temps de rendu Twig
* Consommation mémoire

Les résultats montrent une nette amélioration de la scalabilité et une stabilité accrue des performances sous Symfony 7.4.


| Indicateur     | Symfony 5.4 | Symfony 7.4  <br /> 
|----------------|-------------|---------------------|
| Temps total    | 134 ms      | 608 ms              |
| Initialisation | 5 ms        | 186 ms              |
| Requêtes SQL   | 102         | 2                   |
| Temps SQL      | 34,8 ms     | 34,8 ms             |
| Temps Twig     | 116 ms      | 116 ms              |
| Scalabilité	 | Linéaire    | Stable              |

## Conclusion

Ce projet de refonte avait pour objectif d’identifier et de corriger des problèmes de performance sur une application Symfony initialement développée en version 5.4. L’analyse s’est concentrée en particulier sur la page Invités, dont les temps de chargement augmentaient de manière significative avec la volumétrie des données.

L’utilisation du profiler Symfony a permis d’identifier précisément l’origine du problème : un effet N+1 lié au chargement paresseux des relations Doctrine. Cette implémentation entraînait un nombre excessif de requêtes SQL (102 requêtes pour seulement 2 requêtes distinctes), impactant directement le temps de rendu Twig et la scalabilité de la page.

La migration vers Symfony 7.4, combinée à une refonte de la stratégie de chargement des données, a permis de supprimer totalement cet effet N+1. Le nombre de requêtes SQL a été réduit à 2, le temps d’accès à la base divisé par plus de trois, et le temps de rendu Twig fortement diminué. Les performances deviennent ainsi stables et indépendantes de la volumétrie de données.

Bien que le coût d’initialisation du framework soit plus élevé en environnement de développement sous Symfony 7.4, cette différence constitue un coût fixe sans impact sur la scalabilité en production. La refonte apporte donc une amélioration significative de la qualité, de la maintenabilité et de la performance globale de l’application.

Cette approche méthodique, fondée sur des indicateurs mesurables et reproductibles, répond pleinement aux objectifs du projet et garantit une application plus robuste, évolutive et conforme aux bonnes pratiques actuelles de l’écosystème Symfony.