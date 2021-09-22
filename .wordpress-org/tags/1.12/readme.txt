=== SearchCloak ===
Contributors: firebrandllc, lonkoenig
Donate link:
Tags: search, admin, google, cse
Requires at least: 3.2.1
Tested up to: 3.9
Stable tag: 921808
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Hides Pages & Posts from search results and search engine indexes.

== Description ==
**SearchCloak** allows you to hide specific Posts and Pages from search results.

There are many reasons you may want to remove specific pages from search results:
* Parent pages used to organize URLs, not present content
* Data pages that plugins use to create more complete user-focused pages
* Pay-per-click Landing Pages
* Pages that present similar or duplicate content that might otherwise appear multiple times in search results

This plugin adds a "SearchCloak" option to the edit screen of Posts and Pages. 

The options are: 
*show* - Show this Page or Post in search results (default)
*cloak* - Hide this Page or Post from search results
*children* - Hide this Page and all its children from search results (not available on Posts since they don't have children)

## Features:
* Cloaked pages are also marked as "noindex" for search engines
* You can hide *ALL* the children of a given page
* Compatible with most search plugins
* Works with Google Custom Search


== Installation ==
##Install from WordPress.org

1. Log into your website administrator panel
1. Go to Plugins page and select "Add New"
1. Search for "SearchCloak"
1. Click "Install Now" on the SearchCloak entry
1. Click Activate Plugin

##Install via ftp

1. Download the plugin zip file using the button above
1. Log into your website administrator panel
1. Go to Plugins page and select "Add New"
1. Click "Upload"
1. Choose your recently downloaded zip file
1. Click the Install Now button
1. Click Activate Plugin


== Frequently Asked Questions ==

= Does SearchCloak work with Google Custom Search? =

SearchCloak adds the 
    `<meta name="robots" content="noindex,follow">` 
head element to cloaked Pages and Posts. This should stop them from being indexed and, therefore, prevent them from appearing in your Custom Search results.


== Screenshots ==
1. The SearchCloak box in Page editing.


== Changelog ==

= 1.12 =
Removed extraneous files

= 1.11 =
Fixed screenshots

= 1.1 =
Added ability to cloak Posts

= 1.0 =
Initial commit

== Upgrade Notice ==

Version 1.1 added the ability to cloak regular Posts. Please upgrade.
