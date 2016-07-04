<?php
namespace Nodes\Api\Console\Commands;

use Dingo\Api\Console\Command\Routes as DingoCommandRoutes;

/**
 * Class Routes
 *
 * @package Nodes\Api\Console\Commands
 */
class Routes extends DingoCommandRoutes
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    public $name = 'nodes:api:routes';
}