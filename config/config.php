<?php

    // Set environment based on hostname
    $hostname = php_uname("n");
    switch ($hostname) {

        // PROD
        case "api.mywebsite.com":
            $env = 'PROD';
        break;

        // LOCAL
        default:
            $env = 'DEV';
    }

    // application
    $application = array(
        'controllersDir'     => ROOT_PATH . '/controllers',
        'modelsDir'          => ROOT_PATH . '/models',
        'logDir'             => ROOT_PATH . '/logs',
        'baseUri'            => '/phalcon-rest-boilerplate',
        'baseUrl'            => 'https://api.mywebsite.com',
        'appTitle'           => 'Phalcon REST Boilerplate',
        'appName'            => 'phalcon-rest-boilerplate',
        'env'                => $env,
        'debug'              => '0',
        'securitySalt'       => 'bF37nUyTb9bqfcHKPcDq',
        'apiWhiteListIps'    => array( // List of trusted IP addresses
            '10.10.10.10'
        )
    );

    // routes
    $routes = array(
         /******************/
        // Identity namespace
        // options
        array(
            'route'      => '/v1/identity/token',
            'method'     => 'options',
            'controller' => 'IdentityController',
            'action'     => 'optionsAction'
        ),
        // get token
        array(
            'route'      => '/v1/identity/token',
            'method'     => 'get',
            'controller' => 'IdentityController',
            'action'     => 'getTokenAction'
        ),

        /******************/
        // User namespace
        
        // list user
        array(
            'route'      => '/v1/user/{id:[a-zA-Z0-9]+}',
            'method'     => 'options',
            'controller' => 'UserController',
            'action'     => 'optionsAction'
        ),
        array(
            'route'      => '/v1/user/{id:[a-zA-Z0-9]+}',
            'method'     => 'get',
            'controller' => 'UserController',
            'action'     => 'getAction'
        ),
        
        // create new user
        array(
            'route'      => '/v1/user',
            'method'     => 'options',
            'controller' => 'UserController',
            'action'     => 'optionsAction'
        ),
        array(
            'route'      => '/v1/user',
            'method'     => 'post',
            'controller' => 'UserController',
            'action'     => 'addAction'
        ),

        // update user
        array(
            'route'      => '/v1/user/{id:[a-zA-Z0-9]+}',
            'method'     => 'options',
            'controller' => 'UserController',
            'action'     => 'optionsAction'
        ),
        array(
            'route'      => '/v1/user/{id:[a-zA-Z0-9]+}',
            'method'     => 'put',
            'controller' => 'UserController',
            'action'     => 'updateAction'
        ),
        
        // delete user
        array(
            'route'      => '/v1/user/{id:[a-zA-Z0-9]+}',
            'method'     => 'delete',
            'controller' => 'UserController',
            'action'     => 'deleteAction'
        )
    );

    // Database settings
    $database['connectionString'] = 'mongodb://db_username:db_password' .
                                    '@db_host_1:db_port_1,' .
                                    'db_host_2:db_port_2' .
                                    '/phalcon-rest-boilerplate?replicaSet=rs_db';
    $database['cafile']           = '/path/to/cert.pem';
    $database['dbname']           = 'phalcon-rest-boilerplate';


    // Environment based settings
    switch ( $env ) {
        
        case 'DEV':
            
            // database
            $database['connectionString']   = 'mongodb://127.0.0.1:27017/phalcon-rest-boilerplate';
            $database['cafile']             = null;

            // application overrides
            $application['debug']           = '1';
            
            // Accept requests from these server IP addresses
            $application['apiWhiteListIps'] = array(
                '127.0.0.1'
            );

        break;
        
    }

    return array(
        'application'      => $application,
        'database'         => $database,
        'routes'           => $routes
    );
