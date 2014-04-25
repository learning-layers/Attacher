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

var testData = {};
testData.collections = {
    1: {
        title: 'Collection 1',
        uri: 'http://collections.com/1/',
        tags: {
            1: {
                tag: 'first',
                weight: 2
            },
            2: {
                tag: 'second',
                weight: 5
            },
            3: {
                tag: 'third',
                weight: 1.5
            }
        },
        resources: {
            1: {
                title: 'Resource 1',
                uri: 'http://collections.com/1/resources/1/',
                tags: ['first', 'second', 'third']
            },
            2: {
                title: 'Resource 2',
                uri: 'http://collections.com/1/resources/2/',
                tags: ['second']
            },
            3: {
                title: 'Resource 3',
                uri: 'http://collections.com/1/resources/3/',
                tags: []
            }
        }
    },
    2: {
        title: 'Collection 2',
        uri: 'http://collections.com/2/',
        tags: {
            1: {
                tag: 'fourth',
                weight: 2
            }
        },
        resources: {}
    },
    3: {
        title: 'Collection 3',
        uri: 'http://collections.com/3/',
        tags: {},
        resources: {}
    }
};

(function($) {
    function attacher_initialize_draggable(holder) {
        holder.find('li').draggable({
            appendTo: 'body',
            helper: 'clone',
            scope: 'resources'
        });
    }

    function attacher_initialize_droppable(holder) {
        holder.droppable({
            activeClass: 'ui-state-default',
            hoverClass: 'ui-state-hover',
            scope: 'resources',
            drop: function(event, ui) {
                $(this).find('.placeholder').remove();
                var tmp_href = ui.draggable.find('a').attr('href');
                var tmp_content = '<a href="'+tmp_href+'" target="_blank">'+tmp_href+'</a>';
                // Handling both cases, tinymce active and inactive
                if (!tinymce.activeEditor.isHidden()) {
                    tinymce.activeEditor.execCommand('mceInsertContent', false, tmp_content);
                } else {
                    QTags.insertContent(tmp_content);
                }
            }
        });
    }

    $(document).ready(function() {
        // Authenticate and load user
        // Notify user and stop if it fails
        // Load collections and populate the select (it might be a good idea to create the whole thing)
        // Hook up the change event for collections
        
        var tmp_collections_select = $('#attacher-resources').find('select[name="attacher-collection"]');
        var tmp_collection_tagcloud = $('#attacher-resources').find('.attacher-collection-tagcloud');
        var tmp_collection_resources = $('#attacher-resources').find('.attacher-collection-resources');
        var tmp_collection_show_untagged = $('#attacher-resources').find('input[name="attacher-collection-show-untagged"]');
        
        $.each(testData.collections, function(key, value) {
            tmp_collections_select.append('<option value="'+key+'">'+value.title+'</option>');
        });
        
        tmp_collections_select.on('change', function() {
            var tmp_collection = testData.collections[$(this).val()];
            tmp_collection.ID = $(this).val();
            
            
            tmp_collection_tagcloud.empty();
            tmp_collection_resources.empty();
            tmp_collection_show_untagged.prop('checked', false);
            
            $.each(tmp_collection.tags, function(key, value) {
                tmp_collection_tagcloud.append('  <a href="#" data-tag="'+value.tag+'">'+value.tag+'</a>');
            });
            
            tmp_collection_tagcloud.find('a').on('click', function(e) {
                e.preventDefault();
                tmp_collection_resources.empty();
                tmp_collection_show_untagged.prop('checked', false);
                tmp_collection_tagcloud.find('a').removeClass('selected');
                $(e.target).addClass('selected');
                $.each(testData.collections[tmp_collection.ID].resources, function(key, value) {
                    if (value.tags.indexOf($(e.target).data('tag')) !== -1) {
                        tmp_collection_resources.append('<li><a href="'+value.uri+'" target="_blank">'+value.title+'</a></li>');
                    }
                });
                attacher_initialize_draggable( tmp_collection_resources );
            });
        });
        tmp_collections_select.trigger('change');
        
        tmp_collection_show_untagged.on('click', function() {
            var tmp_collection = testData.collections[tmp_collections_select.val()];
            tmp_collection_resources.empty();
            if ($(this).is(':checked')) {
                tmp_collection_tagcloud.find('a').removeClass('selected');
                $.each(testData.collections[tmp_collection.ID].resources, function(key, value) {
                    if (value.tags.length === 0) {
                        tmp_collection_resources.append('<li><a href="'+value.uri+'" target="_blank">'+value.title+'</a></li>');
                    }
                });
                attacher_initialize_draggable( tmp_collection_resources );
            }
        });
        
        attacher_initialize_droppable($('#wp-content-editor-container'));
        
        // TESTING STUFF
        // TODO
        // Need to go through authentication initially
        // If something fails, just notify user about the problem
        // All subsequent calls should only be done after authentication
        // was successful.
        new SSAuthCheckCred().handle(
            function(result) {
                AttacherData.key = result.key;
                //console.log(result);
                new SSUserLogin().handle(
                    function(result) {
                        AttacherData.user = result.uri;
                        //console.log(result);
                        new SSCollUserRootGet().handle(
                            function(result) {
                                console.log(result);
                            },
                            function(result) {
                                console.log(result);
                            },
                            AttacherData.user,
                            AttacherData.key
                        );
                    },
                    function(result) {
                        console.log(result);
                    },
                    AttacherData.service_username,
                    AttacherData.key
                );
            },
            function(result) {
                console.log(result);
            },
            AttacherData.service_username,
            AttacherData.service_password
        );
    });
})( jQuery );
