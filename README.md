Attacher
========

Attacher is a WordPress plugin for communication with Social Semantic Server.
Latest versions of WordPress are supported. Current version used in development
is 3.9.2.

Works with both MultiUser and non-MultiUser setups. The service credentials are
stored at the blog level (the system assumes that there is one Service user per
blog). In case of MU installation the configuration of Service and JS Client
locations is done by the Network Administrator.

Social Semantic Server Requirements
===================================

* Social Semantic Server - v4.1.0-alpha
* Social Semantic Server Client Side - v5.1.0-alpha

Source Code
===========

`$git clone https://github.com/learning-layers/Attacher.git`

Limitations
===========
1. Assumes one Service user per blog.
2. Downloading linked files would only work in latest versions of Chrome and
Opera (only true for post editing in admin interface).
3. Is using both SSSClientSide JS and own server-side implementation of API
calls (a higher probability for things to break with API changes and a need to
provide locations of both JS and real service addresses).
4. A post short link is being used instead of permalink as the permalink
structure may change, while shortilinks still staying always the same.

Installation
============

1. Place plugin code into **plugins** directory of WordPress installation.
2. Activate the plugin.
3. Visit the settings page and provide information on user credentials and
service locations (administrator privileges needed).
  * In case on MU setup service locations are provided by Network Administrator.

How Does it Work
================
1. Adds a MetaBox to Post edit view that lists all user collections and any
collections that have been marked as shared within the system.
2. Selecting each collection shown a tagcloud that can be used to browse the
tagged resources from specific collection (please note that user root collection
will show a tagcloud for all user resources and it can be used to browse
resources from multiple collections).
  * Shared collections from other users do not affect the this tagcloud
3. Resources are draggable and can be dropped on the post content editor (please
note that a resource anchor element is holding some additional information that
would allow to trigger the file downloads in future).
4. When a post is viewed, all the links pointing to uploaded files will have a
listener that would trigger the download of a corresponding file.
5. A user can also rate the post while it is being viewed. In case of MU
setup the credentials of the currently logged in user will be used (those will
be extracted from the blogs owned by the user). A fallback to current blog
credentials will be used instead.