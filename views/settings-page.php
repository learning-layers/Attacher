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

if ( !current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Insufficient privileges.', Attacher_Plugin::getTextDomain() ) );
}
?>
<div class="wrap">
    <h2><?php _e( 'Attacher settings', Attacher_Plugin::getTextDomain() ); ?></h2> 
    <form method="post" action="options.php">
        <?php settings_fields( 'attacher-settings-group' ); ?>
        <?php do_settings_sections ( 'attacher-settings-group' ); ?>
        
        <table class="form-table">
            <tbody>
                <?php if ( !Attacher_Plugin::isMultisite() ): ?>
                <tr valign="top">
                    <th scope="row"><?php _e( 'SocialSemanticServerREST URL', Attacher_Plugin::getTextDomain() ); ?></th>
                    <td>
                        <input type="text" id="attacher_service_rest_url" name="attacher_service_rest_url" value="<?php echo get_option( 'attacher_service_rest_url', '' ); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e( 'Add REST service URL followed by a slash. Example: http://example.com/ss-adapter-rest/', Attacher_Plugin::getTextDomain() ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e( 'SocialSemanticServerClientSide URL', Attacher_Plugin::getTextDomain() ); ?></th>
                    <td>
                        <input type="text" id="attacher_service_url" name="attacher_service_url" value="<?php echo get_option( 'attacher_service_url', '' ); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e( 'Add ClientSide API URL followed by a slash. Example: http://example.com/SocialSemanticServerClientSide/', Attacher_Plugin::getTextDomain() ); ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr valign="top">
                    <th scope="row"><?php _e( 'SocialSemanticServer Username', Attacher_Plugin::getTextDomain() ); ?></th>
                    <td>
                        <input type="text" id="attacher_service_username" name="attacher_service_username" value="<?php echo get_option( 'attacher_service_username', '' ); ?>" class="regular-text" />
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e( 'SocialSemanticServer Password', Attacher_Plugin::getTextDomain() ); ?></th>
                    <td>
                        <input type="text" id="attacher_service_password" name="attacher_service_password" value="<?php echo get_option( 'attacher_service_password', '' ); ?>" class="regular-text" />
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
