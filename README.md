# Symfony Backend Technical Showcase

[![PHP](https://img.shields.io/badge/PHP-8.3-blue)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-LTS-black)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![CI](https://github.com/job3dot5/sample01/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR_USERNAME/YOUR_REPOSITORY/actions)

This repository contains a small Symfony backend project used as a technical showcase.

The goal is not to build a full application but to demonstrate clean architecture, development tooling, and a few features.

The project includes a Docker development environment and a minimal Symfony application exposing technical endpoints and a protected dashboard.

## Features Demonstrated

- Dashboard (`/dashboard`) – overview page
- Route inspector (`/routes`) – lists application routes
- Health check (`/health`) – basic runtime diagnostics
- Login (`/login`) / logout (`/logout`) with Symfony Security form auth
- IP request rate limiter (5 requests / 30 seconds)
- SEO sitemap generator – CLI command generating `public/sitemap.xml`
- Symfony cache in route listing and IP request rate limiter

## Technical Stack

- PHP 8
- Symfony (LTS)
- Doctrine DBAL
- SQLite
- Docker / Docker Compose

## Development Tooling

- PHPStan (static analysis)
- PHP-CS-Fixer (code style)
- PHPUnit
- Git hooks (pre-commit / pre-push)
- GitHub Actions CI

More details about the Symfony application can be found in:
[apps/web/README.md](apps/web/README.md)

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
