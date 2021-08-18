#!/usr/bin/env bash
set -e
echo "Installing the test environment..."

docker-compose exec magento \
	/bin/app/magento2/install.sh

echo "Install composer packages"
docker-compose exec magento composer install --ignore-platform-reqs