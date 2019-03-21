#!/usr/bin/env bash

uid=`id -u`
gid=`id -g`

pushd docker

docker-compose up -d \
    && while [[ "$(docker inspect --format='{{.State.Running}}' poker-faces-php)" != "true" ]]; do sleep 1; done \
    && docker exec -it poker-faces-php /bin/bash -c "if [[ ! -d vendor ]]; then composer install --no-dev ; fi && chown -R ${uid}:${gid} . && exit" \
    && docker exec -it poker-faces-php /bin/bash
popd
