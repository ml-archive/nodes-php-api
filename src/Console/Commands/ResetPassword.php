<?php
namespace Nodes\Api\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\MountManager;

/**
 * Class ResetPassword
 *
 * @package Nodes\Api\Console\Commands
 */
class ResetPassword extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'nodes:api:reset-password';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Generate Nodes API reset password scaffolding';

    /**
     * Laravel filesystem
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Project folder name
     *
     * @var string
     */
    protected $projectFolderName;

    /**
     * Path to project folder
     *
     * @var string
     */
    protected $projectFolderPath;

    /**
     * GenerateScaffolding constructor
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \Illuminate\Filesystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Generate scaffolding
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    public function handle()
    {
        // Ask for confirmation
        if (!$this->confirm('Do you wish to copy Nodes API reset password scaffolding? <comment>Note: Existing files will be overwritten.</comment>', true)) {
            return false;
        }

        // Set project folder and path
        $this->projectFolderName = $projectFolderName = 'project';
        $this->projectFolderPath = base_path($projectFolderName);

        // Copy routes to project
        $this->copyRoutesToProject();

        // Copy views to project
        $this->copyViewsToProject();

        // Copy assets to project
        $this->copyAssetsToProject();

        // Copy migration and run it
        $this->copyAndRunMigration();
    }

    /**
     * Copy reset password routes to project
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function copyRoutesToProject()
    {
        // Output to console
        $this->comment('Copying reset password routes ...');

        // Copy reset password API routes
        $this->copyFile(base_path('vendor/nodes/api/routes/api.php'), sprintf('%s/%s/%s/reset-password.php', $this->projectFolderPath, 'Routes', 'Api'));

        // Copy reset password frontend routes
        $this->copyFile(base_path('vendor/nodes/api/routes/frontend.php'), sprintf('%s/%s/%s/reset-password.php', $this->projectFolderPath, 'Routes', 'Frontend'));
    }

    /**
     * Copy reset password views to project
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function copyViewsToProject()
    {
        // Output to console
        $this->comment('Copying reset password views ...');

        // Copy reset password views
        $this->copyDirectory(base_path('vendor/nodes/api/resources/views/reset-password'), resource_path('views/vendor/nodes.api/reset-password'));
    }

    /**
     * Copy reset password assets to project
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function copyAssetsToProject()
    {
        // Output to console
        $this->comment('Copying reset password assets ...');

        // Copy reset password views
        $this->copyDirectory(base_path('vendor/nodes/api/resources/assets'), public_path('vendor/nodes/api'));
    }

    /**
     * Copy reset password database migration file and run it
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function copyAndRunMigration()
    {
        // Output to console
        $this->comment('Copying reset password database migration ...');

        // Copy reset password views
        $this->copyDirectory(base_path('vendor/nodes/api/database/migrations/reset-password'), database_path('migrations'));

        // Output to console
        $this->comment('Running reset password database migration ...');

        // Run migration file
        $this->call('migrate');
    }

    /**
     * Publish file to application
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  string $from
     * @param  string $to
     * @return void
     */
    protected function copyFile($from, $to)
    {
        // If destination directory doesn't exist,
        // we'll create before copying the config files
        $directoryDestination = dirname($to);
        if (!$this->filesystem->isDirectory($directoryDestination)) {
            $this->filesystem->makeDirectory($directoryDestination, 0755, true);
        }

        // Copy file to application
        $this->filesystem->copy($from, $to);

        // Output status message
        $this->line(
            sprintf('<info>Copied %s</info> <comment>[%s]</comment> <info>To</info> <comment>[%s]</comment>',
            'File', str_replace(base_path(), '', realpath($from)), str_replace(base_path(), '', realpath($to)))
        );
    }

    /**
     * Publish directory to application
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  string $from
     * @param  string $to
     * @return void
     */
    protected function copyDirectory($from, $to)
    {
        // Load mount manager
        $manager = new MountManager([
            'from' => new Flysystem(new LocalAdapter($from)),
            'to' => new Flysystem(new LocalAdapter($to)),
        ]);

        // Copy directory to application
        foreach ($manager->listContents('from://', true) as $file) {
            if ($file['type'] !== 'file') {
                continue;
            }
            $manager->put(sprintf('to://%s', $file['path']), $manager->read(sprintf('from://%s', $file['path'])));
        }

        // Output status message
        $this->line(
            sprintf('<info>Copied %s</info> <comment>[%s]</comment> <info>To</info> <comment>[%s]</comment>',
            'Directory', str_replace(base_path(), '', realpath($from)), str_replace(base_path(), '', realpath($to)))
        );
    }
}