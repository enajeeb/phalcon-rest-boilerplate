<?php

class UserController extends ControllerBase
{

    /*
    * This method is executed first before any other methods
    */
    public function initialize()
    {
        parent::initialize();
    }

    public function optionsAction()
    {
        return $this->processResponse(array(
                'status'  => 200,
                'allow'    => array(
                    'DELETE',
                    'GET',
                    'OPTIONS',
                    'POST',
                    'PUT',
                ),
                'payload' => null
            )
        );
    }

    /**
    * Get user for the given id
    */
    public function getAction( $id = null )
    {

        // check API authentication
        if ( !$this->authenticateRequest() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'API authentication failed'
                        )
                    )
                )
            );
        }

        // verify token
        if ( !$this->verifyCsrfToken() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'Token validation failed'
                        )
                    )
                )
            );
        }

        if ( empty($id) ) {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'ID not provided'
                        )
                    )
                )
            );
        }

        // find user
        try {
            $user = Users::findById($id);
        } catch (MongoException $e) {
            $this->logger->log(
                sprintf(
                    'NAMESPACE=user MESSAGE=%s',
                    $e->getMessage()
                ), 
                \Phalcon\Logger::ERROR
            );
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => $e->getMessage()
                        )
                    )
                )
            );
        }

        if ( !empty($user) ) {
            return $this->processResponse(array(
                    'status'  => 200,
                    'payload' => array(
                        'status'   => 'success',
                        'data'     => json_encode($user),
                        'error'    => null
                    )
                )
            );
        } else {
            return $this->processResponse(array(
                    'status'  => 204,
                    'payload' => array(
                        'status'   => 'success',
                        'data'     => null,
                        'error'    => null
                    )
                )
            );
        }

    }

    /**
    * Create new user
    */
    public function addAction()
    {
        
        // check API authentication
        if ( !$this->authenticateRequest() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'API authentication failed'
                        )
                    )
                )
            );
        }

        // verify token
        if ( !$this->verifyCsrfToken() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'Token validation failed'
                        )
                    )
                )
            );
        }

        // get HTTP entity body
        $httpContent = file_get_contents('php://input');

        if ( empty($httpContent) ) {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User information not provided'
                        )
                    )
                )
            );
        }

        $data = json_decode($httpContent);

        // create user
        $user = new Users();
        $user->name         = htmlentities($data->name);
        $user->createdDate  = new MongoDate();
        $user->modifiedDate = new MongoDate();

        if ( $user->save() == false ) {
            foreach ($user->getMessages() as $message) {
                $this->logger->log(
                    sprintf(
                        'NAMESPACE=user MESSAGE=%s',
                        $message
                    ), 
                    \Phalcon\Logger::ERROR
                );
            }
            
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'Failed to create user'
                        )
                    )
                )
            );

        } else {
            return $this->processResponse(array(
                    'status'  => 201,
                    'payload' => array(
                        'status'   => 'success',
                        'data'     => json_encode($user),
                        'error'    => null
                    )
                )
            );
        }

    }

    /**
    * Update user
    */
    public function updateAction( $id = null )
    {
        // check API authentication
        if ( !$this->authenticateRequest() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'API authentication failed'
                        )
                    )
                )
            );
        }

        // verify token
        if ( !$this->verifyCsrfToken() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'Token validation failed'
                        )
                    )
                )
            );
        }

        if ( empty($id) ) {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User id not provided'
                        )
                    )
                )
            );
        }

        // get HTTP entity body
        $httpContent = file_get_contents('php://input');

        if ( empty($httpContent) ) {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User information not provided'
                        )
                    )
                )
            );
        }
        $data = json_decode($httpContent);

        // check if user exists
        try {
            $user = Users::findById($id);
        } catch (MongoException $e) {
            $this->logger->log(
                sprintf(
                    'NAMESPACE=user MESSAGE=%s',
                    $e->getMessage()
                ), 
                \Phalcon\Logger::ERROR
            );
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User information not found'
                        )
                    )
                )
            );
        }

        if ( empty($user) ) {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User does not exists'
                        )
                    )
                )
            );
        }

        // update user
        $user->name         = htmlentities($data->name);
        $user->modifiedDate = new MongoDate();

        if ( $user->save() == false ) {
            foreach ($user->getMessages() as $message) {
                $this->logger->log(
                    sprintf(
                        'NAMESPACE=user MESSAGE=%s',
                        $message
                    ), 
                    \Phalcon\Logger::ERROR
                );
            }
            
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'Failed to update user'
                        )
                    )
                )
            );

        } else {
            return $this->processResponse(array(
                    'status'  => 200,
                    'payload' => array(
                        'status'   => 'success',
                        'data'     => json_encode($user),
                        'error'    => null
                    )
                )
            );
        }

    }

    /**
    * Delete User
    */
    public function deleteAction( $id = null )
    {
        // check API authentication
        if ( !$this->authenticateRequest() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'API authentication failed'
                        )
                    )
                )
            );
        }

        // verify token
        if ( !$this->verifyCsrfToken() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'Token validation failed'
                        )
                    )
                )
            );
        }

        if ( empty($id) ) {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User id not provided'
                        )
                    )
                )
            );
        }

        // check if user exists
        try {
            $user = Users::findById($id);
        } catch (MongoException $e) {
            $this->logger->log(
                sprintf(
                    'NAMESPACE=user MESSAGE=%s',
                    $e->getMessage()
                ), 
                \Phalcon\Logger::ERROR
            );
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User information not found'
                        )
                    )
                )
            );
        }

        if ($user != false) {
            if ($user->delete() == false) {
                foreach ($user->getMessages() as $message) {
                    $this->logger->log(
                        sprintf(
                            'NAMESPACE=user MESSAGE=%s',
                            $message
                        ), 
                        \Phalcon\Logger::ERROR
                    );
                }

                return $this->processResponse(array(
                        'status'  => 404,
                        'payload' => array(
                            'status'   => 'failure',
                            'data'     => null,
                            'error'    => array(
                                'code'    => 404,
                                'message' => 'Failed to delete user'
                            )
                        )
                    )
                );
            } else {
                return $this->processResponse(array(
                        'status'  => 200,
                        'payload' => array(
                            'status'   => 'success',
                            'data'     => null,
                            'error'    => null
                        )
                    )
                );
            }
        } else {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'User Id not found'
                        )
                    )
                )
            );
        }

    }

}
