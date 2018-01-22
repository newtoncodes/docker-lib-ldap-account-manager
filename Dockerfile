FROM ubuntu:trusty

ENV CONFIG=/etc/ldap-account-manager
ENV DATA=/var/lib/ldap-account-manager

RUN apt-get update && apt-get upgrade -y && apt-get install -y ldap-account-manager php5-imap
RUN php5enmod imap

RUN mv /etc/ldap-account-manager /etc/ldap-account-manager.original
RUN mv /var/lib/ldap-account-manager /var/lib/ldap-account-manager.original

RUN mkdir /var/lib/ldap-account-manager /etc/ldap-account-manager
RUN chown www-data.www-data /var/lib/ldap-account-manager /etc/ldap-account-manager

RUN sed -i 's,DocumentRoot .*,DocumentRoot /usr/share/ldap-account-manager,' /etc/apache2/sites-available/000-default.conf
RUN ln -sf /proc/1/fd/1 /var/log/apache2/access.log
RUN ln -sf /proc/1/fd/2 /var/log/apache2/error.log

COPY password.php /usr/share/ldap-account-manager/password.php

COPY entrypoint.sh /usr/bin/entrypoint
RUN chmod +x /usr/bin/entrypoint

ENTRYPOINT ["/usr/bin/entrypoint"]
CMD ["httpd"]

VOLUME ["/var/lib/ldap-account-manager"]
EXPOSE 80
