<?php

namespace Nodes\Api\Console\Commands;

use Dingo\Api\Console\Command\Docs as DingoCommandDocs;

/**
 * Class Docs.
 */
class Docs extends DingoCommandDocs
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nodes:api:docs {--name= : Name of the generated documentation}
                                           {--use-version= : Version of the documentation to be generated}
                                           {--output-file= : Output the generated documentation to a file}';
}
