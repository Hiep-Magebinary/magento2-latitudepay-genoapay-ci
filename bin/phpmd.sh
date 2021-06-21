#!/usr/bin/env bash
echo "Running the phpmd"
docker-compose exec magento \
	phpmd /var/www/html/app/code/Latitude/Payment text /var/www/html/app/code/Latitude/Payment/phpmd.xml --exclude vendor/,Tests/
	$*