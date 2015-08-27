=== Custom taxonomy meta ===
Tags:  meta, custom field, taxonomy, taxonomy meta, term meta,category meta, custom fields ,taxonomy image, taxonomy description, taxonomy short description,category image, category description, category short description,category images
Contributors: amu02aftab
Author: amu02aftab
Tested up to: 4.3
License: GPLv2
Requires at least: 3.5.0
Stable tag: 1.0


Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=amu02.aftab@gmail.com&item_name=Donation+WP+Custom+texonomy+meta

== Description ==
Plugin to add custom meta fields within built in and custom taxonomies. Simply add the desired fields by going through WP-admin -> Settings ->Taxonomy Meta . 

<strong>you can add following fields with category/taxonomy</strong>

* Image.
* Input Text.
* Textarea.
* Checkbox.

= Features =
* Very simple in use
* Can be customized easily.

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
4. Go to your WP-admin ->Settings ->Taxonomy Meta  displayed in the category modification form with the meta you configured.
5. You can use the below function into your templates to retrieve all meta using 'category id':
`
<?php 
if (function_exists('get_all_wp_terms_meta'))
{ 
   $arrayMetaList = get_all_wp_terms_meta($category_id);
} 

// array all meta fields for category/term
print_r($arrayMetaList); 
?>
`
where $category_id is category/term id .

6. you can use the below function into your templates to retrieve any particular meta using 'category id' and 'meta key':
`
<?php 
if (function_exists('wp_get_terms_meta'))
{ 
  $MetaValue = wp_get_terms_meta($category_id, $meta_key ,true); 
} 

//meta value for meta key $meta_key
echo $metaValue; 

?>
`
where $category_id is 'category/term id' and $meta_key is 'meta key'.


