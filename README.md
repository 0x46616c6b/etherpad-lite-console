# Etherpad Lite Console (PHP)

**A thin console toolkit to maintain an etherpad lite instance**

## Installation

    git clone https://github.com/0x46616c6b/etherpad-lite-console.git
    
    cd etherpad-lite-console
    
    composer install

    ./bin/console

Sample Output

    Etherpad Lite Console version 0.1
    
    Usage:
      [options] command [arguments]
    
    Options:
      --help           -h Display this help message.
      --quiet          -q Do not output any message.
      --verbose        -v|vv|vvv Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
      --version        -V Display this application version.
      --ansi              Force ANSI output.
      --no-ansi           Disable ANSI output.
      --no-interaction -n Do not ask any interactive question.
    
    Available commands:
      help                  Displays help for a command
      list                  Lists commands
    pad
      pad:delete            Delete a pad
      pad:purge             Purge pads which older then x days
    redis
      redis:import:sqlite   Imports a sqlite database to redis

## Current features

* Delete a pad
* Purge old pads (*avoid massive data retention*)
* Migration
  * From SQLite to Redis

## Implementation

* based on the [Symfony Console Component](http://symfony.com/doc/current/components/console/introduction.html)
* Dependencies:
   * [symfony/console](https://packagist.org/packages/predis/predis)
   * [predis/predis](https://packagist.org/packages/predis/predis)
   * [0x46616c6b/etherpad-lite-client](https://packagist.org/packages/0x46616c6b/etherpad-lite-client)

## Wishlist

* More Migration (MySQL -> Redis, ...)
* Stats, stats, stats
