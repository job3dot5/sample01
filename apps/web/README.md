
# PHP / Symfony Technical Showcase

The goal of this app is to illustrate pragmatic development practices rather than building a full product with:
- a clean development environment
- basic backend type features
- automated code quality checks

## Features Demonstrated

The Symfony application contains a few simple endpoints illustrating common backend concerns:

- Dashboard – overview page
- Route inspector – lists application routes
- Health check – basic runtime diagnostics
- SEO sitemap generator – CLI command generating sitemap.xml
- Cached route listing – example usage of Symfony Cache

These endpoints are intentionally simple and focus on backend logic rather than frontend design.

## Technical Stack

- PHP 8
- Symfony (LTS)
- Docker / Docker Compose

## Development tooling

- PHPStan (static analysis)
- PHP-CS-Fixer (code style)
- Git hooks (pre-commit / pre-push)
- GitHub Actions CI


# How to install

## 1. Follow the docker environment install [here](../../README.md) 

## 2. composer install
Enter your web container from your host machine to install composer modules :
```bash
docker compsoe exec php /bin/bash
composer install
```

## 3. Git hooks

Hooks are not installed automatically by Git. Use symlinks so updates to the hook files are picked up automatically.

From `[project-root]/.git/hooks`

```
ln -s ../../apps/web/.githooks/pre-commit pre-commit
ln -s ../../apps/web/.githooks/pre-push pre-push
```

If a hook already exists remove it.

Hook behavior:
- `pre-commit`: runs `composer cs:check`
- `pre-push`: runs `composer lint`
