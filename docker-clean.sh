#!/usr/bin/env bash

pushd docker

docker exec -it poker-faces-php /bin/bash -c "rm -rf vendor/ tests/_output/*.serialized tests/_support/_generated/*.php && exit" \
    && docker-compose down

popd
