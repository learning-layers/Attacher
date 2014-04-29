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
        add_meta_box( 'attacher-resources',
                __( 'Attacher', self::getTextDomain() ),
                array( 'Attacher_Plugin', 'addResourcesMetaBox'),
                'post',
                'side',
                'high'
        );
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

        if ($post && 'post' == $post->post_type) {
            wp_register_script('attacher-service', ATTACHER_PLUGIN_URL . 'js/service.js');
            wp_localize_script('attacher-service', 'AttacherData', array(
                'service_username' => get_option('attacher_service_username', ''),
                'service_password' => get_option('attacher_service_password', ''),
            ));
            wp_enqueue_script( 'attacher-service' );
            self::enqueueSemanticServerClientSideScripts();
            wp_register_script( 'attacher-post-view', ATTACHER_PLUGIN_URL . 'js/post-view.js', array( 'jquery' ) );
            wp_enqueue_script( 'attacher-post-view' );
        }
    }

    /**
     * Enqueue admin scripts
     * @param string $hook
     */
    public static function adminEnqueueScripts( $hook ) {
        global $post;
        
        if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            if ( $post && 'post' == $post->post_type ) {
                wp_register_script( 'attacher-service', ATTACHER_PLUGIN_URL . 'js/service.js');
                wp_localize_script( 'attacher-service', 'AttacherData', array(
                    'service_username' => get_option( 'attacher_service_username', '' ),
                    'service_password' => get_option( 'attacher_service_password', '' ),
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
        $sss_client_side_url = get_option( 'attacher_service_url', '');
        
        $scripts = array(
            'jsglobals'                 => 'JSUtilities/JSGlobals.js',
            'ssglobals'                 => '/SSSClientInterfaceGlobals/globals/SSGlobals.js',
            'ssvaru'                    => 'SSSClientInterfaceGlobals/globals/SSVarU.js',
            'ssusereventconnwrapper'    => 'SSSClientInterfaceREST/connectors/wrapper/SSUserEventConnWrapper.js',
            'ssauthconns'               => 'SSSClientInterfaceREST/connectors/SSAuthConns.js',
            'sscollconns'               => 'SSSClientInterfaceREST/connectors/SSCollConns.js',
            'ssuserconns'               => 'SSSClientInterfaceREST/connectors/SSUserConns.js',
            'sstagconns'                => 'SSSClientInterfaceREST/connectors/SSTagConns.js',
            'ssfileconns'               => 'SSSClientInterfaceREST/connectors/SSFileConns.js',
            'ssfiledownload'            => 'SSSClientInterfaceREST/connectors/SSFileDownload.js',
        );
        
        foreach ( $scripts as $s_key => $s_val ) {
            wp_enqueue_script( "attacher-{$s_key}-js", $sss_client_side_url . $s_val );
        }
    }
    
    /**
     * Register settings
     */
    public static function registerSettings() {
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
     * Serves settings page
     */
    public static function loadSettingsPage() {
        include( ATTACHER_PLUGIN_DIR . '/views/settings-page.php');
    }
}
