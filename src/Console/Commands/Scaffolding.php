<?php
namespace Nodes\Api\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Class Scaffolding
 *
 * @package Nodes\Api\Console\Commands
 */
class Scaffolding extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'nodes:api:scaffolding';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Generate Nodes API scaffolding';

    /**
     * Laravel filesystem
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Project config
     *
     * @var array
     */
    protected $projectConfig;

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
        // Project config
        $this->projectConfig = $projectConfig = config('nodes.project', null);

        // Make sure we have a valid project config
        if (empty($projectConfig)) {
            $this->error(sprintf('Can not generate scaffolding without project details. Create [nodes.project] config.'));
            return;
        }

        // Set project folder and path
        $this->projectFolderName = $projectFolderName = 'project';
        $this->projectFolderPath = base_path($projectFolderName);

        // Run scaffolding ...
        if ($this->generateStructure()) {
            $this->generateScaffolding();
        }
    }

    /**
     * Generate project structure
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return boolean
     */
    protected function generateStructure()
    {
        // Confirm generation of project structure
        if (!$this->confirm('Do you wish to generate the Nodes API structure?', true)) {
            return false;
        }

        // Create project folder if it doesn't exist
        if (!$this->filesystem->exists($this->projectFolderPath)) {
            $this->filesystem->makeDirectory($this->projectFolderPath, 0755, true);
        }

        // Generate structure folders
        $this->generateControllersFolders();
        $this->generateModelsFolder();
        $this->generateRoutesFolder();

        return true;
    }

    /**
     * Generate controllers folders
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function generateControllersFolders()
    {
        // Output to console
        $this->comment('Creating controllers folders ...');

        // Create API controller folder and put .gitkeep in there
        $controllersApiPath = sprintf('%s/%s/%s', $this->projectFolderPath, 'Controllers', 'Api');
        if (!$this->filesystem->exists($controllersApiPath)) {
            $this->filesystem->makeDirectory($controllersApiPath, 0755, true);
            $this->generateGitKeep($controllersApiPath);
            $this->line(sprintf('<info>Created folder</info> <comment>[%s]</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Controllers', 'Api')));
        } else {
            $this->line(sprintf('<comment>Folder</comment> [%s] <comment>already exists</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Controllers', 'Frontend')));
        }

        // Create Frontend controller folder and put .gitkeep in there
        $controllersFrontendPath = sprintf('%s/%s/%s', $this->projectFolderPath, 'Controllers', 'Frontend');
        if (!$this->filesystem->exists($controllersFrontendPath)) {
            $this->filesystem->makeDirectory($controllersFrontendPath, 0755, true);
            $this->generateGitKeep($controllersFrontendPath);
            $this->line(sprintf('<info>Created folder</info> <comment>[%s]</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Controllers', 'Frontend')));
        } else {
            $this->line(sprintf('<comment>Folder</comment> [%s] <comment>already exists</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Controllers', 'Frontend')));
        }
    }

    /**
     * Generate models folder
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function generateModelsFolder()
    {
        // Output to console
        $this->comment('Creating models folder ...');

        // Create models folders and put .gitkeep in there
        $modelsPath = sprintf('%s/%s', $this->projectFolderPath, 'Models');
        if (!$this->filesystem->exists($modelsPath)) {
            $this->filesystem->makeDirectory($modelsPath, 0755, true);
            $this->generateGitKeep($modelsPath);
            $this->line(sprintf('<info>Created folder</info> <comment>[%s]</comment>', sprintf('%s/%s', $this->projectFolderName, 'Models')));
        } else {
            $this->line(sprintf('<comment>Folder</comment> [%s] <comment>already exists</comment>', sprintf('%s/%s', $this->projectFolderName, 'Models')));
        }
    }

    /**
     * Generate routes folder
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function generateRoutesFolder()
    {
        // Output to console
        $this->comment('Creating routes folders ...');

        // Create API routes folder and put .gitkeep in there
        $routesApiPath = sprintf('%s/%s/%s', $this->projectFolderPath, 'Routes', 'Api');
        if (!$this->filesystem->exists($routesApiPath)) {
            $this->filesystem->makeDirectory($routesApiPath, 0755, true);
            $this->generateGitKeep($routesApiPath);
            $this->line(sprintf('<info>Created folder</info> <comment>[%s]</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Routes', 'Api')));
        } else {
            $this->line(sprintf('<comment>Folder</comment> [%s] <comment>already exists</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Routes', 'Frontend')));
        }

        // Create Frontend doesroutes folder and put .gitkeep in there
        $routesFrontendPath = sprintf('%s/%s/%s', $this->projectFolderPath, 'Routes', 'Frontend');
        if (!$this->filesystem->exists($routesFrontendPath)) {
            $this->filesystem->makeDirectory($routesFrontendPath, 0755, true);
            $this->generateGitKeep($routesFrontendPath);
            $this->line(sprintf('<info>Created folder</info> <comment>[%s]</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Routes', 'Frontend')));
        } else {
            $this->line(sprintf('<comment>Folder</comment> [%s] <comment>already exists</comment>', sprintf('%s/%s/%s', $this->projectFolderName, 'Routes', 'Frontend')));
        }
    }

    /**
     * Generate .gitkeep file
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string $path
     * @return boolean
     */
    protected function generateGitKeep($path)
    {
        return (bool) $this->filesystem->put(sprintf('%s/%s', $path, '.gitkeep'), '');
    }

    /**
     * Generate API Scaffolding
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return boolean
     */
    protected function generateScaffolding()
    {
        if (!$this->confirm('Do you wish to generate Nodes API scaffolding? <comment>Note: Existing files will be overwritten.</comment>', true)) {
            return false;
        }

        $this->scaffoldUsersController();
        $this->scaffoldUserModel();
        $this->scaffoldUserRepository();
        $this->scaffoldUserValidator();
        $this->scaffoldUserTransformer();
        $this->scaffoldTokenModel();
        $this->scaffoldUserRoutes();

        return true;
    }

    /**
     * Scaffold users controller
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldUsersController()
    {
        // Output to console
        $this->comment('Generating Users Controller ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/UsersController.stub', dirname(__FILE__)),
            sprintf('%s/Controllers/Api/UsersController.php', $this->projectFolderPath)
        );
    }

    /**
     * Scaffold user model
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldUserModel()
    {
        // Output to console
        $this->comment('Generating User Model ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/UserModel.stub', dirname(__FILE__)),
            sprintf('%s/Models/Users/User.php', $this->projectFolderPath)
        );
    }

    /**
     * Scaffold user repository
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldUserRepository()
    {
        $this->comment('Generating User Repository ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/UserRepository.stub', dirname(__FILE__)),
            sprintf('%s/Models/Users/UserRepository.php', $this->projectFolderPath)
        );
    }

    /**
     * Scaffold user validator
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldUserValidator()
    {
        $this->comment('Generating User Validator ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/UserValidator.stub', dirname(__FILE__)),
            sprintf('%s/Models/Users/Validation/UserValidator.php', $this->projectFolderPath)
        );
    }

    /**
     * Scaffold user transformer
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldUserTransformer()
    {
        $this->comment('Generating User Transformer ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/UserTransformer.stub', dirname(__FILE__)),
            sprintf('%s/Models/Users/Transformers/UserTransformer.php', $this->projectFolderPath)
        );
    }

    /**
     * Scaffold token model
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldTokenModel()
    {
        $this->comment('Generating Token Model ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/TokenModel.stub', dirname(__FILE__)),
            sprintf('%s/Models/Users/Tokens/Token.php', $this->projectFolderPath)
        );
    }

    /**
     * Scaffold user routes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function scaffoldUserRoutes()
    {
        $this->comment('Generating User Routes ...');
        $this->generateStubFile(
            sprintf('%s/../Stubs/Scaffolding/UserRoutes.stub', dirname(__FILE__)),
            sprintf('%s/Routes/Api/users.php', $this->projectFolderPath)
        );
    }

    /**
     * Generate and save stub file
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  string $stub
     * @param  string $destination
     * @return void
     */
    private function generateStubFile($stub, $destination)
    {
        try {
            // Prepare template and replace namespace
            $template = $this->filesystem->get($stub);
            $template = $this->replaceNamespace($template);
        } catch (FileNotFoundException $e) {
            $this->error(sprintf('Could not locate file: %s', $stub));
            return;
        }

        // Retrieve folder for destination
        $destinationFolderPath = substr($destination, 0, strrpos($destination, '/'));

        // Create destination folder if it doesn't exist
        if (!$this->filesystem->exists($destinationFolderPath)) {
            $this->filesystem->makeDirectory($destinationFolderPath, 0755, true);
        }

        // Generate file and save it to project
        $this->filesystem->put($destination, $template);

        $this->line(sprintf('<info>Successfully created</info> <comment>[%s]</comment>', str_replace(base_path(), '', $destination)));
    }

    /**
     * Replace namespace in stub content
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @param  string $content
     * @return string
     */
    private function replaceNamespace($content)
    {
        return str_replace('DummyNamespace', config('nodes.project.namespace', 'App'), $content);
    }
}