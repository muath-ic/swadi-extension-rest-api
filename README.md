# WP REST API - extension for posts and categories endpoints

**This plugin is currently unmaintained.**

In WordPress 4.7 the `filter` argument for any post endpoint was removed, The `filter` argument allows the posts to be
filtered using `WP_Query` public query vars. This plugin restores the `filter` parameter for sites that were
previously using it.
And this plugin adds ability to know if selected category has children of not

## Usage

Use the `filter` parameter on any post endpoint such as `/wp/v2/posts` or `/wp/v2/pages` as an array of `WP_Query`
argument.

You can find 'swadi_category_children' on categories endpoint
