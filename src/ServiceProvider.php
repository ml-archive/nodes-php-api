<?php
namespace Nodes\Api;

use Dingo\Api\Event\RequestWasMatched as DingoEventRequestWasMatched;
use Dingo\Api\Http\Request as DingoHttpRequest;
use Nodes\AbstractServiceProvider as NodesAbstractServiceProvider;
use Nodes\Api\Http\Middleware\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Response as NodesHttpResponse;
use Nodes\Api\Support\Traits\DingoServiceProvider;
use Nodes\Api\Support\Traits\DingoLaravelServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Nodes\Api
 */
class ServiceProvider extends NodesAbstractServiceProvider
{
    use DingoServiceProvider, DingoLaravelServiceProvider;

    /**
     * Package name
     *
     * @var string
     */
    protected $package = 'api';

    /**
     * Register Artisan commands
     *
     * @var array
     */
    protected $commands = [
        \Nodes\Api\Console\Commands\Scaffolding::class,
        \Nodes\Api\Console\Commands\ResetPassword::class,
    ];

    /**
     * Facades to install
     *
     * @var array
     */
    protected $facades = [
        'NodesAPI' => \Nodes\Api\Support\Facades\API::class,
        'NodesAPIRoute' => \Nodes\Api\Support\Facades\Route::class,
    ];

    /**
     * Array of configs to copy
     *
     * @var array
     */
    protected $configs = [
        'config/' => 'config/nodes/api/',
    ];

    /**
     * Boot the service provider
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Set response static instances
        $this->setResponseStaticInstances();

        // Configure the "Accept"-header parser
        DingoHttpRequest::setAcceptParser($this->app['Dingo\Api\Http\Parser\Accept']);

        // Rebind API router
        $this->app->rebinding('api.routes', function ($app, $routes) {
            $app['api.url']->setRouteCollections($routes);
        });

        // Initiate HTTP kernel
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');

        /// Add middlewares to HTTP request
        $this->app[NodesHttpMiddlewareRequest::class]->mergeMiddlewares(
            $this->gatherAppMiddleware($kernel)
        );

        // Prepend request middleware
        $this->addRequestMiddlewareToBeginning($kernel);

        // Replace route dispatcher
        $this->app['events']->listen(DingoEventRequestWasMatched::class, function (DingoEventRequestWasMatched $event) {
            $this->replaceRouteDispatcher();
            $this->updateRouterBindings();
        });

        // Load project routes
        $this->loadRoutes();

        // Register namespace for API views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nodes.api');
    }

    /**
     * Register the service provider
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    public function register()
    {
        // Dingo service provider
        $this->registerServiceProvider();

        // Dingo Laravel service provider
        $this->registerLaravelServiceProvider();
    }

    /**
     * Load project API routes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @return void
     */
    private function loadRoutes()
    {
        // Generate routes directory path
        $routesDirectory = base_path('project/Routes/');

        // Make sure our directory exists
        if (!file_exists($routesDirectory)) {
            return;
        }

        // Load routes in directory
        load_directory($routesDirectory);
    }

    /**
     * Install scaffolding
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function installScaffolding()
    {
        if (env('NODES_ENV', false)) {
            $this->getInstaller()->callArtisanCommand('nodes:api:scaffolding');
        }
    }

    /**
     * Install custom stuff
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function installCustom()
    {
        // Add "api/" to except array in "VerifyCsrfToken" middlware
        // to always bypass the CSRF token validation on POST requests
        $this->bypassCsrfToken();
    }

    /**
     * Bypass CSRF validation for API routes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @return boolean
     */
    private function bypassCsrfToken()
    {
        $file = file(app_path('Http/Middleware/VerifyCsrfToken.php'));

        $locateExceptArray = array_keys(preg_grep('|protected \$except = \[|', $file));
        if (empty($locateExceptArray[0])) {
            return false;
        }

        // Bypass URL
        $bypassUrl = 'api/*';

        for ($i = $locateExceptArray[0]+2; $i < count($file); $i++) {
            // Remove whitespace from line
            $value = trim($file[$i]);

            if (!empty($value)) {
                // If we're on the outcommented line (which is there out-of-the-box)
                // we'll replace this line instead of inserting it before.
                if ($value == '//') {
                    $file[$i] =  str_repeat("\t", 2) . sprintf('\'%s\',', $bypassUrl) . "\n";
                    break;
                }

                // Remove single quotes from URL for comparison
                $currentBypassUrl = substr($value, 1, strrpos($value, '\''));

                // If we're on the last line of the $except array
                // or if our bypass URL comes before current line
                // - if sorted alphabetically - we'll insert on this line
                if ($value == '];' || strnatcmp($currentBypassUrl, $bypassUrl) > 0) {
                    array_splice($file, $i, 0, [
                        str_repeat("\t", 2) . sprintf('\'%s\',', $bypassUrl) . "\n"
                    ]);
                    break;
                }
            }
        }

        // Update existing file
        file_put_contents(app_path('Http/Middleware/VerifyCsrfToken.php'), implode('', $file));

        return true;
    }
}
