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

if ( !Attacher_Plugin::isMultisite() ) {
    wp_die( __( 'Multisite support is not enabled!', Attacher_Plugin::getTextDomain() ) );
}

if ( !current_user_can( 'manage_network_options' ) ) {
    wp_die( __( 'Insufficient privileges.', Attacher_Plugin::getTextDomain() ) );
}

// The logic is taken from wp-admin/network/settings.php
// As there does not seem to be any possibility of automation like it is
// possible with general options pages.
// Please notice the noheader=true in form action, this is there to prevent
// header output and thus make it impossible to redirect after actions completes
if ( $_POST ) {
    check_admin_referer( 'siteoptions' );

    $options = array(
        'attacher_service_rest_url', 'attacher_service_url',
    );

    foreach ( $options as $option_name ) {
        if ( ! isset($_POST[$option_name]) )
            continue;
        $value = wp_unslash( $_POST[$option_name] );
        update_site_option( $option_name, $value );
    }

    wp_redirect( add_query_arg( 'updated', 'true', network_admin_url( 'settings.php?page=attacher' ) ) );
    exit();
}
?>

<?php if ( isset( $_GET['updated'] ) ):?>
<div id="message" class="updated"><p><?php _e( 'Options saved.', Attacher_Plugin::getTextDomain() ); ?></p></div>
<?php endif; ?>

<div class="wrap">
    <h2><?php _e( 'Attacher settings', Attacher_Plugin::getTextDomain() ); ?></h2> 
    <form method="post" action="settings.php?page=attacher&noheader=true">
        <?php wp_nonce_field( 'siteoptions' ); ?>
        
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e( 'SocialSemanticServerREST URL', Attacher_Plugin::getTextDomain() ); ?></th>
                    <td>
                        <input type="text" id="attacher_service_rest_url" name="attacher_service_rest_url" value="<?php echo get_site_option( 'attacher_service_rest_url', '' ); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e( 'Add REST service URL followed by a slash. Example: http://example.com/ss-adapter-rest/', Attacher_Plugin::getTextDomain() ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e( 'SocialSemanticServerClientSide URL', Attacher_Plugin::getTextDomain() ); ?></th>
                    <td>
                        <input type="text" id="attacher_service_url" name="attacher_service_url" value="<?php echo get_site_option( 'attacher_service_url', '' ); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e( 'Add ClientSide API URL followed by a slash. Example: http://example.com/SocialSemanticServerClientSide/', Attacher_Plugin::getTextDomain() ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>