=== SearchCloak ===
Contributors: firebrandllc, lonkoenig
Donate link:
Tags: search, admin, google, cse
Requires at least: 3.2.1
Tested up to: 5.0.0
Stable tag: 2.1.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Hides Pages & Posts from search results and search engine indexes.

== Description ==    
**SearchCloak** allows you to hide specific Posts and Pages from search results.

There are many reasons you may want to remove specific pages from search results:

* Parent pages used to organize URLs or menus, but don't contain actual content
* Partial page "sections" assembled by plugins into more complete pages
* Pay-per-click Landing Pages (You probably don't want those showing up in search engines.)
* Pages that present similar or duplicate content that might otherwise appear multiple times in search results

This plugin adds a "SearchCloak" option to the edit screen of Posts and Pages. 

When running the Gutenberg editor, this option block will appear under the "Document" tab in the right-hand column.

The options are:    
*show* - Show this Page or Post in search results (default)    
*cloak* - Hide this Page or Post from search results    
*children* - Hide this Page and all its children from search results (not available on Posts since they don't have children)

### Features:
* Cloaked pages are also marked as "noindex" for search engines
* You can hide *ALL* the children of a given page
* Compatible with most search plugins
* Works with Google Custom Search
* Admin search results DO include cloaked pages
* Cleans up multiple "robots" meta tags in the head section (sometimes happens with multiple plugins or settings)
* Cloaking can be enabled for custom post types in the dashboard


== Installation ==

###Install from WordPress.org

1. Log into your website administrator panel
1. Go to Plugins page and select "Add New"
1. Search for "SearchCloak"
1. Click "Install Now" on the SearchCloak entry
1. Click Activate Plugin


###Install via ftp

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

= Does SearchCloak work with multisite? =
Yes. SearchCloak doesn't have any multi-site specific features, but works fine on multi-site installs.

= Does SearchCloak work with custom post types? =
Yes! As of version 2.0.0, SearchCloak lets you specify which Custom Post Types should get the SearchCloak checkboxes.

== Screenshots ==
1. The SearchCloak box in Page editing.
2. The SearchCloak dashboard settings page.

== Changelog ==

= 2.1.3=   
Tested and updated for WordPress version 5.0.0
Documentation updated for Gutenberg
Clean up markdown in readme.txt

= 2.1.2 =   
Tested and updated for WordPress version 4.8

= 2.1.1 =   
Updated documentation

= 2.1.0 =   
Updated for WordPress version 4.7
Combines duplicate NOINDEX tags if multiple plugins generated extra tags

= 2.0.3 =   
Test for empty case when displaying dashboard settings page

= 2.0.2 =   
Fix readme

= 2.0.1 =   
Check existence of saved values before drawing edit box

= 2.0.0 =   
Add support for custom post types

= 1.3.2 =   
Update testing information in documentation

= 1.3.1 =   
Update readme to fix typos and add FAQs

= 1.3 =   
Reformat comments.   
Don't cloak admin searches

= 1.1.1 =   
Minor documentation tweaks

= 1.1 =   
Added ability to cloak Posts

= 1.0 =   
Initial commit

== Upgrade Notice ==

Version 2.0.3 fixes php error when displaying dashboard settings.
