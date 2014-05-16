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
        
        // TODO It might be a good idea to raise an error if either auth or
        // login fails
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
                return $this->checkRequestBodyForErrorsAndReturn( $result['body'] );
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
            'key' => 'someKey',
            'op' => 'authCheckCred',
            'pass' => $this->password,
            'user' => 'mailto:dummyUser',
            'userLabel' => $this->username,
        );
        $result = $this->makeRequest( 'authCheckCred', $body );
        
        if ( ! is_wp_error( $result ) ) {
            $this->key = $result->{$result->op}->key;
            $this->user = $result->{$result->op}->uri;
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Add an entry to a collection.
     * @param string    $coll               Collection URI
     * @param string    $collEntry          Collection entry URI (will not be
     * sent to service if empty)
     * @param string    $collEntryLabel     New entry label
     * @param boolean   $addNewColl         Inficator if newly added entry is a
     * collection (entry URI is not needed in that case)
     * @return mixed
     */
    public function collEntryAdd( $coll, $collEntry, $collEntryLabel, $addNewColl ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'coll' => $coll,
            'collEntryLabel' => $collEntryLabel,
            'op' => 'collEntryAdd',
        );
        
        if ( $collEntry ) {
            $body['collEntry'] = $collEntry;
        }
        if ( $addNewColl ) {
            $body['addNewColl'] = $addNewColl;
        }

        return $this->makeRequest( 'collEntryAdd', $body );
    }
    
    /**
     * Update existing entity label.
     * @param string $entityUri Entity URI
     * @param string $label     New label
     * @return mixed
     */
    public function entityLabelSet( $entityUri, $label ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'entityLabelSet',
            'entityUri' => $entityUri,
            'label' => $label,
        );
        
        return $this->makeRequest( 'entityLabelSet', $body );
    }
    
    /**
     * Add a tag to an entity.
     * @param string $resource  Entity URI
     * @param string $tagString Tag    
     * @param string $space     Either sharedSpace or privateSpace
     * @return mixed
     */
    public function tagAdd( $resource, $tagString, $space ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'resource' => $resource,
            'tagString' => $tagString,
            'op' => 'tagAdd',
            'space' => $space,
        );
        
        return $this->makeRequest( 'tagAdd', $body );
    }
    
    /**
     * Remove a tag.
     * @param string $resource  Entity URI
     * @param string $tagString Tag
     * @param string $space     Either sharedSpace or privateSpace
     * @return mixed
     */
    public function tagsRemove( $resource, $tagString, $space ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'resource' => $resource,
            'tagString' => $tagString,
            'op' => 'tagsRemove',
            'space' => $space,
        );
        
        return $this->makeRequest( 'tagsRemove', $body );
    }
    
    /**
     * Get user root collection.
     * @return mixed
     */
    public function collRootGet() {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'collRootGet',
        );
        
        $result = $this->makeRequest( 'collRootGet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return $result->{$result->op}->coll;
        }
        
        return $result;
    }
    
    /**
     * Get and additional information for an entity.
     * @param string    $entityUri          Entity URI
     * @param boolean   $getDiscUris        Flag to include didcussions
     * @param boolean   $getOverallRating   Flag to include raiting information
     * @param boolean   $getTags            Flag to include tags
     * @return mixed
     */
    public function entityDescGet( $entityUri, $getDiscUris = true, $getOverallRating = true, $getTags = true) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'entityDescGet',
            'entityUri' => $entityUri,
            'getDiscUris' => $getDiscUris,
            'getOverallRating' => $getOverallRating,
            'getTags' => $getTags,
        );
        
        $result = $this->makeRequest( 'entityDescGet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return $result->{$result->op}->entityDesc;
        }
        
        return $result;
    }
    
    /**
     * Set raiting for an entity.
     * @param string    $resource   Entity URI
     * @param int       $value      Value from 1 to 5
     * @return mixed
     */
    public function ratingSet( $resource, $value ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'ratingSet',
            'resource' => $resource,
            'value' => $value,
        );
        
        $result = $this->makeRequest( 'ratingSet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return true;
        }
        
        return $result;
    }
    
    /**
     * Make entity public (mostly applicable to a collection).
     * @param string $entityUri Entity URI
     * @return mixed
     */
    public function entityPublicSet( $entityUri ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'entityPublicSet',
            'entityUri' => $entityUri,
        );
        
        return $this->makeRequest( 'entityPublicSet', $body );
    }    
}
