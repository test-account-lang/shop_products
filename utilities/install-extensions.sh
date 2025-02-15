#!/bin/bash
source ./utilities/progressbar.sh || exit 1

echo "Installing Phalcon and PSR extensions..."

export PSR_VERSION=0.7.0
export PHALCON_VERSION=3.4.4
export PHALCON_EXT_PATH=php7/64bits

php_extensions=(intl gettext gd bcmath zip pdo_mysql sockets)
pecl_extensions=(redis mongodb xdebug)

########## START DO NOT EDIT AREA #########
# Install Phalcon & PSR
# Download PSR, see https://github.com/jbboehr/php-psr
curl -LO https://github.com/jbboehr/php-psr/archive/v${PSR_VERSION}.tar.gz && \
tar xzf ${PWD}/v${PSR_VERSION}.tar.gz > /dev/null
curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz && \
tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz > /dev/null

echo "Installing Phalcon & PSR extensions..."
i=0
draw_progress_bar $i 2 "phalcon & psr extensions"
for ext in php-psr-${PSR_VERSION} cphalcon-${PHALCON_VERSION}/build/${PHALCON_EXT_PATH}; do
  docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) ${PWD}/${ext} > /dev/null
  i=$((i+1))
  draw_progress_bar $i 2 "phalcon & psr extensions"
done
echo

rm -r \
    ${PWD}/v${PSR_VERSION}.tar.gz \
    ${PWD}/php-psr-${PSR_VERSION} \
    ${PWD}/v${PHALCON_VERSION}.tar.gz \
    ${PWD}/cphalcon-${PHALCON_VERSION}

# Install Extensions
echo "Configuring pdo_mysql and gd extensions..."
docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd > /dev/null
docker-php-ext-configure gd --with-freetype-dir=/usr/include/ \
                                 --with-png-dir=/usr/include/ \
                                 --with-jpeg-dir=/usr/include/ > /dev/null

echo "Installing PHP extensions..."
php_extensions_count=${#php_extensions[@]}
i=0
draw_progress_bar $i ${php_extensions_count} "extensions"
for ext in ${php_extensions[*]}; do
  docker-php-ext-install ${ext} > /dev/null
  i=$((i+1))
  draw_progress_bar $i ${php_extensions_count} "extensions"
done
echo

# Install extra extensions
echo "Installing PECL extensions..."
pecl_extensions_count=${#pecl_extensions[@]}
i=0
draw_progress_bar $i ${pecl_extensions_count} "extensions"
for ext in redis mongodb xdebug; do
  pecl install ${ext} > /dev/null
  i=$((i+1))
  draw_progress_bar $i ${pecl_extensions_count} "extensions"
done
echo
########## END OF DO NOT EDIT AREA #########