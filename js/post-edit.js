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
     * Initialize draggable
     * @param {object} holder jQuery selector Object
     */
    function attacher_initialize_draggable(holder) {
        holder.find('li').draggable({
            appendTo: 'body',
            helper: 'clone',
            scope: 'resources',
            iframeFix: true
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
                var tmp_a = ui.draggable.find('a');
                var tmp_content = '<a href="' + tmp_a.attr('href') + '" target="_blank" data-label="' + tmp_a.data('label') + '" class="' + tmp_a.attr('class') + '">' + tmp_a.data('label') + '</a>';
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
     * Initialize downloadable files
     * @param {object} holder jQuery selector
     */
    function attacher_initialize_downloadable_files(holder) {
        holder.find('a.attacher-downloadable-file').on('click', function(e) {
            e.preventDefault();
            attacher_service_download_file(attacher_service_error, $(this).attr('href'), $(this).data('label'));
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
            var collections_select = $('#attacher-resources').find('select[name="attacher-collection"]');
            var collection_tagcloud = $('#attacher-resources').find('.attacher-collection-tagcloud');
            var collection_resources = $('#attacher-resources').find('.attacher-collection-resources');


            if (result.tagFrequs) {
                var fontMin = 10;
                var fontMax = 20;
                var frequMin = 1;
                var frequMax = 1;
                $.each(result.tagFrequs, function(key, tag) {
                    if (0 === key) {
                        frequMin = tag.frequ;
                        frequMax = tag.frequ;
                    }
                    if (tag.frequ > frequMax) {
                        frequMax = tag.frequ;
                    } else if (tag.frequ < frequMin) {
                        frequMin = tag.frequ;
                    }
                });
                $.each(result.tagFrequs, function(key, tag) {
                    var fontSize = (tag.frequ == frequMin) ? fontMin : (tag.frequ / frequMax) * (fontMax - fontMin) + fontMin;
                    collection_tagcloud.append(' <a href="#" data-tag="' + tag.label + '" data-frequ="' + tag.frequ + '" style="font-size:' + fontSize + 'pt;">' + tag.label + ' (' + tag.frequ + ')</a>');
                });

                collection_tagcloud.find('a').on('click', function(e) {
                    e.preventDefault();
                    collection_resources.empty();
                    collection_tagcloud.find('a').removeClass('selected');
                    $(e.target).addClass('selected');

                    attacher_service_search_tags_within_entity(deal_with_resources, attacher_service_error, collections_select.val(), [$(this).data('tag')]);
                });
            }
        }

        /**
         * Callback that populates resources
         * @param {object} result   Service call result object
         */
        function deal_with_resources(resources) {
            var collection_resources = $('#attacher-resources').find('.attacher-collection-resources');

            if (resources) {
                $.each(resources, function(key, entry) {
                    // This habdles results from two different methods that have
                    // different structural elements, compensating for that.
                    var type, space;
                    
                    if (entry.entityType) {
                        type = entry.entityType;
                    } else {
                        type = entry.type;
                    }
                    
                    if (entry.circleTypes) {
                        if ($.inArray('pub', entry.circleTypes) != -1) {
                            space = sSGlobals.spaceShared;
                        } else if ($.inArray('priv', entry.circleTypes) != -1) {
                            space = sSGlobals.spacePrivate;
                        }
                    } else {
                        space = entry.space;
                    }
                    
                    if ('coll' !== type) {
                        var resource_class = 'attacher-resource';
                        if ('file' == type) {
                            resource_class += ' attacher-downloadable-file';
                        }
                        if (sSGlobals.spacePrivate === space) {
                            resource_class += ' attacher-resource-private';
                        } else if (sSGlobals.spaceShared === space) {
                            resource_class += ' attacher-resource-shared';
                        }
                        collection_resources.append('<li><a href="' + entry.id + '" target="_blank" class="' + resource_class + '" data-label="' + entry.label + '">' + entry.label + '</a></li>');
                    }
                });
                attacher_initialize_draggable(collection_resources);
                attacher_initialize_downloadable_files(collection_resources);
            }
        }

        var collections_select = $('#attacher-resources').find('select[name="attacher-collection"]');
        var collection_tagcloud = $('#attacher-resources').find('.attacher-collection-tagcloud');
        var collection_resources = $('#attacher-resources').find('.attacher-collection-resources');

        if (result.colls) {
            $.each(result.colls, function(key, coll) {
                collections_select.append('<option value="' + coll.id + '" data-author="' + coll.author + '">' + coll.label + '</option>');
            });
        }
        
        // Load shared collections available to the user
        attacher_service_get_user_could_subscribe_collections(function(result) {
            if (result.colls) {
                $.each(result.colls, function(key, coll) {
                    collections_select.append('<option value="' + coll.id + '" data-author="' + coll.author + '">' + coll.label + '</option>');
                });
            }
        }, attacher_service_error);

        collections_select.on('change', function() {
            var collection_uri = $(this).val();
            var collection_autor = $(this).find(':selected').data('author');

            collection_tagcloud.empty();
            collection_resources.empty();

            if (collection_uri) {
                attacher_service_get_collection_tags(deal_with_tags, attacher_service_error, collection_uri, collection_autor);
                attacher_service_get_collection_with_entries(deal_with_resources, attacher_service_error, collection_uri);
            }
        });

        collections_select.trigger('change');
    }

    /**
     * Gets collecions list and initializes as needed.
     */
    function attacher_initialize_resources() {
        attacher_service_get_user_collections(attacher_populate_collections, attacher_service_error);
    }

    $(document).ready(function() {
        attacher_service_authenticate(attacher_initialize_resources, attacher_service_error);
        attacher_initialize_droppable($('#wp-content-editor-container'));
    });
})(jQuery);
