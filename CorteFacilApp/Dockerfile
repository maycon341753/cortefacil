FROM php:8.1-apache
# ou a versão do PHP que você está usando

# Atualiza os pacotes e instala as extensões PDO
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copia os arquivos da aplicação
COPY . /var/www/html/

# Define permissões se necessário
RUN chown -R www-data:www-data /var/www/html/

# Habilita mod_rewrite se necessário (comum em aplicações PHP)
RUN a2enmod rewrite

# Expõe a porta 80
EXPOSE 80