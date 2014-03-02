# WP Client Reference

It's common practice for digital agencies to build a WordPress site/theme then simply hand it off to the client for day-to-day updating. The developers work hard to strike a balance between replicating the designer's vision and making the theme easy for the client to use. As the design becomes more complex the developer faces questions: Will the client remember to wrap this in a `<blockquote>`? Can we count on the content manager remembering to add `class="btn"` to that link?

It can be equally frustrating from the client side. You don't want to deal with learning HTML but you want your content to look just as well formatted as the stuff the agency put in at launch. The agency sent over a formatting/style guide but it never seems to be around when you need it.

WP Client Reference attempts to solve these problems by embedding client documentation directly into WordPress. Now agencies can include style guides, tutorials, FAQs, and more without worrying about content managers on the client side not knowing how to format that complicated call-to-action. It's also useful for any type of team to keep track of information (with versioning) right within WordPress.

## Roadmap

### Version 0.4

* First public release to WordPress plugin repository
* Focus on custom post type and basic formatting

### Version 0.41

* Fixed issue #2

### Version 0.42

* Fixed issue #3
* Added @ChrisVanPatten as a collaborator

### Version 0.5

* Major refactor of the `WP_Client_Reference` class
* Use dashicons (WordPress 3.8+) for the Articles and Knowledgebase menus

#### Planned

* Sorting in the post list (Github issue #6)
* Re-work templates to work with the more responsive WordPress admin area (Github issue #5)
* Internationalization (Github issue #4)

### Version 0.6 and Beyond

* Further emphasis on client-facing view
* Introduction of custom taxonomies (categories and tags) for articles (Github issue #7)
* Search articles
* Explicitly set who can edit articles custom post type
* Customize `WPClientReference::get_breadcrumbs()` output
* Ability to remove all articles upon uninstall
* Customize the columns in the article list (`WPClientReference::get_article_column_content()`)
* Better handling of permalinks within the admin area
* Better navigation in the default article theme
* Ability to set an article as the front page of the articles viewer instead of looping through top-level articles
* Integrate the articles with the WordPress contextual help menu
* Better copywriting!