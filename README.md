# WP Client Reference

It's common practice for digital agencies to build a WordPress site/theme then simply hand it off to the client for day-to-day updating. The developers work hard to strike a balance between replicating the designer's vision and making the theme easy for the client to use. As the design becomes more complex the developer faces questions: Will the client remember to wrap this in a `<blockquote>`? Can we count on the content manager remembering to add `class="btn"` to that link?
  
It can be equally frustrating from the client side. You don't want to deal with learning HTML but you want your content to look just as well formatted as the stuff the agency put in at launch. The agency sent over a formatting/style guide but it never seems to be around when you need it.

WP Client Reference attempts to solve these problems by embedding client documentation directly into WordPress. Now agencies can include style guides, tutorials, FAQs, and more without worrying about content managers on the client side not knowing how to format that complicated call-to-action.

## Roadmap

**Version 0.4**

* Scheduled to be first public release to WordPress plugin repository
* Focus on custom post type and basic formatting

TODO:

* Finish `WPClientReference::get_article_column_content()`
* Update copy in `WPClientReference::load_settings_view_main()`
* Ability to remove all WP Client Reference entries in `wp_options` upon uninstall
* Write readme.txt; validate with [WordPress readme.txt validator](http://wordpress.org/extend/plugins/about/validator/)
* Fix any outstanding issues in [Github issue tracker](https://github.com/stevegrunwell/WP-Client-Reference/issues/)

**Version 0.5**

* Further emphasis on client-facing view
* Introduction of custom taxonomies (categories and tags) for articles
* Search articles
* Explicitly set who can edit articles custom post type
* More customization options
* Internationalization
* Import/export of articles
* Customize `WPClientReference::get_breadcrumbs()` output
* Ability to remove all articles upon uninstall