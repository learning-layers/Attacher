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
    
    function attacher_populate_tagcloud(tagcloud, tags) {
        
        /**
         * Callback that populates resources
         * @param {object} result   Service call result object
         */
        function deal_with_resources(resources) {
            var my_resources = $('#attacher-resources').find('.attacher-my-resources');
            var others_resources = $('#attacher-resources').find('.attacher-others-resources');

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
                        if (entry.author === AttacherData.user) {
                            my_resources.append('<li><a href="' + entry.id + '" target="_blank" class="' + resource_class + '" data-label="' + entry.label + '">' + entry.label + '</a></li>');
                        } else {
                            others_resources.append('<li><a href="' + entry.id + '" target="_blank" class="' + resource_class + '" data-label="' + entry.label + '">' + entry.label + '</a></li>');
                        }
                    }
                });
                attacher_initialize_draggable(my_resources);
                attacher_initialize_downloadable_files(my_resources);
                attacher_initialize_draggable(others_resources);
                attacher_initialize_downloadable_files(others_resources);
            }
        }
        
        var my_resources = $('#attacher-resources').find('.attacher-my-resources');
        var others_resources = $('#attacher-resources').find('.attacher-others-resources');
        var tagsWithFrequs = {};
        var fontMin = 10;
        var fontMax = 20;
        var frequMin = null;
        var frequMax = null;

        $.each(tags, function(key, element) {
            if (tagsWithFrequs.hasOwnProperty(element.label)) {
                tagsWithFrequs[element.label].frequ += 1;
            } else {
                tagsWithFrequs[element.label] = {
                    label: element.label,
                    frequ: 1
                };
            }
        });

        $.each(tagsWithFrequs, function(key, element) {
            if (element.frequ > frequMax) {
                frequMax = element.frequ;
            } else if (element.frequ < frequMin) {
                frequMin = element.frequ;
            }
        });

        $.each(tagsWithFrequs, function(key, tag) {
            var fontSize = (tag.frequ === frequMin) ? fontMin : (tag.frequ / frequMax) * (fontMax - fontMin) + fontMin;
            tagcloud.append(' <a href="#" data-tag="' + tag.label + '" data-frequ="' + tag.frequ + '" style="font-size:' + fontSize + 'pt;">' + tag.label + ' (' + tag.frequ + ')</a>');
        });

        tagcloud.find('a').on('click', function(e) {
            e.preventDefault();
            my_resources.empty();
            others_resources.empty();
            tagcloud.find('a').removeClass('selected');
            $(e.target).addClass('selected');

            attacher_service_search_tags(deal_with_resources, attacher_service_error, [$(this).data('tag')]);
        });
    }
    
    function attacher_populate_my_tagcloud(tags) {
        var tagcloud = $('#attacher-resources').find('.attacher-my-tagcloud');
        attacher_populate_tagcloud(tagcloud, tags);
    }
    
    function attacher_populate_all_tagcloud(tags) {
        var tagcloud = $('#attacher-resources').find('.attacher-all-tagcloud');
        attacher_populate_tagcloud(tagcloud, tags);
    }

    /**
     * Gets collecions list and initializes as needed.
     */
    function attacher_initialize_tagclouds() {
        $('#attacher-resources').find('input[name="tagcloud-select"]').on('change', function() {
            $('#attacher-resources').find('.attacher-my-tagcloud').slideToggle();
            $('#attacher-resources').find('.attacher-all-tagcloud').slideToggle();
        });

        attacher_service_user_tags_get(attacher_populate_my_tagcloud, attacher_service_error, AttacherData.user);
        attacher_service_all_tags_get(attacher_populate_all_tagcloud, attacher_service_error);
    }

    $(document).ready(function() {
        attacher_service_authenticate(attacher_initialize_tagclouds, attacher_service_error);
        attacher_initialize_droppable($('#wp-content-editor-container'));
    });
})(jQuery);
