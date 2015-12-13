<?php
/**
 * Bootstraps the application
 */
use Phalcon\DI\FactoryDefault as PhDi;
use Phalcon\Loader as PhLoader;
use Phalcon\Config as PhConfig;
use Phalcon\Logger\Adapter\File as PhLogFileAdapter;
use Phalcon\Debug as PhDebug;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Collection\Manager as CollectionManager;
use Phalcon\Exception as PhException;
use Phalcon\Security;

class Bootstrap
{

    private $di;

    private $app;

    /**
    * Constructor
    *
    * @param $di
    */
    public function __construct( $di )
    {
        $this->di = $di;
    }

    /**
    * Runs the application performing all initializations
    *
    * @return mixed
    */
    public function run()
    {
        $loaders = array(
            'config',
            'loader',
            'router',
            'database',
            'log',
            'security'
        );

        try {

            // create new micro app
            $this->app = new \Phalcon\Mvc\Micro();

            // set dependency injector for the application
            $this->app->setDI($this->di);

            // process loaders
            foreach ( $loaders as $service ) {
                $function = 'init' . ucfirst($service);
                $this->$function();
            }

            return $this->app->handle();

        } catch (PhException $e) {
            echo $e->getMessage();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
    * Initializes the config. Reads it from its location and
    * stores it in the Di container for easier access
    */
    protected function initConfig()
    {
        // __DIR__ inside config directory
        $configFile = require_once(__DIR__ . '/config.php');

        // Create the new object
        $config = new PhConfig($configFile);

        // Store it in the Di container
        // Settings cones from the include
        $this->di['config'] = $config;

    }

    /**
    * Initializes the loader
    */
    protected function initLoader()
    {
        
        $config = $this->di['config'];

        // Creates the autoloader
        $loader = new PhLoader();
        $loader->registerDirs(
            array(
                $config->application->controllersDir,
                $config->application->modelsDir
            )
        );

        $loader->register();

        // Dump it in the DI to reuse it
        $this->di['loader'] = $loader;
    }

    /**
    * Initializes the router
    */
    protected function initRouter()
    {

        $config = $this->di['config'];

        // Not-Found Handler
        $this->app->notFound(function() {
             
            // Create a response
            $response = new Phalcon\Http\Response();

            // Set the Content-Type header
            $response->setContentType('application/json');
            $response->setStatusCode(404, "Not Found");
            $response->setContent(json_encode(array(
                    'status'   => 'failure',
                    'data'     => array(),
                    'error'    => array(
                        'code' => 404,
                        'message' => 'Invalid endpoint request'
                    )
                ))
            );
            return $response;
        });

        // Define the routes from config
        foreach ($config['routes'] as $items) {

            // load routes based on controller/action
            $className = $items['controller'];
            $myController = new $className();

            $this->app->$items['method']($items['route'], array(
                $myController, 
                $items['action']
            ));

        }

    }

    /**
    * Initializes the database
    */
    protected function initDatabase()
    {
        
        $config = $this->di['config'];
        $this->di->set('mongo', function () use ($config) {

            // check if use ssl
            if ( !empty($config->database->cafile) ) {
                $ctx = stream_context_create(array(
                    "ssl" => array(
                        /* Certificate Authority the remote server certificate must be signed by */
                        "cafile"            => $config->database->cafile,

                        /* Disable self signed certificates */
                        "allow_self_signed" => false,

                        /* Verify the peer certificate against our provided Certificate Authority root certificate */
                        "verify_peer"       => true, /* Default to false pre PHP 5.6 */

                        /* Verify the peer name (e.g. hostname validation) */
                        /* Will use the hostname used to connec to the node */
                        "verify_peer_name"  => true,

                        /* Verify the server certificate has not expired */
                        "verify_expiry"     => true, /* Only available in the MongoDB PHP Driver */
                    ),
                ));

                $mongo = new MongoClient(
                    $config->database->connectionString,
                    array("ssl" => true),
                    array("context" => $ctx)
                );

            } else {

                // without ssl
                $mongo = new MongoClient(
                    $config->database->connectionString
                );

            }

            return $mongo->selectDB($config->database->dbname);

        }, true);

        // set collectionManager
        $this->di->set('collectionManager', function(){
            return new Phalcon\Mvc\Collection\Manager();
        }, true);

    }

    /**
    * Initializes the file logger
    */
    protected function initLog()
    {

        $config = $this->di['config'];

        $this->di['logger'] = function () use ($config)
        {

            $logger = new PhLogFileAdapter($config->application->logDir . "/app.log");

            return $logger;

        };
    }

    /**
    * Initializes security
    */
    protected function initSecurity()
    {

        $config = $this->di['config'];

        $this->di['security'] = function () use ($config)
        {

            $security = new Security();

            return $security;

        };
    }

}
