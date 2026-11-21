# SUNUSCORE

Plateforme de gestion et de suivi des compétitions de football.
**Slogan** : Suivez. Vivez. Partagez le football.

## Phase 1 - Fondations

Cette phase met en place l'architecture MVC, la configuration, le noyau applicatif,
le schéma SQL complet et le seed de démonstration.

### Installation locale (XAMPP)

1. Placer le projet dans `C:\xampp\htdocs\sunuscore`.
2. Copier `.env.example` en `.env` et adapter les identifiants MySQL.
3. Créer la base `sunuscore` (utf8mb4_unicode_ci) dans phpMyAdmin.
4. Importer `database/schema.sql` puis `database/seed.sql`.
5. Exécuter `composer install` à la racine.
6. Ouvrir `http://localhost/sunuscore/public/`.

### Tests

```cmd
php tests\DatabaseTest.php