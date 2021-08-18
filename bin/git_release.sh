#!/usr/bin/env bash
cd ..
set -e
SLUG=${PWD##*/}
EXISTING_TAGS=(`echo $(git tag -l)`);
COMPOSER_TAG=$(grep -o '^ *"version": *"[0-9\.]*"' composer.json|awk '{print $2}'|sed -e 's/"\(.*\)"/\1/g')
for constant in $EXISTING_TAGS
do
  if [ "$constant" = "$COMPOSER_TAG" ]; then
    echo "The tag exists already: ${COMPOSER_TAG}";
    exit;
  fi

done
echo "Create new tag: ${COMPOSER_TAG}"
CHANGELOG_JSON="Create new tag: ${COMPOSER_TAG}"
API_JSON=$(printf '{"tag_name": "%s","target_commitish": "ci-cd","name": "%s","body": "%s","draft": false,"prerelease": false}' $COMPOSER_TAG $COMPOSER_TAG "$CHANGELOG_JSON")

curl --data "$API_JSON" https://api.github.com/repos/${GITHUB_USER}/${SLUG}/releases?access_token=${GITHUB_TOKEN}

echo "*** FIN ***"