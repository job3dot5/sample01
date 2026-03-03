# Web App

## Project setup

### Prerequisites
- PHP >= 8.2
- Composer

### Install

From `apps/web`:

```
composer install
```

### Local configuration

Set the base URL for URL generation (e.g., sitemap):

Create/edit `apps/web/.env.local`:

```
DEFAULT_URI=https://sample01.dev
```

## Git hooks (local)

Hooks are not installed automatically by Git. Use symlinks so updates to the hook files are picked up automatically.

From `apps/web`:

```
ln -s .githooks/pre-commit ../../.git/hooks/pre-commit
ln -s .githooks/pre-push ../../.git/hooks/pre-push
```

If a hook already exists:

```
rm ../../.git/hooks/pre-commit
rm ../../.git/hooks/pre-push
```

Hook behavior:
- `pre-commit`: runs `composer cs:check`
- `pre-push`: runs `composer lint`
