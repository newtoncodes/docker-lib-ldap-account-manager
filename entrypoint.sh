#!/usr/bin/env bash

if [ "$1" = "httpd" ]; then
    for f in /var/lib/ldap-account-manager /etc/ldap-account-manager; do
        if [ ! -z "$(ls -A $f.original)" ]; then
            if [ -z "$(ls -A $f)" ]; then
                cp -a ${f}.original/* ${f}/
                chown -R www-data.www-data ${f}
            fi

            rm -rf ${f}.original
        fi
    done

    set -e

    # Apache gets grumpy about PID files pre-existing
    rm -f /usr/local/apache2/logs/httpd.pid

    exec apachectl -DFOREGROUND
else
    exec "$@"
fi