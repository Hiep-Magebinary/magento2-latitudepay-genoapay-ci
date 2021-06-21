#!/usr/bin/env bash
echo "Running the phpcbf"
docker-compose exec magento \
	phpcbf --standard=phpcs.xml.dist --extensions=php --ignore=*/vendor/,*/tests/ --colors -s -p -v ./
	$*