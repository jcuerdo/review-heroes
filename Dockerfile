FROM nginx

ADD app /var/www/html
ADD ./nginx/vhost-prod.conf /etc/nginx/conf.d/default.conf

