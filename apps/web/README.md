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

From `[project-root]/.git/hooks`

```
ln -s ../../apps/web/.githooks/pre-commit pre-commit
ln -s ../../apps/web/.githooks/pre-push pre-push
```

If a hook already exists remove it.

Hook behavior:
- `pre-commit`: runs `composer cs:check`
- `pre-push`: runs `composer lint`
