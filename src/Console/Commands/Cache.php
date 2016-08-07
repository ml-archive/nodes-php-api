<?php

namespace Nodes\Api\Console\Commands;

use Dingo\Api\Console\Command\Cache as DingoCommandCache;

/**
 * Class Cache.
 */
class Cache extends DingoCommandCache
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    public $signature = 'nodes:api:cache';
}
