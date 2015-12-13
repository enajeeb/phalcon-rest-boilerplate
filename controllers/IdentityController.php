<?php

class IdentityController extends ControllerBase
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
                    'GET',
                    'OPTIONS'
                ),
                'payload' => null
            )
        );
    }

    /**
    * Get CSRF token for the session
    */
    public function getTokenAction()
    {
        
        // All the checks should fail in this action otherwise the REST does not work
        // check API authentication
        if ( !$this->authenticateRequest() ) {
            return $this->processResponse(array(
                    'status'  => 401,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => array(),
                        'error'    => array(
                            'code'    => 401,
                            'message' => 'API authentication failed'
                        )
                    )
                )
            );
        }

        $csrfToken = $this->updateSessionDocument();

        if ( !empty($csrfToken) ) {

            return $this->processResponse(array(
                    'status'  => 200,
                    'payload' => array(
                        'status'   => 'success',
                        'data'     => $csrfToken,
                        'error'    => null
                    )
                )
            );
        } else {
            return $this->processResponse(array(
                    'status'  => 404,
                    'payload' => array(
                        'status'   => 'failure',
                        'data'     => null,
                        'error'    => array(
                            'code'    => 404,
                            'message' => 'Failed to create session token'
                        )
                    )
                )
            );
        }

    }

}
