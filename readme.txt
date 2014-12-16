=== Pop Up Archive for WordPress ===
Contributors: popuparchive
Tags: audio, speech-to-text, media search, embed player, auto tags
Requires at least: 3.6
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates with the Pop Up Archive platform to deliver searchable audio using cutting edge speech-to-text technology.

== Description ==
The Pop Up Archive Wordpress Plugin integrates with the Pop Up Archive platform to deliver searchable audio using cutting edge speech-to-text technology. To use: sign up for Pop Up Archive (popuparchive.com) and upload any audio file. Pop Up Archive will tag, index & transcribe it automatically. The WP plugin allows you to embed your Pop Up Archive audio and tags in your WordPress site. 

== Installation ==
1. Copy the `popuparchive-wp` directory into your `wp-content/plugins` directory
2. Navigate to the *Plugins* dashboard page
3. Locate the `Pop Up Archive` plugin
4. Click on *Activate*

This will activate the Pop Up Archive Plugin.

To use the WP Pop Up Archive plugin you will firstly need to create a Pop Up Archive app using your current Pop Up Archive account details and then paste some of the details from your app in the configuration settings of this page. 

Creating Your Pop Up Archive App

This literally takes a minute to do. To create a Pop Up Archive app go to https://www.popuparchive.com and fill in the details as follows:
Enter the following string for the "Name" of your application - popuparchive-wp
Copy and paste the url provided  for the "Redirect uri" of your application 

Configuring the Plugin

After creating the application on the Pop Up Archive site, copy the following details from your app and paste in the configuration settings on this page:
Application ID - Copy this value from your Pop Up Archive app and paste in the "Application ID" field below.
Secret - Copy this value of your application's "Secret" and paste in the "Secret" field below.
After entering the configuration settings click the "Save Settings" button.

After saving your settings click the "Connect To Pop Up Archive" link in the "Pop Up Archive Connection Status" section below. This will take you to the Pop Up Archive site and ask you to allow the "popuparchive-wp" plugin to connect to your Pop Up Archive account. Click the "Connect" button.

== Screenshots ==
1. The embeddable player and auto tags for a Pop Up Archive item.
2. Add media view.

== Changelog ==

= 1.1 =
* Added paging to add media view
* Added search to add media view

= 1.0 =