#!/usr/bin/env bash
echo "Running the unit tests..."
docker-compose exec magento \
	vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Latitude/Payment/Test/Unit/ --testdox
	$*