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
    
    private $uri;
    private $username;
    private $password;
    private $user;
    private $key;
    private $connection_established = false;
    
    /**
     * 
     * @param string $uri
     * @param string $username
     * @param string $password
     */
    public function __construct( $uri, $username, $password ) {
        $this->uri = $uri;
        $this->username = $username;
        $this->password = $password;
        
        // TODO It might be a good idea to raise an error if either auth or
        // login fails
        if ( $this->authCheckCred() ) {
            if ( $this->userLogin() ) {
                $this->connection_established = true;
            }
        }
    }
    
    public function isConnectionEstablished() {
        return $this->connection_established;
    }
    
    private function logError( $data ) {
        error_log( print_r( $data, true ) );
    }
    
    private function checkRequestBodyForErrorsAndReturn( $body ) {
        $body = json_decode( $body );
        if ( ! $body.error ) {
            return $body;
        } else {
            $this->logError( $body );
            return WP_Error( 'error', 'Got method call error' );
        }
    }
    
    private function makeRequest( $method, $body ) {
        $request_url = "{$this->uri}rest/SSAdapterRest/{$method}/";
        
        $args['body'] = json_encode( $body );
        $args['headers']['content-type'] = 'application/json';
        
        $result = wp_remote_post( $request_url, $args );
        
        if ( is_wp_error( $result ) ) {
            $this->logError( $result );
            return $result;
        } else {
            if ( 200 == $result['response']['code'] ) {
                return $this->checkRequestBodyForErrorsAndReturn( $result['body'] );
            } else {
                $this->logError( $result ); 
                return WP_Error( $result['response']['code'], 'Got response code error' );
            }
        }
    }
    
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
            return TRUE;
        }
        return FALSE;
    }
    
    public function userLogin() {
        $body = array(
            'key' => $this->key,
            'op' => 'userLogin',
            'user' => 'mailto:dummyUser',
            'userLabel' => $this->username,
        );
        $result = $this->makeRequest( 'userLogin', $body );
        
        if ( ! is_wp_error( $result ) ) {
            $this->user = $result->{$result->op}->uri;
            return TRUE;
        }
        return FALSE;
    }
    
    public function collUserEntryAdd( $coll, $collEntry, $collEntryLabel, $addNewColl ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'coll' => $coll,
            'collEntryLabel' => $collEntryLabel,
            'op' => 'collUserEntryAdd',
        );
        
        if ( $collEntry ) {
            $body['collEntry'] = $collEntry;
        }
        if ( $addNewColl ) {
            $body['addNewColl'] = $addNewColl;
        }

        return $this->makeRequest( 'collUserEntryAdd', $body );
    }
    
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
    
    public function tagsUserRemove( $resource, $tagString, $space ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'resource' => $resource,
            'tagString' => $tagString,
            'op' => 'tagsUserRemove',
            'space' => $space,
        );
        
        return $this->makeRequest( 'tagsUserRemove', $body );
    }
    
    public function collUserRootGet() {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'collUserRootGet',
        );
        
        $result = $this->makeRequest( 'collUserRootGet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return $result->{$result->op}->coll;
        }
        
        return $result;
    }
    
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
    
    public function ratingUserSet( $resource, $value ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'ratingUserSet',
            'resource' => $resource,
            'value' => $value,
        );
        
        $result = $this->makeRequest( 'ratingUserSet', $body );
        
        if ( ! is_wp_error( $result ) ) {
            return true;
        }
        
        return $result;
    }
    
    public function entityUserPublicSet( $entityUri ) {
        $body = array(
            'key' => $this->key,
            'user' => $this->user,
            'op' => 'entityUserPublicSet',
            'entityUri' => $entityUri,
        );
        
        return $this->makeRequest( 'entityUserPublicSet', $body );
    }    
}
