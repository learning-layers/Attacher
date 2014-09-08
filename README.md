Attacher
========

Attacher is a WordPress plugin for communication with Social Semantic Server.
Latest versions of WordPress are supported. Current version used in development
is 3.9.2.

Works with both MultiUser and non-MultiUser setups. The service credentials are
stored at the blog level (the system assumes that there is one Service user per
blog). In case of MU installation the configuration of Service and JS Client
locations is done by the Network Administrator.

**NB! This is a special version used for internal evaluation.**

Social Semantic Server Requirements
===================================

* Social Semantic Server - v6.0.0-alpha
* Social Semantic Server Client Side - v7.0.0-alpha

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
1. Adds a MetaBox to Post edit view that allows user to switch between two
tagclouds. The default one consists of all user tags and the other one consists
of all tags user within the system (excludes private tags by other users).
2. Clicking on a tag will search the system for resources and show them in two
lists. The first one will hold all the resources created by the user and the
other one will hold all the resources created by others.
3. Resources are draggable and can be dropped on the post content editor (please
note that default drag-and-drop is used, thus this will not add any additional
information to the links; this will not allow to download uploaded files).
4. A user can also rate the post while it is being viewed. In case of MU
setup the credentials of the currently logged in user will be used (those will
be extracted from the blogs owned by the user). A fallback to current blog
credentials will be used instead.