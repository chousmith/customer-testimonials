=== Plugin Name ===
Contributors: chousmith
Tags: testimonials, testimonial, customer testimonials, customer, plugins
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: trunk

Showcase Testimonials praising your site/product(s), with easy (CPT) control of the content, Widgets to display Testimonials in sidebars, and Shortcodes.

== Description ==

ProGo Customer Testimonials creates a "Testimonials" Custom Post Type so you can easily add Testimonial quotes (headline / main quote / author's name / location) to your site. Widgets allow you to to pull in a randomly-chosen single Testimonial ("Random Testimonial") or multiple testimonials ("Testimonials") either one after another or rotating one at a time.

The plugin also registers a Shortcode so you can include Testimonials elsewhere on your site (in a "Testimonials" page you create, or wherever else), via:

[testimonials (...)] with the following arguments:

* num = the # of Testimonials you want to show. Use num=0 to list All. Default is 1.
* order = "menu" (default) to follow the Order attribute of the Testimonials, "random" to randomly choose the # of Testimonials.

so just using [testimonials] will list the first Testimonial listed in the Testimonials admin section, and [testimonials num=3 order="random"] will choose a random 3.

== Installation ==

1. Upload the entire `customer-testimonials` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the Testimonials content area to add/edit Testimonials
1. Use the Testimonials Widgets or [testimonials] Shortcode to showcase Testimonials on your site.

== Changelog ==

= 1.2.3 =
* had wrapped most of the Testimonial quote output pieces in filters, except the actual Testimonial quote (body content) itself
* this has been remedied with the new "progo_testimonials_quote_body" filter

= 1.2.2 =
* updated [testimonials] shortcode to allow for specifying a Category , via [testimonials cat={cat}] where {cat} = either the ID # or the term slug of the Testimonial Category of your choosing

= 1.2.1 =
* added Testimonial Categories to the back end : now you can sort your Testimonials for more specific control over what quotes appear where on your site!

= 1.2 =
* renamed the plugin to ProGo Customer Testimonials

= 1.0 =
* Initial release of the plugin

== Upgrade Notice ==

= 1.0 =
Initial release of the plugin
