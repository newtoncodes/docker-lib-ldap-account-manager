#!/usr/bin/env bash

if [ -f /var/lib/ldap-account-manager/vars.env ]; then
    source /var/lib/ldap-account-manager/vars.env

    export LDAP_ORG=${LDAP_ORG:-Example Org}
    export LDAP_DOMAIN=${LDAP_DOMAIN:-example.org}
    export LDAP_HOST=${LDAP_HOST:-localhost}
    export LDAP_PORT=${LDAP_PORT:-389}
    export LDAP_USERS_DN=${LDAP_USERS_DN:-ou=users,dc=example,dc=org}
    export LDAP_SEARCH_DN=${LDAP_SEARCH_DN:-cn=admin,dc=example,dc=org}
    export LDAP_SEARCH_PASSWORD=${LDAP_SEARCH_PASSWORD:-admin}
else
    export LDAP_ORG=${LDAP_ORG:-Example Org}
    export LDAP_DOMAIN=${LDAP_DOMAIN:-example.org}
    export LDAP_HOST=${LDAP_HOST:-localhost}
    export LDAP_PORT=${LDAP_PORT:-389}
    export LDAP_USERS_DN=${LDAP_USERS_DN:-ou=users,dc=example,dc=org}
    export LDAP_SEARCH_DN=${LDAP_SEARCH_DN:-cn=admin,dc=example,dc=org}
    export LDAP_SEARCH_PASSWORD=${LDAP_SEARCH_PASSWORD:-admin}

    echo "
export LDAP_ORG=$LDAP_ORG
export LDAP_DOMAIN=$LDAP_DOMAIN
export LDAP_HOST=$LDAP_HOST
export LDAP_PORT=$LDAP_PORT
export LDAP_USERS_DN=$LDAP_USERS_DN
export LDAP_SEARCH_DN=$LDAP_SEARCH_DN
export LDAP_SEARCH_PASSWORD=$LDAP_SEARCH_PASSWORD
    " > /var/lib/ldap-account-manager/vars.env
fi

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