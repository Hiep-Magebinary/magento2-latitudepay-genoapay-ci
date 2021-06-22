#!/bin/bash
export SSHPASS=$SSH_REMOTE_SERVER_PASSWORD
export DEPLOY_BRANCH=$SSH_REMOTE_SERVER_GIT_BRANCH
INVALID_PARAMETERS="\033[1;31mError:\033[0m Please make sure you've indicated correct parameters."

# Path to plugin directory on the remote server
PLUGIN_DIR="${SSH_REMOTE_SERVER_ROOT}/app/code/Latitude/Payment"

# Set Remote server SSH credentials (If this is a public Repo, you will want to set these as CircleCI environment variables)
SSH_CREDS="${SSH_REMOTE_SERVER_USER}@${SSH_REMOTE_SERVER_HOST}"
SSH_EXTRA="-o PubkeyAuthentication=no -o StrictHostKeyChecking=no"

# Add the server IP to the known_hosts file
# Required for CircleCI to allow SSH connections to remote server
# example: ssh-keyscan 123.456.789.123 >> ~/.ssh/known_hosts
#ssh-keyscan ${SSH_REMOTE_SERVER_IP} >> ~/.ssh/known_hosts
cd /var/www/html/app/code/Latitude/Payment

if [ $# -eq 0 ]
	then
		echo -e ${INVALID_PARAMETERS}
elif [ $1 == "--dry-run" ]
	then
		echo "Running dry-run deployment."
		#SSH_COMMAND="ssh ${SSH_EXTRA} -p ${SSH_REMOTE_SERVER_PORT}"
		BUILD_COMMAND="cd ${PLUGIN_DIR} && git checkout ${DEPLOY_BRANCH} && git reset --hard origin/${DEPLOY_BRANCH} && git pull origin ${DEPLOY_BRANCH} && cd ${SSH_REMOTE_SERVER_ROOT} && bin/magento module:enable Latitude_Payment && bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento setup:static-content:deploy -f && bin/magento cache:enable"
		
		#sshpass -e ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT} "mkdir -p ${PLUGIN_DIR}"
		#sshpass -e ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT} "rm -rf ${PLUGIN_DIR}/*"
		#sshpass -e rsync --exclude={'.git/','bin/','.docker/','vendor/','log/','tests/*','.vscode/','.circleci/','.idea/'} --stats --dry-run --delete --progress -avz -e $SSH_COMMAND ./ ${SSH_REMOTE_SERVER_USER}@${SSH_REMOTE_SERVER_HOST}:${PLUGIN_DIR}
		#sshpass -e rsync --exclude={'.git/','bin/','.docker/','vendor/','log/','tests/*','.vscode/','.circleci/','.idea/'} --stats --dry-run --delete --progress -avz -e $SSH_COMMAND ./ ${SSH_REMOTE_SERVER_USER}@${SSH_REMOTE_SERVER_HOST}:${PLUGIN_DIR}
		sshpass -e ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT} $BUILD_COMMAND
elif [ $1 == "live" ]
	then
		echo "Running actual deploy"
		BUILD_COMMAND="cd ${PLUGIN_DIR} && git checkout ${DEPLOY_BRANCH} && git reset --hard origin/${DEPLOY_BRANCH} && git pull origin ${DEPLOY_BRANCH} && cd ${SSH_REMOTE_SERVER_ROOT} && bin/magento module:enable Latitude_Payment && bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento setup:static-content:deploy -f && bin/magento cache:enable"
		#sshpass -p ${SSH_REMOTE_SERVER_PASSWORD} ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT} 'mkdir -p ${PLUGIN_DIR}'
		#sshpass -p ${SSH_REMOTE_SERVER_PASSWORD} ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT} 'rm -rf ${PLUGIN_DIR}/*'
		#rsync --exclude={'.git/','bin/','.docker/','vendor/','log/','tests/','.vscode/','.circleci/','.idea/'} --stats --delete -avz -e "sshpass -p'${SSH_REMOTE_SERVER_PASSWORD}' ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT}" ./ ${SSH_CREDS}:${PLUGIN_DIR}
		sshpass -e ssh ${SSH_EXTRA} ${SSH_CREDS} -p${SSH_REMOTE_SERVER_PORT} $BUILD_COMMAND
else
	echo -e ${INVALID_PARAMETERS};
fi