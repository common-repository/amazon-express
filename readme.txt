=== Amazon Express ===
Contributors: rampantlogic
Donate link: http://www.rampantlogic.com/amazonx
Tags: amazon, associates, affiliate, books, reviews, cover
Requires at least: 3.0
Tested up to: 3.0.4
Stable tag: 1.0.1

Loads Amazon product images and Amazon Associates referral links into posts from the ISBN/ASIN.

== Description ==

Amazon Express allows you to easily insert Amazon Associates links and Amazon product images, including images of book covers, into your Wordpress posts. This is ideal for quickly creating book review or product review posts. Either the Amazon Standard Identification Number (ASIN) or ISBN can be entered into the Amazon Express metabox in the admin page for any post to automatically generate the content. You can also specify a rating for each product that will be displayed as stars in the post. Using the shortcode `amazonx`, you can insert a listing of all products on your blog. This listing can be filtered based on the rating using the `minrating`, `maxrating`, and `category` parameters.

== Installation ==

1. Extract `amazonx.zip` to `/wp-content/plugins/` directory on server.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add an ASIN or ISBN in the Amazon Express metabox in any post or page.

== Frequently Asked Questions ==

= How can I generate a listing of products in a specific category? =

First find the category ID by entering the wp-admin page, click on Posts->Categories, and then click on the category you would like to list. Check the address bar for the category ID at the end: tag_ID=##. Then create a post or page and write `[shortcode category=##]` in the content box.

= How can I filter a listing based on ratings? =

You can add the `minrating` and `maxrating` parameters to the shortcode to filter results. For example, if you only want to list products with a rating of 4 or 5 stars, you can use `[amazonx minrating=4]`.

= How can I remove the category names in a listing? =

Add the parameter `show_categories=0` to the shortcode. For example `[amazonx category=12 show_categories=0]`.

== Screenshots ==

1. Example of a post with a product image and Associate link.
2. The metabox found in the edit page for a post where you can specify the ASIN/ISBN and rating.
3. Amazon Express options page where you can enter your Amazon credentials and CSS overrides.

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0 =
* Initial release