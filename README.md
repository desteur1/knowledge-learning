## Technologies utilisées

- Symfony 7
- PHP 8.2
- MySQL
- Doctrine ORM
- Twig
- EasyAdmin
- Dompdf
- HTML / CSS

---

## Installation

Clonez le projet :

```bash
git clone https://github.com/votre-repo/knowledge-learning.git
cd knowledge-learning
```

## Installez les dépendances

composer install
npm install
npm run build

## Création de base

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

## Chargez les données de démonstration

php bin/console doctrine:fixtures:load

## php bin/console doctrine:fixtures:load

symfony server:start

## Lancer les tests

php bin/phpunit
