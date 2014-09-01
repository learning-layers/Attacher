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

function attacher_service_user_tags_get(callback, error_callback, user_uri) {
    new SSTagsGet(
            function(result) {
                callback(result.tags);
            },
            function(result) {
                error_callback();
            },
            AttacherData.user,
            AttacherData.key,
            user_uri, // forUser
            null, // entities
            null, // labels
            null, // space
            null // startTime
    );
}

function attacher_service_all_tags_get(callback, error_callback) {
    new SSTagsGet(
            function(result) {
                callback(result.tags);
            },
            function(result) {
                error_callback();
            },
            AttacherData.user,
            AttacherData.key,
            null, // forUser
            null, // entities
            null, // labels
            null, // space
            null // startTime
    );
}
/**
 * A service callback to get all resources with certain tag
 * @param {function}    callback        Success callback
 * @param {function}    error_callback  Error callback
 * @param {array}       tag_labels      Array of tag labels
 */
function attacher_service_search_tags(callback, error_callback, tag_labels) {
    new SSSearch(
            function(result) {
                callback(result.entities);
            },
            function(result) {
                error_callback();
            },
            AttacherData.user,
            AttacherData.key,
            null, // keywordsToSearchFor
            false, // includeTextualContent
            null, // wordsToSearchFor
            true, // includeTags
            tag_labels, // tagsToSearchFor
            false, // includeMIs
            null, // misToSearchFor
            false, // includeLabel
            null, // labelsToSearchFor
            false, // includeDescription
            null, // descriptionsToSearchFor
            [], // typesToSearchOnlyFor
            true, // includeOnlySubEntities
            null, // entitiesToSearchWithin
            false, // includeRecommendedResults
            false // provideEntries
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
    new SSAuthCheckCred(
            function(result) {
                AttacherData.key = result.key;
                AttacherData.user = result.user;
                callback();
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
    // XXX Might make sense to first make call to SSEntityDescGet, that would provide
    // the title and then call the download functionality
    // The solution might be to first call SSFileExtGet, and only then the file download code
    new SSFileExtGet(
            function(result) {
                var mimeType = result.fileExt;
                new SSFileDownload(
                        function(result) {
                            console.log(result);
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

/**
 * A service call that brings the entity overall rating
 * @param {function}    callback        Success callback
 * @param {function}    error_callback  Error callback
 * @param {String}      uri             Entity URI
 */
function attacher_service_raiting_overall_get(callback, error_callback, uri) {
    new SSRatingOverallGet(
            function(result) {
                callback(result);
            },
            function(result) {
                error_callback();
            },
            AttacherData.user,
            AttacherData.key,
            uri
            );
}
