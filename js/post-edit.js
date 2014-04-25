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
     * A callback function to display error message to a user
     */
    function attacher_service_error() {
        alert('SocialSemanticService Communication Error');
    }

    /**
     * A service call that would authenticate a user
     * @param {function} callback         Success callback, is given result object
     * @param {function} error_callback   Error collback
     */
    function attacher_service_get_root_collection(callback, error_callback) {
        new SSCollUserRootGet().handle(
                function(result) {
                    callback(result);
                },
                function(result) {
                    error_callback();
                },
                AttacherData.user,
                AttacherData.key
                );
    }

    /**
     * A service call to get cumulated collection tags
     * @param {function} callback         Success callback, is given result object
     * @param {function} error_callback   Erro callback
     * @param {string} collection_uri     Collection URI
     */
    function attacher_service_get_collection_tags(callback, error_callback, collection_uri) {
        new SSCollUserCumulatedTagsGet().handle(
                function(result) {
                    callback(result);
                },
                function(result) {
                    error_callback();
                },
                AttacherData.user,
                AttacherData.key,
                collection_uri
                );
    }

    /**
     * A service call to get collection with entries
     * @param {function} callback         Success callback, is given result object
     * @param {function} error_callback   Error callback
     * @param {function} collection_uri   Collection URI
     */
    function attacher_service_get_collection_with_entries(callback, error_callback, collection_uri) {
        new SSCollUserWithEntries().handle(
                function(result) {
                    callback(result);
                },
                function(result) {
                    error_callback();
                },
                AttacherData.user,
                AttacherData.key,
                collection_uri
                );
    }

    /**
     * Obtains service token for an account. Makes a second call on success that
     * will bring the user URI. Both are added to AttacherData object to be used
     * later on.
     * @param {function} callback         Success callback, is given result object
     * @param {function} error_callback   Error callback
     */
    function attacher_service_authenticate(callback, error_callback) {
        new SSAuthCheckCred().handle(
                function(result) {
                    AttacherData.key = result.key;
                    new SSUserLogin().handle(
                            function(result) {
                                AttacherData.user = result.uri;
                                callback();
                            },
                            function(result) {
                                error_callback();
                            },
                            AttacherData.service_username,
                            AttacherData.key
                            );
                },
                function(result) {
                    error_callback();
                },
                AttacherData.service_username,
                AttacherData.service_password
                );
    }

    /**
     * Initialize draggable
     * @param {object} holder jQuery selector Object
     */
    function attacher_initialize_draggable(holder) {
        holder.find('li').draggable({
            appendTo: 'body',
            helper: 'clone',
            scope: 'resources'
        });
    }

    /**
     * Initialize droppable
     * @param {object} holder jQuery selector Object
     */
    function attacher_initialize_droppable(holder) {
        holder.droppable({
            activeClass: 'ui-state-default',
            hoverClass: 'ui-state-hover',
            scope: 'resources',
            drop: function(event, ui) {
                $(this).find('.placeholder').remove();
                var tmp_href = ui.draggable.find('a').attr('href');
                var tmp_content = '<a href="' + tmp_href + '" target="_blank">' + tmp_href + '</a>';
                // Handling both cases, tinymce active and inactive
                if (!tinymce.activeEditor.isHidden()) {
                    tinymce.activeEditor.execCommand('mceInsertContent', false, tmp_content);
                } else {
                    QTags.insertContent(tmp_content);
                }
            }
        });
    }

    /**
     * Callback that populates collections select. Used by service call.
     * @param {object} result Service call result object
     */
    function attacher_populate_collections(result) {
        /**
         * Callback that populates tagcloud
         * @param {object} result Service call result object
         */
        function deal_with_tags(result) {
            /**
             * Callback that populates resources
             * @param {object} result   Service call result object
             */
            function deal_with_resources(result) {
                var collection_resources = $('#attacher-resources').find('.attacher-collection-resources');

                if (result.coll.entries) {
                    $.each(result.coll.entries, function(key, entry) {
                        if ('coll' !== entry.entityType) {
                            collection_resources.append('<li><a href="' + entry.uri + '" target="_blank">' + entry.label + '</a></li>');
                        }
                    });
                    attacher_initialize_draggable(collection_resources);
                }
            }

            var collections_select = $('#attacher-resources').find('select[name="attacher-collection"]');
            var collection_tagcloud = $('#attacher-resources').find('.attacher-collection-tagcloud');
            var collection_resources = $('#attacher-resources').find('.attacher-collection-resources');


            if (result.tagFrequs) {
                $.each(result.tagFrequs, function(key, tag) {
                    collection_tagcloud.append(' <a href="#" data-tag="'+tag.label+'" data-frequ="'+tag.frequ+'">' + tag.label + ' (' + tag.frequ + ')</a>');
                });

                collection_tagcloud.find('a').on('click', function(e) {
                    e.preventDefault();
                    collection_resources.empty();
                    collection_tagcloud.find('a').removeClass('selected');
                    $(e.target).addClass('selected');

                    attacher_service_get_collection_with_entries(deal_with_resources, attacher_service_error, collections_select.val());
                });
            }
        }

        var collections_select = $('#attacher-resources').find('select[name="attacher-collection"]');
        var collection_tagcloud = $('#attacher-resources').find('.attacher-collection-tagcloud');
        var collection_resources = $('#attacher-resources').find('.attacher-collection-resources');

        collections_select.append('<option value="' + result.coll.uri + '">' + result.coll.label + '</option>');
        if (result.coll.entries) {
            $.each(result.coll.entries, function(key, coll) {
                if ('coll' === coll.entityType) {
                    collections_select.append('<option value="' + coll.uri + '">' + coll.label + '</option>');
                }
            });
        }

        collections_select.on('change', function() {
            var collection_uri = $(this).val();

            collection_tagcloud.empty();
            collection_resources.empty();

            attacher_service_get_collection_tags(deal_with_tags, attacher_service_error, collection_uri);
        });

        collections_select.trigger('change');
    }

    /**
     * Gets collecions list and initializes as needed.
     */
    function attacher_initialize_resources() {
        attacher_service_get_root_collection(attacher_populate_collections, attacher_service_error);
    }

    $(document).ready(function() {
        attacher_service_authenticate(attacher_initialize_resources, attacher_service_error);
        attacher_initialize_droppable($('#wp-content-editor-container'));
    });
})(jQuery);
