#!/usr/bin/env bash
set -e
echo "Start docker"
export DOCKER_CLIENT_TIMEOUT=300
export COMPOSE_HTTP_TIMEOUT=300
docker-compose up -d