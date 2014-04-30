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
        $this->authCheckCred();
        $this->userLogin();
    }
    
    private function makeRequest( $method, $body ) {
        $request_url = "{$this->uri}rest/SSAdapterRest/{$method}/";
        
        $args['body'] = json_encode( $body );
        $args['headers']['content-type'] = 'application/json';
        
        $result = wp_remote_post( $request_url, $args );
        
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            if ( 200 == $result['response']['code'] ) {
                // TODO Need to check the response if it has errors or not
                return json_decode( $result['body'] );
            } else {
                return WP_Error( $result['response']['code'], 'GOT ERROR' );
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
            'key' => 'kala',
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
}
