#!/bin/sh
cd /app
touch .env

echo "##################### START MY SHIT ####################"
echo "Setting FILE: .env"


echo "APP_NAME=${APP_NAME}" >> .env
echo "APP_ENV=${APP_ENV}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_DEBUG=${APP_DEBUG}" >> .env
echo "APP_URL=${APP_URL}" >> .env
echo "APP_FRONTEND_URL_FOR_DEVELOPMENT=${APP_FRONTEND_URL_FOR_DEVELOPMENT}" >> .env
echo "APP_FRONTEND_URL_FOR_PRESTAGING=${APP_FRONTEND_URL_FOR_PRESTAGING}" >> .env
echo "APP_FRONTEND_URL_FOR_STAGING=${APP_FRONTEND_URL_FOR_STAGING}" >> .env
echo "APP_FRONTEND_URL_FOR_DEPLOYMENT=${APP_FRONTEND_URL_FOR_DEPLOYMENT}" >> .env
echo "LOG_CHANNEL=${LOG_CHANNEL}" >> .env
echo "LOG_LEVEL=${LOG_LEVEL}" >> .env
echo "DB_CONNECTION=${DB_CONNECTION}" >> .env
echo "DB_HOST=${DB_HOST}" >> .env
echo "DB_PORT=${DB_PORT}" >> .env
echo "DB_DATABASE=${DB_DATABASE}" >> .env
echo "DB_USERNAME=${DB_USERNAME}" >> .env
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
echo "BROADCAST_DRIVER=${BROADCAST_DRIVER}" >> .env
echo "CACHE_DRIVER=${CACHE_DRIVER}" >> .env
echo "FILESYSTEM_DRIVER=${FILESYSTEM_DRIVER}" >> .env
echo "QUEUE_CONNECTION=${QUEUE_CONNECTION}" >> .env
echo "SESSION_DRIVER=${SESSION_DRIVER}" >> .env
echo "SESSION_LIFETIME=${SESSION_LIFETIME}" >> .env
echo "MEMCACHED_HOST=${MEMCACHED_HOST}" >> .env
echo "REDIS_CLIENT=${REDIS_CLIENT}" >> .env
echo "REDIS_HOST=${REDIS_HOST}" >> .env
echo "REDIS_PASSWORD=${REDIS_PASSWORD}" >> .env
echo "REDIS_PORT=${REDIS_PORT}" >> .env
echo "REDIS_PRIMARY_PROD=${REDIS_PRIMARY_PROD}" >> .env
echo "REDIS_READER_PROD=${REDIS_READER_PROD}" >> .env
echo "REDIS_PASSWORD=${REDIS_PASSWORD}" >> .env
echo "REDIS_PORT=${REDIS_PORT}" >> .env
echo "REDIS_PRIMARY=${REDIS_PRIMARY}" >> .env
echo "REDIS_READER=${REDIS_READER}" >> .env
echo "MAIL_MAILER=${MAIL_MAILER}" >> .env
echo "MAIL_HOST=${MAIL_HOST}" >> .env
echo "MAIL_PORT=${MAIL_PORT}" >> .env
echo "MAIL_USERNAME=${MAIL_USERNAME}" >> .env
echo "MAIL_PASSWORD=${MAIL_PASSWORD}" >> .env
echo "MAIL_ENCRYPTION=${MAIL_ENCRYPTION}" >> .env
echo "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}" >> .env
echo "MAIL_FROM_NAME=${MAIL_FROM_NAME}" >> .env
echo "AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}" >> .env
echo "AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}" >> .env
echo "AWS_DEFAULT_REGION=${AWS_DEFAULT_REGION}" >> .env
echo "AWS_BUCKET=${AWS_BUCKET}" >> .env
echo "AWS_USE_PATH_STYLE_ENDPOINT=${AWS_USE_PATH_STYLE_ENDPOINT}" >> .env
echo "PUSHER_APP_ID=${PUSHER_APP_ID}" >> .env
echo "PUSHER_APP_KEY=${PUSHER_APP_KEY}" >> .env
echo "PUSHER_APP_SECRET=${PUSHER_APP_SECRET}" >> .env
echo "PUSHER_APP_CLUSTER=${PUSHER_APP_CLUSTER}" >> .env
echo "MIX_PUSHER_APP_KEY=${MIX_PUSHER_APP_KEY}" >> .env
echo "MIX_PUSHER_APP_CLUSTER=${MIX_PUSHER_APP_CLUSTER}" >> .env
echo "SQS_AWS_ACCESS_KEY_ID=${SQS_AWS_ACCESS_KEY_ID}" >> .env
echo "SQS_AWS_SECRET_ACCESS_KEY=${SQS_AWS_SECRET_ACCESS_KEY}" >> .env
echo "SQS_PREFIX=${SQS_PREFIX}" >> .env
echo "SQS_QUEUE=${SQS_QUEUE}" >> .env
echo "QUEUE_FAILED_DRIVER=${QUEUE_FAILED_DRIVER}" >> .env
echo "QUEUE_FAILED_AWS_ACCESS_KEY_ID=${QUEUE_FAILED_AWS_ACCESS_KEY_ID}" >> .env
echo "QUEUE_FAILED_AWS_SECRET_ACCESS_KEY=${QUEUE_FAILED_AWS_SECRET_ACCESS_KEY}" >> .env
echo "QUEUE_FAILED_AWS_DEFAULT_REGION=${QUEUE_FAILED_AWS_DEFAULT_REGION}" >> .env
echo "QUEUE_FAILED_AWS_BUCKET=${QUEUE_FAILED_AWS_BUCKET}" >> .env
echo "QUEUE_FAILED_AWS_USE_PATH_STYLE_ENDPOINT=${QUEUE_FAILED_AWS_USE_PATH_STYLE_ENDPOINT}" >> .env
echo "QUEUE_FAILED_DYNAMODB_TABLE=${QUEUE_FAILED_DYNAMODB_TABLE}" >> .env
echo "QUEUE_FAILED_DYNAMODB_ENDPOINT=${QUEUE_FAILED_DYNAMODB_ENDPOINT}" >> .env
echo "EASYPOST_PK=${EASYPOST_PK}" >> .env
echo "EASYPOST_TK=${EASYPOST_TK}" >> .env
echo "PASSPORT_GRANT_PASSWORD_CLIENT_ID=${PASSPORT_GRANT_PASSWORD_CLIENT_ID}" >> .env
echo "PASSPORT_GRANT_PASSWORD_CLIENT_SECRET=${PASSPORT_GRANT_PASSWORD_CLIENT_SECRET}" >> .env
echo "WEB_DOCUMENT_ROOT=${WEB_DOCUMENT_ROOT}" >> .env


# NOTE: In order for this line to work, the last command "php artisan config:clear" should be uncommented and run.
echo "MY_RANDOM_CONTAINER_NUMBER=${RANDOM}" >> .env


echo "PASSPORT_PRIVATE_KEY=\"" >> .env
cat /app/storage/oauth-private.key >> .env
echo "\"" >> .env


echo "PASSPORT_PUBLIC_KEY=\"" >> .env
cat /app/storage/oauth-public.key >> .env
echo "\"" >> .env


echo "FILE: .env has been set."


echo "Changing file ownerships..."
chown -R application:application .

echo "File ownerships have been set."
echo "##################### END MY SHIT ####################"



echo "Caching Laravel stuffs..."

php artisan config:cache
php artisan route:cache
php artisan view:cache
# php artisan config:clear


echo "Laravel stuffs cached!"