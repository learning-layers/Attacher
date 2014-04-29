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
            // TODO Probably need to get entity info first, the download the file
            attacher_service_download_file(attacher_service_error, $(this).attr('href'), $(this).data('label'));
        });
    }
    
    /**
     * Initialize post view additional logic
     * @returns {undefined}
     */
    function attacher_initialize_post_view() {
        attacher_initialize_downloadable_files($('.entry-content'));
    }
    
    $(document).ready(function() {
        attacher_service_authenticate(attacher_initialize_post_view, attacher_service_error);
    });
})(jQuery);
