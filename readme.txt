=== Plugin Name ===
Contributors: stevegrunwell, VanPattenMedia
Donate link: http://stevegrunwell.com/wp-client-reference
Tags: help, knowledgebase, client, reference
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 0.42

Create a reference guide for clients right in the WordPress administration area.


== Description ==

It's common practice for digital agencies to build a WordPress site/theme then simply hand it off to the client for day-to-day updating. The developers work hard to strike a balance between replicating the designer's vision and making the theme easy for the client to use. As the design becomes more complex the developer faces questions: Will the client remember to wrap this in a `<blockquote>`? Can we count on the content manager remembering to add `class="btn"` to that link?

It can be equally frustrating from the client side. You don't want to deal with learning HTML but you want your content to look just as well formatted as the stuff the agency put in at launch. The agency sent over a formatting/style guide but it never seems to be around when you need it.

WP Client Reference attempts to solve these problems by embedding client documentation directly into WordPress. Now agencies can include style guides, tutorials, FAQs, and more without worrying about content managers on the client side not knowing how to format that complicated call-to-action. It's also useful for any type of team to keep track of information (with versioning) right within WordPress.


== Installation ==

1. Upload the `wp-client-reference` plugin directory to `/wp-content/plugins/`
2. Activate the plugin
3. (Optional) Adjust the plugin settings on the "Options" page under the "Help Articles" menu item


== Frequently Asked Questions ==

= How do I customize the article views? =

WP Client Reference looks for a `wpclientref-views` directory in your current theme's directory. If it finds `front.php` (the default view), `single.php` (a single article), or `wpclientref.css` (article styles), it will use those files instead of the plugin's defaults.

= Can I change the name of the custom post type? =

Yes! If you need to change the name of the custom post type (default is `client_reference`) you can do so in the "Article Options" page. This will also update the posts in `wp_posts` accordingly.

= Why has the Knowledgebase replaced my Users menu? =

Version 0.4 of the plugin used menu position 70 as its default, which is normally occupied by the Users menu. It's been patched in version 0.41 but any users who installed pre-0.41 should manually change the menu position (Articles > Options) to a position not occupied by a native WordPress menu (71 is the new default for WP Client Reference).


== Changelog ==

= 0.42 =
* Fixed path issues with `WPClientReference::get_template_url()` when loading static assets from {THEME}/wpclientref-views/ (Issue #3). Special thanks to Chris VanPatten for tracking this down!

= 0.41 =
* Fixed issue with default 'Knowledgebase' position preventing access to Users menu (special thanks to duckgoesoink)


== Special Thanks ==

Thank you to duckgoesoink for catching the Knowledgebase/User conflict fixed in 0.41.