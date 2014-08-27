<?php

/**
 * Code contributed to the Learning Layers project
 * http://www.learning-layers.eu
 * Development is partly funded by the FP7 Programme of the European Commission under
 * Grant Agreement FP7-ICT-318209.
 * Copyright (c) 2014, Tallinn University - Institute of Informatics (Centre for Educational Technology).
 * For a list of contributors see the AUTHORS file at the top-level directory of this distribution.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Description of class-social-semantic-server-rest
 *
 * @author pjotr
 */
class Social_Semantic_Server_Rest {
    const SPACE_SHARED = 'sharedSpace';
    const SPACE_PRIVATE = 'privateSpace';
    
    const SC_KEY = 'key';
    const SC_OP = 'op';
    const SC_PASSWORD = 'password';
    const SC_USER = 'user';
    const SC_LABEL = 'label';
    const SC_COLL = 'coll';
    const SC_ADD_NEW_COLL = 'addNewColl';
    const SC_ENTRY = 'entry';
    const SC_ENTITY = 'entity';
    const SC_SPACE = 'space';
    const SC_GET_TAGS = 'getTags';
    const SC_GET_OVERALL_RATING = 'getOverallRating';
    const SC_GET_DISCS = 'getDiscs';
    const SC_VALUE = 'value';
    const SC_DESCRIPTION = 'description';
    const SC_COMMENTS = 'comments';
    const SC_FILE = 'file';
    
    private $uri;
    private $username;
    private $password;
    private $user;
    private $key;
    private $connection_established = false;
    
    /**
     * Creates an instance
     * @param string $uri       Service URI
     * @param string $username  Service username
     * @param string $password  Service password
     */
    public function __construct( $uri, $username, $password ) {
        $this->uri = $uri;
        $this->username = $username;
        $this->password = $password;
        
        // TODO It might be a good idea to raise an error if auth fails
        if ( $this->authCheckCred() ) {
            $this->connection_established = true;
        }
    }
    
    /**
     * Return the value to see if connection with the service could be
     * established
     * @return boolean
     */
    public function isConnectionEstablished() {
        return $this->connection_established;
    }
    
    /**
     * Used to log error request data to error_log
     * @param mixed $data 
     * @param array $body Request body (optional)
     */
    private function logError( $data, $body = null) {
        error_log( 'Service call ERROR' );
        error_log( 'START' );
        error_log( print_r( $data, true ) );
        if ( isset( $body ) && !empty( $body ) ) {
            error_log( print_r( $body, true ) );
        }
        error_log( 'END' );
    }
    
    /**
     * Checks body to see if service error occured. If an error flag is set,
     * returns WP_Error object. Logs the  response body to error_log.
     * @param string $body
     * @return mixed Either response object or WP_Error
     */
    private function checkRequestBodyForErrorsAndReturn( $body ) {
        $body = json_decode( $body );
        if ( ! $body.error ) {
            return $body;
        } else {
            $this->logError( $body );
            return WP_Error( 'error', 'Got method call error' );
        }
    }
    
    /**
     * A wrapper for making requests to the service. Handles error cases and
     * returns either response object or WP_Error.
     * @param string    $method A method to be called
     * @param array     $body   A body for the service call 
     * @return mixed
     */
    private function makeRequest( $method, $body ) {
        $request_url = "{$this->uri}rest/SSAdapterRest/{$method}/";
        
        $args['body'] = json_encode( $body );
        $args['headers']['content-type'] = 'application/json';
        
        $result = wp_remote_post( $request_url, $args );
        
        if ( is_wp_error( $result ) ) {
            $this->logError( $result, $body );
            return $result;
        } else {
            if ( 200 == $result['response']['code'] ) {
                if ($result['headers']['content-type'] === 'application/json') {
                    return $this->checkRequestBodyForErrorsAndReturn( $result['body'] );
                } else {
                    return $result['body'];
                }
            } else {
                $this->logError( $result, $body ); 
                return new WP_Error( $result['response']['code'], 'Got response code error' );
            }
        }
    }
    
    /**
     * Make a credentials check call. Will store the returned key and user
     * URI for further service calls.
     * @return boolean
     */
    public function authCheckCred() {
        $body = array(
            self::SC_KEY => 'someKey',
            self::SC_OP => 'authCheckCred',
            self::SC_PASSWORD => $this->password,
            self::SC_USER => 'mailto:dummyUser',
            self::SC_LABEL => $this->username,
        );
        $result = $this->makeRequest( 'authCheckCred', $body );
        
        if ( ! is_wp_error( $result ) ) {
            $this->key = $result->{$result->op}->key;
            $this->user = $result->{$result->op}->user;
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Add an entry to a collection.
     * @param string    $coll               Collection URI
     * @param string    $entry          Collection entry URI (will not be
     * sent to service if empty)
     * @param string    $label     New entry label
     * @param boolean   $addNewColl         Inficator if newly added entry is a
     * collection (entry URI is not needed in that case)
     * @return mixed
     */
    public function collEntryAdd( $coll, $entry, $label, $addNewColl ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_COLL => $coll,
            self::SC_LABEL => $label,
            self::SC_OP => 'collEntryAdd',
        );
        
        if ( $entry ) {
            $body[self::SC_ENTRY] = $entry;
        }
        if ( $addNewColl ) {
            $body[self::SC_ADD_NEW_COLL] = $addNewColl;
        }

        return $this->makeRequest( 'collEntryAdd', $body );
    }
    
    /**
     * Update existing entity. Any optional parameter left empty will not be
     * sent to the service.
     * @param string $entity      Entity URI
     * @param string $label       New label (optional)
     * @param string $description New description (optional)
     * @param string $comments    New comments text (optional)
     * @return mixed
     */
    public function entityUpdate( $entity, $label = '', $description = '', $comments = '' ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'entityUpdate',
            self::SC_ENTITY => $entity,
        );
            
        if ( $label ) {
            $body[self::SC_LABEL] = $label; 
        }
        if ( $description ) {
            $body[self::SC_DESCRIPTION] = $description;
        }
        if ( $comments ) {
            $body[self::SC_COMMENTS] = $comments;
        }
        
        return $this->makeRequest( 'entityUpdate', $body );
    }
    
    /**
     * Add a tag to an entity.
     * @param string $entity  Entity URI
     * @param string $label Tag    
     * @param string $space     Either sharedSpace or privateSpace
     * @return mixed
     */
    public function tagAdd( $entity, $label, $space ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_ENTITY => $entity,
            self::SC_LABEL => $label,
            self::SC_OP => 'tagAdd',
            self::SC_SPACE => $space,
        );
        
        return $this->makeRequest( 'tagAdd', $body );
    }
    
    /**
     * Remove a tag.
     * @param string $entity    Entity URI
     * @param string $label     Tag
     * @param string $space     Either sharedSpace or privateSpace
     * @return mixed
     */
    public function tagsRemove( $entity, $label, $space ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'tagsRemove',
        );
        
        if ( $entity ) {
            $body[self::SC_ENTITY] = $entity;
        }     
        if ( $label ) {
            $body[self::SC_LABEL] = $label; 
        }
        if ( $space ) {
            $body[self::SC_SPACE] = $space;
        }
        
        return $this->makeRequest( 'tagsRemove', $body );
    }
    
    /**
     * Get user root collection.
     * @return mixed
     */
    public function collRootGet() {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'collRootGet',
        );
        
        $result = $this->makeRequest( 'collRootGet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return $result->{$result->op}->coll;
        }
        
        return $result;
    }
    
    /**
     * Get and additional information for an entity.
     * @param string    $entity           Entity URI
     * @param boolean   $getTags          Flag to include tags
     * @param boolean   $getOverallRating Flag to include raiting information
     * @param boolean   $getDiscs         Flag to include discussions
     * @param boolean   $getUEs           Flag to include user events
     * @param boolean   $getThumb         Flag to include thumbnail
     * @param boolean   $getFlags         Flag to include flags  
     * @return mixed
     */
    public function entityDescGet( $entity, $getTags = true, $getOverallRating = true, $getDiscs = true, $getUEs = false, $getThumb = false, $getFlags = false) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'entityDescGet',
            self::SC_ENTITY => $entity,
        );
        
        if ( $getTags ) {
            $body[self::SC_GET_TAGS] = $getTags;
        }
        if ( $getOverallRating ) {
            $body[self::SC_GET_OVERALL_RATING] = $getOverallRating;
        }
        if ( $getDiscs ) {
            $body[self::SC_GET_DISCS] = $getDiscs;
        }
        
        $result = $this->makeRequest( 'entityDescGet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return $result->{$result->op}->desc;
        }
        
        return $result;
    }
    
    /**
     * Set raiting for an entity.
     * @param string    $entity   Entity URI
     * @param int       $value      Value from 1 to 5
     * @return mixed
     */
    public function ratingSet( $entity, $value ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'ratingSet',
            self::SC_ENTITY => $entity,
            self::SC_VALUE => $value,
        );
        
        $result = $this->makeRequest( 'ratingSet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return true;
        }
        
        return $result;
    }
    
    /**
     * Get overall rating of an entity.
     * @param string $entity
     * @return boolean|object
     */
    public function ratingOverallGet( $entity ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'ratingOverallGet',
            self::SC_ENTITY => $entity,
        );
        
        $result = $this->makeRequest( 'ratingOverallGet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return $result->{$result->op}->ratingOverall;
        }
        
        return false;
    }
    
    /**
     * Make entity public (mostly applicable to a collection).
     * @param string $entity Entity URI
     * @return mixed
     */
    public function entityPublicSet( $entity ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'entityPublicSet',
            self::SC_ENTITY => $entity,
        );
        
        return $this->makeRequest( 'entityPublicSet', $body );
    }
    
    public function fileDownload( $file ) {
        $body = array(
            self::SC_KEY => $this->key,
            self::SC_USER => $this->user,
            self::SC_OP => 'fileDownload',
            self::SC_FILE => $file,
        );
        
        return $this->makeRequest( 'fileDownload', $body );
    }
}
