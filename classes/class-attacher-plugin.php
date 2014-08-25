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
 * Attacher plugin main class
 *
 * @author pjotr
 */
class Attacher_Plugin {
    const TEXT_DOMAIN = 'attacher';
    
    private static $initiated = FALSE;
    
    /**
     * Plugin init function used 
     */
    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }
    
    /**
     * Used by register_activation_hook()
     */
    public static function plugin_activation() {
        // do something
    }
    
    /**
     * Used by register_deactivation_hook()
     */
    public static function plugin_deactivation() {
        // do something
    }
    
    /**
     * Initializes WordPress hooks
     */
    public static function init_hooks() {
        self::$initiated = TRUE;
        
        add_action( 'wp_enqueue_scripts', array( 'Attacher_Plugin', 'enqueueScripts' ) );
        
        if ( is_admin() ) {
            add_action( 'add_meta_boxes', array( 'Attacher_Plugin', 'addMetaBoxes' ) );
            add_action( 'admin_enqueue_scripts', array( 'Attacher_Plugin', 'adminEnqueueScripts' ) );
            add_action( 'admin_menu', array( 'Attacher_Plugin', 'addMenuPages' ) );
            add_action( 'post_updated', array( 'Attacher_Plugin', 'savePost' ), 10, 2 );
            add_action( 'admin_notices', array( 'Attacher_Plugin', 'adminNotices' ) );
            add_action( 'network_admin_menu', array( 'Attacher_Plugin', 'addNetworkMenuPages' ) );
            add_action( 'network_admin_notices', array( 'Attacher_Plugin', 'networkAdminNotices' ) );
        }
    }
    
    /**
     * Return text domain
     * @return string
     */
    public static function getTextDomain() {
        return self::TEXT_DOMAIN;
    }
    
    /**
     * Hooked to add_meta_boxes action
     */
    public static function addMetaBoxes() {
        if ( self::getServiceUrl() && self::getServiceUsername() && self::getServicePassword() ) {
            add_meta_box( 'attacher-resources',
                    __( 'Attacher', self::getTextDomain() ),
                    array( 'Attacher_Plugin', 'addResourcesMetaBox'),
                    'post',
                    'side',
                    'high'
            );
        }
    }
    
    /**
     * Outputs resources metabox content
     * @param WP_Post $post Post object
     */
    public static function addResourcesMetaBox( $post ) {
        echo '<div class="attacher-container attacher-collection-container">';
            echo '<label>';
                echo '<span>';
                    echo __( 'Collections', self::getTextDomain() );
                echo '</span>';
            echo '</label>';
            echo '<select name="attacher-collection">';
            echo '</select>';
        echo '</div>';
        
        echo '<div class="attacher-container attacher-collection-tagcloud-container">';
            echo '<label>';
                echo '<span>';
                    echo __( 'Tags', self::getTextDomain() );
                echo '</span>';
            echo '</label>';
            echo '<div class="attacher-collection-tagcloud">';
            echo '</div>';
        echo '</div>';
        
        echo '<div class="attacher-container attacher-collection-resources-container">';
            echo '<label>';
                echo '<span>';
                    echo __( 'Resources', self::getTextDomain() );
                echo '</span>';
            echo '</label>';
            echo '<ul class="attacher-collection-resources">';
            echo '</div>';
        echo '</div>';
    }
    
    /**
     * Enqueue scripts
     * @global WP_Post $post Current post
     */
    public static function enqueueScripts() {
        global $post;

        if ( $post && ( self::getServiceUrl() && self::getServiceUsername() && self::getServicePassword() ) ) {
            $username = self::getServiceUsername();
            $password = self::getServicePassword();
            
            $credentials = self::getServiceCredentialsForCurrentUser();
            if ( $credentials && $credentials['username'] && $credentials['password'] ) {
                $username = $credentials['username'];
                $password = $credentials['password'];
            }
            wp_register_script('attacher-service', ATTACHER_PLUGIN_URL . 'js/service.js');
            wp_localize_script('attacher-service', 'AttacherData', array(
                'service_username' => $username,
                'service_password' => $password,
                'user_ip' => preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] ),
                'home_url' => home_url(),
                'is_user_logged_in' => is_user_logged_in() ? 1 : 0,
            ));
            wp_enqueue_script( 'attacher-service' );
            self::enqueueSemanticServerClientSideScripts();
            wp_register_script( 'attacher-post-view', ATTACHER_PLUGIN_URL . 'js/post-view.js', array( 'jquery' ) );
            wp_enqueue_script( 'attacher-post-view' );
            
            wp_register_style( 'attacher-post-view', ATTACHER_PLUGIN_URL . 'css/post-view.css', array( 'dashicons' ) );
            wp_enqueue_style( 'attacher-post-view' );
        }
    }

    /**
     * Enqueue admin scripts
     * @param string $hook
     */
    public static function adminEnqueueScripts( $hook ) {
        global $post;
        
        if ( $hook == 'post-new.php' || $hook == 'post.php' && ( self::getServiceUrl() && self::getServiceUsername() && self::getServicePassword() ) ) {
            if ( $post && 'post' == $post->post_type ) {
                wp_register_script( 'attacher-service', ATTACHER_PLUGIN_URL . 'js/service.js');
                wp_localize_script( 'attacher-service', 'AttacherData', array(
                    'service_username' => self::getServiceUsername(),
                    'service_password' => self::getServicePassword(),
                ));
                wp_enqueue_script( 'attacher-service' );
                self::enqueueSemanticServerClientSideScripts();
                
                wp_register_script( 'attacher-post-edit', ATTACHER_PLUGIN_URL . 'js/post-edit.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );
                wp_enqueue_script( 'attacher-post-edit' );
                
                wp_register_style( 'attacher-post-edit', ATTACHER_PLUGIN_URL . 'css/post-edit.css' );
                wp_enqueue_style( 'attacher-post-edit' );
                
                wp_enqueue_style( 'attacher-jquery-ui', 'http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css' );
            }
        }
    }
    
    /**
     * Enqueue SocialSemanticServerClientSide scripts
     */
    public static function enqueueSemanticServerClientSideScripts() {
        $sss_client_side_url = self::getServiceUrl();
        
        $scripts = array(
            'jsglobals'                 => 'JSUtilities/JSGlobals.js',
            'ssglobals'                 => 'SSSClientInterfaceGlobals/globals/SSGlobals.js',
            'ssvaru'                    => 'SSSClientInterfaceGlobals/globals/SSVarU.js',
            'ssconns'                   => 'SSSClientInterfaceREST/SSConns.js',
        );
        
        foreach ( $scripts as $s_key => $s_val ) {
            wp_enqueue_script( "attacher-{$s_key}-js", $sss_client_side_url . $s_val );
        }
    }
    
    /**
     * Register settings
     */
    public static function registerSettings() {
        register_setting( 'attacher-settings-group', 'attacher_service_rest_url' );
        register_setting( 'attacher-settings-group', 'attacher_service_url' );
        register_setting( 'attacher-settings-group', 'attacher_service_username' );
        register_setting( 'attacher-settings-group', 'attacher_service_password' );
    }
    
    /**
     * Add administration menu pages
     */
    public static function addMenuPages() {
        add_options_page( __( 'Attacher', self::getTextDomain() ), __( 'Attacher settings', self::getTextDomain() ), 'manage_options', 'attacher', array( 'Attacher_Plugin', 'loadSettingsPage' ) );
        
        if (current_user_can( 'manage_options' ) ) {
            add_action( 'admin_init', array( 'Attacher_Plugin', 'registerSettings' ) );
        }
    }
    
    /**
     * Add network administration menu pages
     */
    public static function addNetworkMenuPages() {
        add_submenu_page('settings.php',
                __( 'Attacher settings', self::getTextDomain() ),
                __( 'Attacher settings', self::getTextDomain() ),
                'manage_network_options',
                'attacher',
                array( 'Attacher_Plugin', 'loadNetworkSettingsPage' )
                );
    }

    /**
     * Serves settings page
     */
    public static function loadSettingsPage() {
        include( ATTACHER_PLUGIN_DIR . '/views/settings-page.php' );
    }
    
    /**
     * Serves network settings page
     */
    public static function  loadNetworkSettingsPage() {
        include( ATTACHER_PLUGIN_DIR . '/views/network-settings-page.php' );
    }

    /**
     * Used with post_update action to make necessary service calls
     * @param int       $post_id    Post identifier
     * @param WP_Post   $post       Post object
     * @return null
     */
    public static function savePost( $post_id, $post ) {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }
        if ( defined('DOING_AJAX') && DOING_AJAX ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( false !== wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        if ( 'post' == $post->post_type ) {
            if ( ! ( self::getServiceRestUrl() && self::getServiceUsername() && self::getServicePassword() ) ) {
                return;
            }
            $service = new Social_Semantic_Server_Rest(
                    self::getServiceRestUrl(),
                    self::getServiceUsername(),
                    self::getServicePassword()
                    );
            if ( !$service->isConnectionEstablished() ) {
                error_log( 'NO SERVICE CONNECTION' );
                return;
            }
            
            $entity_uri = wp_get_shortlink( $post->ID );
            
            $root_collection = $service->collRootGet();
            $attacher_shared_resources_uri = NULL;
            $attacher_shared_resources_title = self::getServiceUsername() . ' : Attacher Shared Resources';
            
            // Check if "Attacher Shared Resources" already exists within a root
            // collection.
            if ( $root_collection->entries && is_array( $root_collection->entries ) && sizeof( $root_collection->entries > 0 ) ) {
                foreach ( $root_collection->entries as $entry ) {
                    if ( 'coll' == $entry->type ) {
                        if ( $attacher_shared_resources_title == $entry->label ) {
                            $attacher_shared_resources_uri = $entry->id;
                        }
                    }
                }
            }
            
            // Create "Attacher Shard Resources" collections if not exists, also
            // set it to be shared (public).
            if ( ! $attacher_shared_resources_uri ) {
                $attacher_shared_resources = $service->collEntryAdd( $root_collection->id, null, $attacher_shared_resources_title, true );
                $attacher_shared_resources_uri = $attacher_shared_resources->collEntryAdd->entity;
                $service->entityPublicSet( $attacher_shared_resources_uri ); 
            }
            
            // TODO Need to get the contents of that shared collection and check
            // if the post is already there
            $entry_added = $service->collEntryAdd( $attacher_shared_resources_uri, $entity_uri, $post->post_title, false );
            
            // TODO it might be a good idea to check if a resource already exists
            $entity = $service->entityDescGet( $entity_uri, true, true, true );
            
            if ( $post->title !== $entity->label) {
                $service->entityUpdate( $entity_uri, $post->post_title );
            }
            
            $existing_tags = array();
            if ( $entity->tags && sizeof( $entity->tags ) > 0 ) {
                foreach( $entity->tags as $tag ) {
                    
                    $existing_tags[] = $tag;
                } 
            }
            
            $tags = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
            
            $combined_tags = array_unique( array_merge( $existing_tags, $tags ) );
            if ( $combined_tags && sizeof( $combined_tags ) > 0 ) {
                foreach ( $combined_tags as $tag ) {
                    if ( ! in_array( $tag, $existing_tags ) ) {
                        $service->tagAdd( $entity_uri, $tag, $service::SPACE_SHARED );
                    } else if ( ! in_array( $tag, $tags ) ) {
                        $service->tagsRemove( $entity_uri, $tag, $service::SPACE_SHARED );
                    }
                }
            }
        }
    }
    
    /**
     * Retruns service REST API URL.
     * @return string
     */
    public static function getServiceRestUrl() {
        return get_site_option( 'attacher_service_rest_url', '' );
    }
    
    /**
     * Retutns service ClientSide JS URL.
     * @return string
     */
    public static function getServiceUrl() {
        return get_site_option( 'attacher_service_url', '' );
    }
    
    /**
     * Returns service username
     * @return String
     */
    public static function getServiceUsername() {
        return get_option( 'attacher_service_username', '' );
    }
    
    /**
     * Returns service password
     * @return String
     */
    public static function getServicePassword() {
        return get_option( 'attacher_service_password', '' );
    }

    /**
     * Display admin notices
     */
    public static function adminNotices() {
       
        if (!( self::getServiceRestUrl() && self::getServiceUrl() )) {
            if (self::isMultisite()) {
                if (is_super_admin()) {
                    echo '<div class="error">';
                    echo '<p>' . sprintf(__('SocialSemanticServer location not set! Please visit the <a href="%s">network settings</a> page.', self::getTextDomain()), network_admin_url('settings.php?page=attacher')) . '</p>';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<p>' . __('SocialSemanticServer location not set! Please contact your network admin.', self::getTextDomain()) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<div class="error">';
                echo '<p>' . sprintf(__('SocialSemanticServer location not set! Please visit the <a href="%s">settings</a> page.', self::getTextDomain()), admin_url('options-general.php?page=attacher')) . '</p>';
                echo '</div>';
            }
        }

        if ( ! ( self::getServiceUsername() && self::getServicePassword() ) ) {
            echo '<div class="error">';
                echo '<p>' . sprintf( __( 'Service username or possword not set! Please visit the <a href="%s">settings</a> page.', self::getTextDomain() ), admin_url( 'options-general.php?page=attacher' ) ) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Display network admin notices
     */
    public static function networkAdminNotices() {
        if (!( self::getServiceRestUrl() && self::getServiceUrl() )) {
            if (is_super_admin()) {
                echo '<div class="error">';
                echo '<p>' . sprintf(__('SocialSemanticServer location not set! Please visit the <a href="%s">network settings</a> page.', self::getTextDomain()), network_admin_url('settings.php?page=attacher')) . '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * Checks if running multisite environent.
     * @return boolean
     */
    public static function isMultisite() {
        return function_exists( 'is_multisite' ) && is_multisite();
    }
    
    /**
     * Returns currently logged in user service credentials for multisite case.
     * If credentials could not be found, a false is returned. In case user has
     * multiple blogs, the first set of existing credentials is taken.
     * @return mixed
     */
    public static function getServiceCredentialsForCurrentUser() {
        if ( self::isMultisite() && is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $user_blogs = get_blogs_of_user($current_user->ID);
            
            if ( is_array( $user_blogs) && sizeof($user_blogs) > 0 ) {
                foreach ( $user_blogs as $single_blog ) {
                    $username = get_blog_option( $single_blog->userblog_id, 'attacher_service_username' );
                    $password = get_blog_option( $single_blog->userblog_id, 'attacher_service_password' );
                    if ( $username && $password ) {
                        return array(
                            'username' => $username,
                            'password' => $password,
                        );
                    }
                }
            }
            
            return false;
        }
    }
}
