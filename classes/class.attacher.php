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
class AttacherPlugin {
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
        
        if ( is_admin() ) {
            add_action( 'add_meta_boxes', array( 'AttacherPlugin', 'addMetaBoxes' ) );
            add_action( 'admin_enqueue_scripts', array( 'AttacherPlugin', 'adminEnqueueScripts' ) );
        }
    }
    
    public static function getTextDomain() {
        return self::TEXT_DOMAIN;
    }
    
    /**
     * Hooked to add_meta_boxes action
     */
    public static function addMetaBoxes() {
        add_meta_box( 'attacher-resources',
                __( 'Resources', self::getTextDomain() ),
                array( 'AttacherPlugin', 'addResourcesMetaBox'),
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
        echo '<select name="attacher-collection">';
        echo '</select>';
        
        echo '<div>';
        echo '<input type="checkbox" name="attacher-collection-show-untagged" value="1" />';
        echo __( 'Show untagged', self::getTextDomain() );
        echo '</div>';
        
        echo '<div class="attacher-collection-tagcloud">';
        echo '</div>';
        
        echo '<ul class="attacher-collection-resources">';
        echo '</div>';
    }
    
    /**
     * 
     * @param type $hook
     */
    public static function adminEnqueueScripts( $hook ) {
        global $post;
        
        if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            if ( $post && 'post' == $post->post_type ) {
                wp_register_script( 'attacher-post-edit', ATTACHER_PLUGIN_URL . 'js/post-edit.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );
                wp_enqueue_script( 'attacher-post-edit' );
                wp_register_style( 'attacher-post-edit', ATTACHER_PLUGIN_URL . 'css/post-edit.css' );
                wp_enqueue_style( 'attacher-post-edit' );
                wp_enqueue_style( 'attacher-jquery-ui', 'http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css' );
            }
        }
    }
}
