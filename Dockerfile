FROM newtoncodes/php:5.6

RUN apt-get update && \
    DEBIAN_FRONTEND="noninteractive" apt-get upgrade -y && \
    DEBIAN_FRONTEND="noninteractive" apt-get install -y ldap-account-manager apache2 apache2-utils
#RUN DEBIAN_FRONTEND="noninteractive" apt-get purge -y php7.2 php7.2-*
RUN DEBIAN_FRONTEND="noninteractive" apt-get install -y php5.6-imap php5.6-imagick php5.6-json php5.6-gd php5.6-opcache php5.6-readline php5.6-cli php5.6-ldap php5.6-common php-fpdf
RUN mv /etc/ldap-account-manager /etc/ldap-account-manager.original
RUN mv /var/lib/ldap-account-manager /var/lib/ldap-account-manager.original

RUN mkdir /var/lib/ldap-account-manager /etc/ldap-account-manager
RUN chown www-data.www-data /var/lib/ldap-account-manager /etc/ldap-account-manager

RUN sed -i 's,DocumentRoot .*,DocumentRoot /usr/share/ldap-account-manager,' /etc/apache2/sites-available/000-default.conf
RUN ln -sf /proc/1/fd/1 /var/log/apache2/access.log
RUN ln -sf /proc/1/fd/2 /var/log/apache2/error.log

COPY entrypoint.sh /usr/bin/entrypoint
RUN chmod +x /usr/bin/entrypoint

ENTRYPOINT ["/usr/bin/entrypoint"]
CMD ["httpd"]

VOLUME ["/var/lib/ldap-account-manager"]
EXPOSE 80
