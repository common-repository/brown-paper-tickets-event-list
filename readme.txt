=== Brown Paper Tickets Event List Plugin ===
Contributors: Chandler Blum
Donate Link: N/A
Tags: bpt, brown paper tickets
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: 0.7.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Brown Paper Tickets Event List Plugin is a simple way to display events in a Wordpress post/page.

== Description ==

The Brown Paper Tickets Event List Plugin is a simple way to display events in a Wordpress post/page. You can display a single event, a list of all events or all of your events in a calendar.

The BPT Wordpress Plugin is FOSS, released under the [GNU GPL v2](http://www.gnu.org/licenses/gpl-2.0.txt).

It's source code can be found on [Github](https://github.com/BrownPaperTickets/bpt_wordpress_plugin).

**There are some caveats to using this plugin. PLEASE READ!**

* The data returned by the [pricelist](http://www.brownpapertickets.com/apidocs/2.0/pricelist.html) API call does not make a distinction between password protected prices and regular prices. As a result, prices that are typically hidden by passwords on BPT will show up via the plugin. **DO NOT use this plugin if you intend to use the event list feature or want your password protected prices to stay hidden.** Calendar format should be OK as it does not make the price list API call.

**Please use the [Issues](https://github.com/BrownPaperTickets/bpt_wordpress_plugin/issues) page to submit bugs, feature requests, etc.**

== Installation == 

To install the plugin, download the zip, extract it and upload the extracted folder to your plugins directory on your webserver.

From there, activate the plugin as normal. Then go to Settings > BPT API Settings and put in your Developer ID and your Client ID.

To obtain your developer ID, you must first have developer tools added to your Brown Paper Tickets account. First log into BPT, then go to [Account Functions](https://www.brownpapertickets.com/user/functions.html). Click Developer Tools and then add. You'll see a new link in the BPT navigation titled "Developer". Click that and you'll see your developer ID listed at the top.

Your client ID is typically whatever you use to log into Brown Paper Tickets.

== Plugin Usage ==

To use the plugin place the shortcode ``` [list_events] ``` in the post
or page that you want the listing to appear.

= Single Event Listing =

[list_events event_id="XXXXXX"] where XXXXXX is the ID of the event.

```
[list_events print_at_home="yes" physical="yes" will_call="yes"]
```

Use the above shortcodes to display various shipping options.

If no options are passed, the plugin will display will-call and print at home shipping options by default.

** The plugin has no way to ensure that the shipping options displayed actually exist on your BPT event. You need to be certain that the options are correct. If you display the wrong shipping options, ticket buyers will get an error upon being transferred to the BPT checkout telling them that the shipping option no longer exists. **

= Calendar Format =

Display a calendar listing all of your events:


[list_events calendar="yes"]


= About the Calendar =
NOTE: This feature is currently somewhat buggy.

The calendar is powered by the [jQuery Event Calendar plugin](http://www.vissit.com/projects/eventCalendar/).

It was created by [Jaime Fernández](http://www.vissit.com/jquery-event-calendar-plugin-english-version).

Some modifications have been made to the jQuery plugin for use in
wordpress and the way it displays dates.

- Replaced $ variable with jQuery. [More Info](http://codex.wordpress.org/Function_Reference/wp_enqueue_script#jQuery_noConflict_Wrappers).

- Switched the getYear, getMonth, getDate, etc methods to their get
UTC counterparts. 

- Displays dates in the 12 hour format.


= All Events =


[list_events]


= Customization =

You can style the output of the plugin by editing the style.css file
located in the plugin folder. Each element has a class.

== Changelog ==

= 0.7.3 =
* Added mobile shipping option

= 0.7.2 =
* Fixed issue with unapproved events being displayed.

= 0.7.1 = 
* Various Bug Fixes

= 0.7 =
* Transitioned to the plugin to make use of the bptPHP class (as a git sub
module).

* Extended the documentation available within the plugin.

* Moved CSS and JS into their own folders, modified plugin to match.

= 0.6 =
* Added the ability to select the shipping options available on events. If listing all events, the shipping options will apply to each event.

= 0.5.4 =
* Fixed Calendar list to only show dates for a specific event when event_id is set.


= 0.5.3 =
* Removed the following CSS:

 html {
	background-color:#eee;
 }
 body {
	font-family: Arial, "Lucida Grande", sans-serif;
	font-size: 13px;
	line-height: 18px;
	color: #555;
	background-color:#fff;
 }

from eventCalendar_theme_responsive.css

= 0.5.2 =
* Reformatted dates/prices to allow more than one price to be added
to the cart at a time.

* Fixed bug when pulling prices with a single date or inactive dates.

* Plugin no longer shows non-live events.

= 0.5.1 =
* Removed whitespace from bpt_wordpress.php

= 0.5 =
* Initial release

== To Do ==
* Further OOPifiy the plugin to meet Wordpress Plugin best practices/standards.

* Add options to allow users to specify how they want events/dates/prices
displayed.

* Extend jQuery Calendar plugin to allow users to set how they want 
calendar dates displayed in the initial call.

- Add caching of event data to speed up page loading.