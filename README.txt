=== Multisite REST API ===
Contributors: brettkrueger
Tags: json, api, multisite
Requires at least: 5.1
Requires PHP: 7.0
Tested up to: 6.5.5
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides several endpoints for creating, listing, updating, and deleting WordPress multisites. It aims to make headless WordPress multisite environments not only possible, but practical.

Take a look at our [github page](http://github.com/brettkrueger/multisite-rest-api/) for the full documentation.

== Authentication ==

All endpoints require authentication from an existing WordPress user.
We suggest using JWT through something like [simple-jwt-login](https://wordpress.org/plugins/simple-jwt-login/).

== Frequently Asked Questions ==

= Where is the documentation for the API? =

Take a look at our [github page](http://github.com/brettkrueger/multisite-rest-api/) for the full documentation.

== Upgrade Notice ==

Upgrades are pushed through WordPress.org.

== Screenshots ==

1. [!Screenshot](https://raw.githubusercontent.com/brettkrueger/multisite-rest-api/main/screenshot.png)

== Changelog ==

= 1.3.0 =
* Get or create a new user when creating a site

= 1.2.1 =
* Fix versioning in SVN

= 1.2.0 =
* General quality of life improvements 

= 1.1.0 =
* Adding assign function and GET specification for /sites endpoint.

= 1.0.0 =
* Create, List, Update, Delete multisites.

