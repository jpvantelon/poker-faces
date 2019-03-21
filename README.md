# Poker Faces - Poker Hands Comparator

## Direct execution using Docker

### Installation

Install Docker and docker-compose.

### Execute Comparator

Run `./docker-run.sh`, then, in Docker container, execute comparison with :
```$bash
bin/console poker:compare "<hand 1>" "<hand 2>"
```

Example :
```$bash
bin/console poker:compare "5C 6C aC 8C TC" "8S kS AD aH aS"
```

Get help with `bin/console poker:compare -h`.

### Run tests

Run `./docker-exec-tests.sh`.


### Remove Docker container

Run `./docker-clean.sh`.


## Local installation using PHP and Composer

### Installation

1. Install PHP 7.1.3+ and Composer
2. Run `composer install` (`composer install --no-dev` if don't want to run the tests)

### Execute Comparator

Execute comparison with :
```$bash
bin/console poker:compare "<hand 1>" "<hand 2>"
```

Example :
```$bash
bin/console poker:compare "5C 6C aC 8C TC" "8S kS AD aH aS"
```

Get help with `bin/console poker:compare -h`.


### Run tests

Run `vendor/bin/codecept run`.

Run `vendor/bin/codecept run --coverage` for coverage metrics.
