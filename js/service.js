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
 * A callback function to display error message to a user
 */
function attacher_service_error() {
    alert('SocialSemanticService Communication Error');
}

/**
 * A service call that would get user root collection
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
 * A service call that would bring all user collections
 * @param {function} callback         Success callback, is given result object
 * @param {function} error_callback   Error collback
 */
function attacher_service_get_user_collections(callback, error_callback) {
    new SSCollsUserWithEntries().handle(
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
 * A service callback to get all resources with certain tag within a collection
 * @param {function}    callback        Success callback
 * @param {function}    error_callback  Error callback
 * @param {string}      entity_uri      Entity (collection) uri
 * @param {array}       tag_labels      Array of tag labels
 */
function attacher_service_search_tags_within_entity(callback, error_callback, entity_uri, tag_labels) {
    new SSSearchWithTagsWithinEntity().handle(
            function(result) {
                callback(result);
            },
            function(result) {
                error_callback();
            },
            AttacherData.user,
            AttacherData.key,
            entity_uri,
            tag_labels
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
 * Deals with file downloads
 * @param {function}    error_callback  Error callback
 * @param {string}      uri             File URI
 * @param {string       label           Resource label (used for downloaded file name)
 */
function attacher_service_download_file(error_callback, uri, label) {
    // The solution might be to first call SSFileExtGet, and only then the file download code
    new SSFileExtGet().handle(
            function(result) {
                var mimeType = result.fileExt;
                new SSFileDownload().handle(
                        function(result) {
                            var a = document.createElement("a");

                            if (jSGlobals.endsWith(label, "." + mimeType)) {
                                a.download = label;
                            } else {
                                a.download = label + "." + mimeType;
                            }

                            a.href = window.URL.createObjectURL(result);
                            a.textContent = jSGlobals.download;

                            a.click();
                        },
                        function(result) {
                            error_callback();
                        },
                        AttacherData.user,
                        AttacherData.key,
                        uri
                        );
            },
            function(result) {
                error_callback();
            },
            AttacherData.user,
            AttacherData.key,
            uri
            );
}
