# LDAP account manager on ubuntu docker image

Basic LDAP account manager image, no additional functionality. 

Just run it with openldap and configure via the web interface.

Default password: **lam**.

**Added password change page:**

http://host:port/password.php

Environment variables:

```bash
LDAP_ORG=Example Org
LDAP_DOMAIN=example.org
LDAP_HOST=localhost
LDAP_PORT=389
LDAP_USERS_DN=ou=users,dc=example,dc=org
LDAP_SEARCH_DN=cn=admin,dc=example,dc=org
LDAP_SEARCH_PASSWORD=admin
```