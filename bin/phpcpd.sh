#!/usr/bin/env bash
echo "Running the phpcpd"
docker-compose exec magento \
	phpcpd --exclude vendor --exclude tests .
	$*