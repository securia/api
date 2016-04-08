upstream hhvm {
        least_conn;
        server unix:///var/run/hhvm.sock.1;
        server unix:///var/run/hhvm.sock.2;
        server unix:///var/run/hhvm.sock.3;
        server unix:///var/run/hhvm.sock.4;
        server unix:///var/run/hhvm.sock.5;
        server unix:///var/run/hhvm.sock.6;
        server unix:///var/run/hhvm.sock.7;
        server unix:///var/run/hhvm.sock.8;
        server unix:///var/run/hhvm.sock.9;
        server unix:///var/run/hhvm.sock.10;
        server unix:///var/run/hhvm.sock.11;
        server unix:///var/run/hhvm.sock.12;
        server unix:///var/run/hhvm.sock.13;
        server unix:///var/run/hhvm.sock.14;
        server unix:///var/run/hhvm.sock.15;
        server unix:///var/run/hhvm.sock.16;
}
server {
        listen 443 ssl default_server;
        listen 8081 default_server;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS;
        ssl_certificate /opt/ssl/wildcard.puzzlesocial.com.chained.crt;
        ssl_certificate_key /opt/ssl/wildcard.puzzlesocial.com.key;
        root /opt/data/nginx/vhosts/apin.puzzlesocial.com/htdocs;
        access_log on;
        error_log /var/log/nginx/apin.puzzlesocial.com_error.log;
        index index.html index.php;
        server_name apin.puzzlesocial.com;
        location ~ \\.(hh|php)$ {
            fastcgi_keep_conn on;
            fastcgi_pass   hhvm;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
        client_max_body_size 16M;
        fastcgi_read_timeout 120;
        location @rewrite {rewrite ^/(.*)$ /index.php?_url=/$1;}
        location ~ /\\.ht {deny all;}
        location / {try_files $uri $uri/ /index.php$is_args$args; }
}
