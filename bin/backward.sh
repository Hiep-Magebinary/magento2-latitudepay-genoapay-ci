#!/usr/bin/env bash
echo "Running Backward Compatibility Check"
docker-compose exec magento \
	vendor/bin/roave-backward-compatibility-check
	$*