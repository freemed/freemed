# FreeMED runtime image.
#
# WHY THIS FILE EXISTS: the tree used to consume a third-party image by a
# floating tag (jbuchbinder/moodle-php-8.3-fpm:latest, which resolved to PHP
# 8.3.31 built 2026-05-27) and mount its ini drop-in at a Bitnami path that
# image does not scan. That left the container on a PHP older than the
# PHP2SHELL fix floor with none of the tree's ini settings in effect. See
# doc/PHP2SHELL_AUDIT sections 2, 3.1 and 6.
#
# The base is pinned BY DIGEST, not by tag: 8.3.36 is the first release that
# carries the fixes for CVE-2026-109618 / CVE-2026-109619, and a digest is the
# only form of the reference a later `docker pull` cannot silently move.
#
# Digest provenance (measured 2026-10-23):
#   docker pull php:8.3-fpm && docker image inspect php:8.3-fpm \
#     --format '{{index .RepoDigests 0}}'
#   -> php@sha256:c435accaeb7c535cacf8d6d188fc310431f4fc54545b1694a793069185271dcb
#   docker run --rm --entrypoint php php:8.3-fpm -v  ->  PHP 8.3.36
#
# To move the runtime forward: bump FROM to the new 8.3.x tag, re-run the two
# commands above, replace the digest with the one they print, rebuild, and
# record the new `php -v` here. Do not go back to a bare tag.
FROM php:8.3.36-fpm@sha256:c435accaeb7c535cacf8d6d188fc310431f4fc54545b1694a793069185271dcb

# Extension set kept in line with what the application actually uses and what
# the previous image provided: mysqli (FreemedDb), gd + exif (imaging and
# PhotographicIdentification), soap (services/RemittCallback), intl, xsl
# (CDA/XML output), zip (document archives), sodium, opcache.
# NOT installed: xdebug. The previous image loaded it in the server process;
# a debugger belongs in a development image, not in the one that serves PHI.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
        libsodium-dev \
        libxml2-dev \
        libxslt1-dev \
        libzip-dev; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" exif gd intl mysqli opcache soap sodium xsl zip

# Runtime contract of this image, for `docker inspect` and for anything that
# has to prove which interpreter it is talking to.
LABEL org.opencontainers.image.title="FreeMED PHP runtime" \
      org.opencontainers.image.description="PHP-FPM runtime for FreeMED 0.9.x (PHP2SHELL fix floor)" \
      org.freemed.php.version="8.3.36" \
      org.freemed.php.cve-2026-109618="fixed in 8.3.36" \
      org.freemed.php.cve-2026-109619="fixed in 8.3.36"

# The application tree is mounted at /usr/share/freemed by compose; this image
# carries the interpreter only.
WORKDIR /usr/share/freemed

# Fail the BUILD, not the deployment, if a later edit to the base or the
# extension list quietly moves the interpreter below the fix floor.
RUN php -r 'exit(version_compare(PHP_VERSION, "8.3.36", ">=") ? 0 : 1);' \
    && php -m | grep -qx mysqli
