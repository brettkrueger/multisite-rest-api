=== Multisite REST API ===
Contributors: brettkrueger  
Tags: json, API, multisite  
Requires at least: 5.1  
Requires PHP: 7.0+  
Tested up to: 6.0  
Stable tag: v1.1
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

This plugin provides several endpoints for creating, listing, updating, and deleting WordPress multisites. It aims to make headless WordPress multisite environments not only possible, but practical.

Take a look at our [github page](http://github.com/brettkrueger/multisite-rest-api/) for the full documentation.

= Authentication =

All endpoints require authentication from an existing WordPress user.
We suggest using JWT through something like [simple-jwt-login](https://wordpress.org/plugins/simple-jwt-login/).

== Frequently Asked Questions ==

= Where is the documentation for the API? =

Take a look at our [github page](http://github.com/brettkrueger/multisite-rest-api/) for the full documentation.

== Upgrade Notice ==

Upgrades are pushed through WordPress.org.

== Screenshots ==

1. [!Screenshot](https://raw.githubusercontent.com/brettkrueger/multisite-rest-api/master/screenshot.png)

== Changelog ==

= 1.1.0 =
* Adding assign function and GET specification for /sites endpoint.

= 1.0.0 =
* Create, List, Update, Delete multisites.

