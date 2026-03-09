# PHP / Symfony Technical Showcase

This repository is a small Symfony project created as a technical showcase.
The goal is not to build a full application but to demonstrate clean architecture, development tooling, and a few features.

The project includes a Docker development environment and a minimal Symfony application exposing technical endpoints and a protected dashboard.

## Features

- Dashboard (`/dashboard`) – overview page
- Route inspector (`/routes`) – lists application routes
- Health check (`/health`) – basic runtime diagnostics
- About page (`/about`)
- Login page (`/login`) and logout (`/logout`)
- SEO sitemap generator – CLI command generating `public/sitemap.xml`

## Endpoint access

- Public: `/login`, `/logout`, `/about`, `/sitemap.xml`
- Protected (requires authentication): `/dashboard`, `/routes`, `/health`

## Technical Stack

- PHP 8
- Symfony (LTS)
- Doctrine DBAL
- SQLite (`apps/web/var/app.db`)
- Docker / Docker Compose

More details about the Symfony application can be found in:
[apps/web/README.md](apps/web/README.md)

## Dashboard authentication

Dashboard endpoints use Symfony Security form login with credentials stored in SQLite.

Create or update a user from inside the web container:

```bash
php bin/console app:dashboard-user:create <username>
```

If `--password` is not provided, the command prompts for it.

## How to setup local development

This project uses HTTPS locally with the custom domain `sample01.dev`

### 1. Install mkcert (one-time setup)

Run the following commands:

`sudo apt update`

`sudo apt install mkcert libnss3-tools`

Initialize the local Certificate Authority: `mkcert -install`

---

### 2. Generate the local SSL certificate

From any directory, run: `mkcert sample01.dev`

This will generate two files:
- `sample01.dev.pem` (certificate)
- `sample01.dev-key.pem` (private key)

---

### 3. Copy certificates into the project

From the directory where the certificates were generated, run:

`mv sample01.dev.pem [project-path]/docker/apache/ssl/sample01.dev.crt`

`mv sample01.dev-key.pem [project-path]/docker/apache/ssl/sample01.dev.key`

Important: these files are local-only and must NOT be committed to Git.

---

### 4. Configure local DNS

Edit the local hosts file as root: `sudo vim /etc/hosts`

Add the following line: `127.0.0.1 sample01.dev`

---

### 5. Start the project

Run: `docker compose up -d --build`

Application code lives in `apps/web` (mounted to `/var/www/web` inside containers).

Then open the following URL in your browser: https://sample01.dev

You should see a valid HTTPS connection with no browser warning.

---

### Optional troubleshooting

If mkcert was previously installed manually and the command does not resolve correctly, run:
`hash -r`

Then retry the mkcert commands.
