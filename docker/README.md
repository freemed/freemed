# FREEMED FOR DOCKER

This should allow you to run FreeMED locally. It assumes a db dump from a running instance.

Check out https://github.com/freemed/freemed.git or put your copy of `/usr/share/freemed` into a local `./freemed` directory, and chown it to be owned by `101:101`.

`docker compose up -d --build` should bring everything up.

## The runtime image

`docker/Dockerfile` builds the PHP runtime this tree expects: PHP-FPM **8.3.36**,
the first 8.3 release with the PHP2SHELL fixes (CVE-2026-109618 /
CVE-2026-109619), with the base image pinned by digest. The compose file builds
it as `freemed-php:8.3.36`; it no longer pulls a third-party `:latest`, which is
how this stack previously ran PHP 8.3.31 built 2026-05-27.

Moving the runtime forward: bump `FROM` in `docker/Dockerfile` to the new 8.3.x
tag, re-resolve the digest
(`docker pull php:8.3-fpm && docker image inspect php:8.3-fpm --format '{{index .RepoDigests 0}}'`),
update the label and the build-time assertion, rebuild, and re-run:

    make php-runtime-check          # asserts the interpreter is at/above the fix floor

The audit, the inventory of every PHP runtime this tree declares, and the
per-layout verdicts are in `doc/PHP2SHELL_AUDIT`; the measured before/after rows
for this image are in `tests/security/evidence/php2shell-remediation.txt`.

## PHP settings

`./php-custom.ini` is mounted at `/usr/local/etc/php/conf.d/zz-freemed.ini`,
which is the directory the image scans (`PHP_INI_DIR=/usr/local/etc/php`). Verify
any change from **inside** the container - `php --ini` and `php -i` - because a
mount at a path PHP does not scan fails silently and looks identical to a
working one. That is exactly the defect this stack's history records.

The body cap in `nginx.conf` (`client_max_body_size 64M`) and PHP's
`post_max_size` (64M) are matched deliberately: they are the CVE-2026-109618
mitigation, and raising one without the other either breaks document uploads or
widens the vulnerable parse window.
