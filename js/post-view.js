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
 * 
 * @param {object} $ jQuery object
 */
(function($) {
    /**
     * Initialize downloadable files found within the body
     * @param {object} holder jQuery selector
     */
    function attacher_initialize_downloadable_files(holder) {
        holder.find('a.attacher-downloadable-file').on('click', function(e) {
            e.preventDefault();
            attacher_service_download_file(attacher_service_error, $(this).attr('href'), $(this).data('label'));
        });
    }
    
    /**
     * Initializes or updates already present raiting for a single post
     * @param {object} holder jQuery selector for the holder of raiting
     * @param {string} post_uri Post shortlink
     */
    function attacher_add_update_raiting(holder, post_uri) {
        attacher_service_raiting_overall_get(function(result) {
            holder.find('.attacher-raiting').remove();
            var raiting_html = '<div class="attacher-raiting">';
            for (var i = 1; i <= 5; i++) {
                if (result.ratingOverall.score >= i) {
                    raiting_html += '<a href="#" data-score="' + i + '"><div class="dashicons dashicons-star-filled"></div></a>';
                } else {
                    raiting_html += '<a href="#" data-score="' + i + '"><div class="dashicons dashicons-star-empty"></div></a>';
                }
            }
            raiting_html += '<div class="attacher-raiting-frequency">(' + result.ratingOverall.frequency + ')</div>';
            raiting_html += '</div>';
            holder.append(raiting_html);

            holder.find('.attacher-raiting a').on('click', function(e) {
                e.preventDefault();
                var score = $(this).data('score');
                var user_uri = AttacherData.user;
                // In case of anonymous user add IP to URI
                if ('1' !== AttacherData.is_user_logged_in) {
                    user_uri += '_' + AttacherData.user_ip;
                }
                new SSRatingSet(
                        function(result) {
                            attacher_add_update_raiting(holder, post_uri);
                        },
                        function(result) {
                            attacher_service_error();
                        },
                        user_uri,
                        AttacherData.key,
                        post_uri,
                        score
                        );
            });
        }, attacher_service_error, post_uri);
    }
    
    /**
     * Constructs post shortlink using id attribute
     * @param {String} id_attr Post article element id attribute
     * @returns {String} Post shortlink
     */
    function attacher_post_shortlink_from_id_attr(id_attr) {
        id_attr = id_attr.split('-');
        return AttacherData.home_url + '/?p=' + id_attr[1];
    }

    /**
     * Initialize post view additional logic
     * @returns {undefined}
     */
    function attacher_initialize_post_view() {
        attacher_initialize_downloadable_files($('article.type-post .entry-content'));
        $('article.type-post').each(function() {
            var post_shortlink = attacher_post_shortlink_from_id_attr($(this).attr('id'));
            attacher_add_update_raiting($(this).find('header.entry-header'), post_shortlink);
        });
    }

    $(document).ready(function() {
        attacher_service_authenticate(attacher_initialize_post_view, attacher_service_error);
    });
})(jQuery);
