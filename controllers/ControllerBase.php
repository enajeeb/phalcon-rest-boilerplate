<?php

use Phalcon\Mvc\Controller,
    Phalcon\DI\FactoryDefault as PhDi;

class ControllerBase extends Controller
{

    public $config;

    public function initialize()
    {

        $di = PhDi::getDefault();

        // global config
        $this->config = $di['config'];

    }

    /**
    * Authenticate Request
    * @return boolean
    */
    public function authenticateRequest()
    {

        $this->initialize();

        // Check IP Whitelist for the request
        if ( !empty($this->config->application->apiWhiteListIps) && !empty($_SERVER['SERVER_ADDR']) ) {
            if ( !in_array($_SERVER['SERVER_ADDR'], (array) $this->config->application->apiWhiteListIps) ) {
                $this->logger->log(
                    sprintf(
                        'NAMESPACE=ControllerBase MESSAGE=IP address not in the whitelist REMOTE_IP=%s', 
                        $_SERVER['SERVER_ADDR']
                    ), 
                    \Phalcon\Logger::ERROR
                );
                return false;
            }
        }

        return true;
    }

    /**
    * Verify CSRF token provided in request header
    * @return boolean
    */
    public function verifyCsrfToken() {

        // verify CSRFToken
        $requestHeader = getallheaders();
        if ( !empty($requestHeader['X-CSRFToken']) ) {

            $sessionData = $this->getSessionDocument($requestHeader['X-CSRFToken']);

            if ( empty($sessionData) || $requestHeader['X-CSRFToken'] != $sessionData->csrfToken ) {
                $this->logger->log(
                    sprintf(
                        'NAMESPACE=ControllerBase MESSAGE=Invalid X-CSRFToken'
                    ), 
                    \Phalcon\Logger::ERROR
                );
                return false;
            } else {
                // extend session
                $this->updateSessionDocument($requestHeader['X-CSRFToken']);
            }
        } else {
            $this->logger->log(
                sprintf(
                    'NAMESPACE=ControllerBase MESSAGE=X-CSRFToken header not provided'
                ), 
                \Phalcon\Logger::ERROR
            );
            return false;
        }

        return true;
    }

    /**
    * Process response
    * @param mixed $options array('status', 'message', 'data', 'allow')
    * @return mixed
    */
    public function processResponse($options = array())
    {
        
        if ( empty($options) ) {
            return false;
        }

        // Create a response
        $response = new Phalcon\Http\Response();

        // set allow response field
        if ( !empty($options['allow']) && is_array($options['allow']) ) {
            $response->setRawHeader("Allow: " . implode(",", $options['allow']));
        }
        
        // Set the Content-Type header
        $response->setContentType('application/json');
        $response->setStatusCode($options['status']);
        
        // 204 response code should not contain http body
        if ( $options['status'] != 204 && !empty($options['payload']) ) {
            $response->setContent(json_encode($options['payload']));
        }

        return $response;
    }

    public function getSessionDocument( $csrfToken = null )
    {

        // get config
        $this->initialize();

        $session = null;
        
        // check to see if session document already exists
        if ( !empty($csrfToken) ) {
            $session = Sessions::findFirst(
                array(
                    "conditions" => array(
                        "csrfToken"        => $csrfToken,
                        'expireDate' => array(
                            '$gt' => new Mongodate(strtotime("now"))
                        )
                    )
                )
            );
        }

        if ( !empty($session) ) {
            
            return $session;

        }

        return false;

    }

    /**
    * Update session collection
    */
    public function updateSessionDocument( $csrfToken = null )
    {

        // get config
        $this->initialize();

        $session = null;

        // get session document
        if ( !empty($csrfToken) ) {
            $session = Sessions::findFirst(
                array(
                    "conditions" => array(
                        "csrfToken" => $csrfToken
                    )
                )
            );
        }

        if ( !empty($session) ) {
            
            // extend session
            $session->expireDate             = new MongoDate(strtotime("+6hour"));
            $session->modifiedDate           = new MongoDate();
            
            if ( $session->save() == false ) {
                foreach ($session->getMessages() as $message) {
                    $this->logger->log(
                        sprintf(
                            'NAMESPACE=ControllerBase MESSAGE=%s',
                            $message
                        ), 
                        \Phalcon\Logger::ERROR
                    );
                }
                return false;
            } else {
                // session record updated successfully
                return $session->csrfToken;
            }

        } else {
            
            // create session
            $session                         = new Sessions();
            $session->csrfToken              = $this->security->getTokenKey() . $this->security->getToken();
            $session->expireDate             = new MongoDate(strtotime("+6hour"));
            $session->createdDate            = new MongoDate();
            $session->modifiedDate           = new MongoDate();

            if ( $session->save() == false ) {
                foreach ($session->getMessages() as $message) {
                    $this->logger->log(
                        sprintf(
                            'NAMESPACE=ControllerBase MESSAGE=%s',
                            $message
                        ), 
                        \Phalcon\Logger::ERROR
                    );
                }
                return false;
            } else {
                return $session->csrfToken;
            }
        }

    }

}
