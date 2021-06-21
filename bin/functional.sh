
#!/usr/bin/env bash
echo "Running the functional tests"
docker-compose exec magento \
	vendor/bin/mftf generate:tests
docker-compose exec magento \
	vendor/bin/mftf run:test AdminLoginSuccessfulTest --remove
	$*