# syntax=docker/dockerfile:1.6
#
# PyroCMS 2.2.4 — Apache + PHP 8.2 image for Coolify-style deploys.
#
# Build (locally, for testing):
#   docker build -t pyrocmspro2ng .
#   docker run --rm -p 8080:80 --env-file .env pyrocmspro2ng
#
# Coolify:
#   - Point the application at this repo; Coolify auto-detects the Dockerfile.
#   - Enable "Automatic deployments" on the application so a push to main
#     rebuilds the image. (Coolify UI → application → Configuration → toggle
#     Automatic deployments. Optionally lock to a branch / require CI green.)
#   - Configure the env vars listed in .env.production.example (Coolify
#     env-vars panel) — real env vars override anything baked into a .env
#     inside the image (see system/cms/bootstrap/env.php).
#   - Mount persistent volumes onto:
#       /var/www/html/uploads
#       /var/www/html/assets/cache
#       /var/www/html/system/cms/cache
#       /var/www/html/system/cms/logs
#     Otherwise these directories are reset on every redeploy.
#   - Provision a MySQL 8.4 database and point DB_* at it.
#
# Build args:
#   APP_ENV=production (default) — opcache.validate_timestamps=0 for max perf.
#   APP_ENV=development           — opcache.validate_timestamps=1 so a bind-
#                                   mounted source tree picks up edits without
#                                   restarting the container. Set in Coolify
#                                   under "Build → Build arguments" for any
#                                   environment that bind-mounts source.

FROM php:8.2-apache

# -----------------------------------------------------------------------------
# System packages + PHP extensions
# -----------------------------------------------------------------------------
# - libcurl4-openssl-dev : curl (PHP ext)
# - libfreetype/libpng/libjpeg/libwebp : gd
# - libicu-dev       : intl
# - libonig-dev      : mbstring
# - libxml2-dev      : xml/dom
# - libzip-dev       : zip
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
        zip \
    ; \
    docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        zip \
    ; \
    rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------------------------
# Apache
# -----------------------------------------------------------------------------
# mod_rewrite for CodeIgniter pretty URLs; mod_headers/mod_expires for the
# admin asset cache-busting we just added (?v=<mtime>) to land Cache-Control.
RUN a2enmod rewrite headers expires

# AllowOverride All so the project's .htaccess (if present) can rewrite URLs.
# Repo currently gitignores .htaccess — supply one via a Coolify volume / build
# arg if you want index.php-less URLs.
RUN printf '<Directory /var/www/html>\n  AllowOverride All\n  Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/pyrocms.conf; \
    a2enconf pyrocms

# Don't leak the Apache/PHP version in headers.
RUN { \
        echo 'ServerTokens Prod'; \
        echo 'ServerSignature Off'; \
    } > /etc/apache2/conf-available/security-tighten.conf; \
    a2enconf security-tighten

# -----------------------------------------------------------------------------
# PHP runtime tuning
# -----------------------------------------------------------------------------
RUN { \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=64M'; \
        echo 'post_max_size=64M'; \
        echo 'max_execution_time=120'; \
        echo 'expose_php=Off'; \
        echo 'date.timezone=Europe/Stockholm'; \
        echo 'session.cookie_httponly=1'; \
        echo 'session.cookie_samesite=Lax'; \
    } > /usr/local/etc/php/conf.d/zz-pyrocms.ini

# OPcache. validate_timestamps=0 in prod — Coolify rebuilds the image on every
# deploy, so files only change when a new image is pushed. Build with
# `--build-arg APP_ENV=development` (or set it in Coolify) to flip to 1 for
# images that bind-mount source over the baked-in copy during local work.
ARG APP_ENV=production
RUN set -eux; \
    if [ "$APP_ENV" = "development" ]; then \
        opcache_validate=1; opcache_revalidate=2; \
    else \
        opcache_validate=0; opcache_revalidate=0; \
    fi; \
    { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo "opcache.validate_timestamps=${opcache_validate}"; \
        echo "opcache.revalidate_freq=${opcache_revalidate}"; \
    } > /usr/local/etc/php/conf.d/zz-opcache.ini

# -----------------------------------------------------------------------------
# Application
# -----------------------------------------------------------------------------
WORKDIR /var/www/html

# Source. .dockerignore prunes .git, .idea, .playwright-mcp, .env, etc.
COPY . .

# Writable runtime directories. These are the paths Coolify should mount
# volumes onto for persistence. We create + chown them up front so the
# container starts cleanly even before any volume is attached.
RUN set -eux; \
    mkdir -p \
        uploads \
        assets/cache \
        system/cms/cache \
        system/cms/logs \
    ; \
    chown -R www-data:www-data \
        uploads \
        assets/cache \
        system/cms/cache \
        system/cms/logs \
    ; \
    find uploads assets/cache system/cms/cache system/cms/logs \
        -type d -exec chmod 0775 {} + ; \
    find uploads assets/cache system/cms/cache system/cms/logs \
        -type f -exec chmod 0664 {} +

# Default .htaccess for CodeIgniter pretty URLs. The repo gitignores
# .htaccess (different sites/envs ship different rewrite rules locally),
# so the image otherwise has none — meaning Apache only serves `/` (via
# DirectoryIndex) and 404s every other path before the front controller
# ever sees the URL. We only write this file if no .htaccess was copied
# in, so a deployment that does commit one still wins.
# Uses BuildKit heredoc support (enabled by the `# syntax=` line at top).
RUN <<'BASH'
set -eux
if [ ! -f /var/www/html/.htaccess ]; then
    cat > /var/www/html/.htaccess <<'HT'
# Auto-generated by Dockerfile when the repo ships no .htaccess.
RewriteEngine On

# Allow direct access to real files and directories (assets, uploads…).
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Block direct access to system internals just in case.
RewriteRule ^(system|application)(/|$) - [F,L]

# Everything else → CodeIgniter front controller.
RewriteRule ^(.*)$ index.php/$1 [L]
HT
fi
BASH

# PyroCMS / CI 2.x case-sensitivity shim. CI core's Loader::model() does
# ucfirst() on the model name before searching the filesystem, so it looks
# for `Foo_m.php` while PyroCMS ships the files as `foo_m.php`. macOS APFS
# is case-insensitive so this just works locally; on Linux the lookup
# fails with "Unable to locate the model you have specified". Same problem
# bites views and controllers occasionally. Create capitalized symlinks
# next to every lowercase model/view/controller file so both spellings
# resolve. Idempotent: skips files that already start with an upper-case
# letter, and safely no-ops re-runs (`ln -snf`).
RUN set -eux; \
    find /var/www/html/system/cms/modules \
         /var/www/html/system/cms/libraries \
         /var/www/html/system/sparks \
         /var/www/html/addons \
         -type f \
         \( -path '*/models/*.php' -o -path '*/controllers/*.php' -o -path '*/libraries/*.php' \) \
         -print 2>/dev/null \
    | while IFS= read -r f; do \
        base=$(basename "$f"); \
        first=$(printf %.1s "$base"); \
        case "$first" in \
            [a-z]) \
                cap=$(printf '%s%s' "$(printf %.1s "$base" | tr a-z A-Z)" "${base#?}"); \
                target="$(dirname "$f")/$cap"; \
                [ -e "$target" ] || ln -s "$base" "$target"; \
                ;; \
        esac; \
    done

# Static health endpoint. The probe deliberately doesn't hit the front
# controller — that would couple liveness to PHP/DB/router/template state,
# and Coolify would roll back deploys for config issues (DB DNS, missing env
# vars) that the container itself is not responsible for. Apache serving a
# byte off disk is enough proof "the container is up; route to me."
RUN echo 'OK' > /var/www/html/healthz.html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS --max-time 4 http://127.0.0.1/healthz.html || exit 1

CMD ["apache2-foreground"]
