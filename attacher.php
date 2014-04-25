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

/*
Plugin Name: Attacher
Plugin URI: http://github.com/learning-layers/Attacher
Description: Attacher WordPress Plugin
Version: 0.0.1
Author: Pjotr Savitski
Author URI: http://github.com/learning-layers
License: Apache License, Version 2.0
Text Domain: attacher
*/

define( 'ATTACHER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ATTACHER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ATTACHER_PLUGIN_DIR . 'classes/class-attacher-plugin.php' );

register_activation_hook( __FILE__ , array( 'Attacher_Plugin', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Attacher_Plugin', 'plugin_deactivation' ) );

add_action( 'init', array( 'Attacher_Plugin', 'init' ) );
