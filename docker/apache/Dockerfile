FROM httpd:2.4

# Enable required Apache modules
RUN sed -i \
    -e 's/^#LoadModule ssl_module/LoadModule ssl_module/' \
    -e 's/^#LoadModule socache_shmcb_module/LoadModule socache_shmcb_module/' \
    -e 's/^#LoadModule proxy_module/LoadModule proxy_module/' \
    -e 's/^#LoadModule proxy_fcgi_module/LoadModule proxy_fcgi_module/' \
    conf/httpd.conf

# Remove default SSL configuration (conflict with custom vhost)
RUN rm -f /usr/local/apache2/conf/extra/httpd-ssl.conf

# Ensure Apache listens on port 443 (80 is already defined by default)
RUN grep -q "^Listen 443" /usr/local/apache2/conf/httpd.conf \
 || echo "Listen 443" >> /usr/local/apache2/conf/httpd.conf

# Custom VirtualHost configuration
COPY vhost.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf

# SSL certificates
COPY ssl /usr/local/apache2/conf/ssl

# Explicitly include custom vhosts
RUN echo "Include conf/extra/httpd-vhosts.conf" \
    >> /usr/local/apache2/conf/httpd.conf

# Set global ServerName to suppress FQDN warning
RUN echo "ServerName sample01.dev" >> /usr/local/apache2/conf/httpd.conf