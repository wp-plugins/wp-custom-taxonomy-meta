=== WP Custom taxonomy meta ===
Tags:  meta, custom field, taxonomy, taxonomy meta, term meta,category meta, custom fields ,taxonomy image, taxonomy description, taxonomy short description,category image, category description, category short description,category images
Contributors: amu02aftab
Author: amu02aftab
Tested up to: 4.2.2
License: GPLv2
Requires at least: 3.5.0
Stable tag: 1.0


Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=amu02.aftab@gmail.com&item_name=Donation+WP+Custom+texonomy+meta

== Description ==
Plugin to add custom meta fields within built in and custom taxonomies. Simply add the desired fields by going through WP-admin -> Settings ->Taxonomy Meta . 

= Features =
1.Using this plugin, you can add following fields with category/taxonomy.
a.Taxonomy image field
b.Taxonomy text field
c.Taxonomy textarea field
d.Taxonomy checkbox field
2.Very simple in use
3.Can be customized easily.

== Screenshots ==

1. Settings page where you can add the custom fields
2. Example of the custom fields under the general category fields


== Frequently Asked Questions ==
1. No technical skills needed.



== Changelog ==
This is first version no known errors found

== Upgrade Notice == 
This is first version no known notices yet

== Installation ==
1. Unzip into your `/wp-content/plugins/` directory. If you're uploading it make sure to upload
the top-level folder. Don't just upload all the php files and put them in `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to your WP-admin ->Settings menu a new "Taxonomy Meta" page is created.
4. go to your WP-admin ->Settings ->Taxonomy Meta  displayed in the category modification form with the meta you configured.
5. you can use the following functions into your templates to retrieve all meta:

if (function_exists('get_all_wp_terms_meta'))
{
    $metaList = get_all_wp_terms_meta($category_id);
}

6. you can use the following functions into your templates to retrieve 1 meta:

if (function_exists('wp_get_terms_meta'))
{
    $metaValue = wp_get_terms_meta($category_id, $meta_key);
}


