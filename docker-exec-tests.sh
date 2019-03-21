#!/usr/bin/env bash

uid=`id -u`
gid=`id -g`

pushd docker

docker-compose up -d \
    && while [[ "$(docker inspect --format='{{.State.Running}}' poker-faces-php)" != "true" ]]; do sleep 1; done \
    && docker exec -it poker-faces-php /bin/bash -c "composer install --dev && chown -R ${uid}:${gid} . && exit" \
    && docker exec -it poker-faces-php /bin/bash -c "vendor/bin/codecept run && chown -R ${uid}:${gid} . && exit"

popd
