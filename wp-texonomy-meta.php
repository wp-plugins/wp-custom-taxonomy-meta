<?php
/**
 * Plugin Name: Category and Taxonomy Meta Fields
 * Plugin URI: https://aftabhusain.wordpress.com/
 * Description:  Simply add the desired fields by going through WP-admin -> Settings ->Taxonomy Meta
 * Version: 1.0.0
 * Author: Aftab Husain
 * Author URI: https://aftabhusain.wordpress.com/
 * License: GPLv2
 */

if (!defined('WP_CONTENT_DIR')) {
    
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
    
}

if (!defined('DIRECTORY_SEPARATOR')) {
    
    if (strpos(php_uname('s'), 'Win') !== false) {
        
        define('DIRECTORY_SEPARATOR', '\\');
        
    } else {
        
        define('DIRECTORY_SEPARATOR', '/');
        
    }
}

$pluginPath = ABSPATH.PLUGINDIR.DIRECTORY_SEPARATOR."wp-custom-taxonomy-meta";
define('WPTM_PATH', $pluginPath);
$filePath = $pluginPath.DIRECTORY_SEPARATOR.basename(__FILE__);
$asolutePath = dirname(__FILE__).DIRECTORY_SEPARATOR;
define('WPTM_ABSPATH', $asolutePath);

// Initialization and Hooks
global $wpdb;
global $wp_version;
global $wpaft_version;
global $wpaft_db_version;
global $wpaft_table_name;
global $wp_version;

$wpaft_version       = '1.0.0';
$wpaft_db_version    = '0.0.1';
$wpaft_table_name    = $wpdb->prefix.'termsmeta';

register_activation_hook($filePath,'wpaft_install');

if ($wp_version >= '2.7') {
    
    register_uninstall_hook($filePath,'wpaft_uninstall');
    
} else {
    
    register_deactivation_hook($filePath,'wpaft_uninstall');
    
}

// Actions & Filters
add_action('admin_init', 'wpaft_init');
add_filter('admin_enqueue_scripts','wpaft_admin_enqueue_scripts');

if (is_admin()) {
    
    include WPTM_ABSPATH.'includes'.DIRECTORY_SEPARATOR.'options.php';
    
    $WPTMAdmin = new wpaft_admin();
    
}

/**
 * Function called when installing or updgrading the plugin.
 * @return void.
 */
function wpaft_install() {
    
    global $wpdb;
    global $wpaft_table_name;
    global $wpaft_db_version;

    // create table on first install
    if ($wpdb->get_var("show tables like '$wpaft_table_name'") != $wpaft_table_name) {

        wpaft_createTable($wpdb, $wpaft_table_name);
        add_option("wpaft_db_version", $wpaft_db_version);
        add_option("wpaft_configuration", '');
        
    }

    // On plugin update only the version nulmber is updated.
    $installed_ver = get_option( "wpaft_db_version" );
    
    if ($installed_ver != $wpaft_db_version) {

        update_option( "wpaft_db_version", $wpaft_db_version );
        
    }

}

/**
 * Function called when un-installing the plugin.
 * @return void.
 */
function wpaft_uninstall() {
    
    global $wpdb;
    global $wpaft_table_name;

    // delete table
    if($wpdb->get_var("show tables like '$wpaft_table_name'") == $wpaft_table_name) {

        wpaft_dropTable($wpdb, $wpaft_table_name);
    }
    
    delete_option("wpaft_db_version");
    delete_option("wpaft_configuration");
    
}

/**
 * Function that creates the wptm table.
 *
 * @param $wpdb : database manipulation object.
 * @param $table_name : name of the table to create.
 * @return void.
 */
function wpaft_createTable($wpdb, $table_name) {
    
    $sql = "CREATE TABLE  ".$table_name." (
          meta_id bigint(20) NOT NULL auto_increment,
          terms_id bigint(20) NOT NULL default '0',
          meta_key varchar(255) default NULL,
          meta_value longtext,
          PRIMARY KEY  (`meta_id`),
          KEY `terms_id` (`terms_id`),
          KEY `meta_key` (`meta_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    
    $results = $wpdb->query($sql);
    
}

/**
 * Function that drops the plugin table.
 *
 * @param $wpdb : database manipulation object.
 * @param $table_name : name of the table to create.
 * @return void.
 */
function wpaft_dropTable($wpdb, $table_name) {
    
    $sql = "DROP TABLE  ".$table_name." ;";

    $results = $wpdb->query($sql);
    
}

/**
 * Function that initialise the plugin.
 * It loads the translation files.
 *
 * @return void.
 */
function wpaft_init() {
    
    global $wp_version;
    
 
    
    if ($wp_version >= '3.0') {
        
        add_action('created_term', 'wpaft_save_meta_tags');
        add_action('edit_term', 'wpaft_save_meta_tags');
        add_action('delete_term', 'wpaft_delete_meta_tags');
        
        $wpaft_taxonomies = get_taxonomies('','names');
        
        if (is_array($wpaft_taxonomies) ) {
            
            foreach ($wpaft_taxonomies as $wpaft_taxonomy ) {
                
                add_action($wpaft_taxonomy . '_add_form_fields', 'wpaft_add_meta_textinput');
                add_action($wpaft_taxonomy . '_edit_form', 'wpaft_add_meta_textinput');
                
            }
            
        }
        
    } else {
        
        add_action('create_category', 'wpaft_save_meta_tags');
        add_action('edit_category', 'wpaft_save_meta_tags');
        add_action('delete_category', 'wpaft_delete_meta_tags');
        add_action('edit_category_form', 'wpaft_add_meta_textinput');
        
    }
    
}

/**
 * Add the loading of needed javascripts for admin part.
 *
 */
function wpaft_admin_enqueue_scripts() {
    
    if (is_admin() && isset($_REQUEST["taxonomy"])) {
        
        wp_register_style('thickbox-css', '/wp-includes/js/thickbox/thickbox.css');
        wp_enqueue_style('thickbox-css');
        
        wp_enqueue_script('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('quicktags');
        wp_enqueue_script('wp-texonomy-meta-scripts','/wp-content/plugins/wp-custom-taxonomy-meta/includes/wp-texonomy-meta-scripts.js');
        
    }
    
}

/**
 * add_terms_meta() - adds metadata for terms
 *
 *
 * @param int $terms_id terms (category/tag...) ID
 * @param string $key The meta key to add
 * @param mixed $value The meta value to add
 * @param bool $unique whether to check for a value with the same key
 * @return bool
 */
function add_terms_meta($terms_id, $meta_key, $meta_value, $unique = false) {

    global $wpdb;
    global $wpaft_table_name;

    // expected_slashed ($meta_key)
    $meta_key   = stripslashes($meta_key);
    $meta_value = stripslashes($meta_value);
    
    if ($unique && $wpdb->get_var($wpdb->prepare("SELECT meta_key FROM $wpaft_table_name WHERE meta_key = %s AND terms_id = %d", $meta_key, $terms_id ))) {
        
        return false;
        
    }

    $meta_value = maybe_serialize($meta_value);
    
    $wpdb->insert($wpaft_table_name,compact('terms_id', 'meta_key', 'meta_value') );

    wp_cache_delete($terms_id, 'terms_meta');

    return true;
    
}

/**
 * delete_terms_meta() - delete terms metadata
 *
 *
 * @param int $terms_id terms (category/tag...) ID
 * @param string $key The meta key to delete
 * @param mixed $value
 * @return bool
 */
function delete_terms_meta($terms_id, $key, $value = '') {

    global $wpdb;
    global $wpaft_table_name;

    // expected_slashed ($key, $value)
    $key    = stripslashes($key);
    $value  = stripslashes($value);

    if (empty($value)) {
        
        $sql = $wpdb->prepare("SELECT meta_id FROM $wpaft_table_name WHERE terms_id = %d AND meta_key = %s", $terms_id, $key );
        $meta_id = $wpdb->get_var($sql);
        
    } else {
        
        $sql = $wpdb->prepare("SELECT meta_id FROM $wpaft_table_name WHERE terms_id = %d AND meta_key = %s AND meta_value = %s", $terms_id, $key, $value );
        $meta_id = $wpdb->get_var($sql);
        
    }

    if (!$meta_id) {
        
        return false;
        
    }

    if (empty($value)) {
        
        $wpdb->query($wpdb->prepare("DELETE FROM $wpaft_table_name WHERE terms_id = %d AND meta_key = %s", $terms_id, $key));
        
    } else {
        
        $wpdb->query($wpdb->prepare("DELETE FROM $wpaft_table_name WHERE terms_id = %d AND meta_key = %s AND meta_value = %s", $terms_id, $key, $value));
        
    }

    wp_cache_delete($terms_id, 'terms_meta');

    return true;
    
}

/**
 * wp_get_terms_meta() - Get a terms meta field
 *
 *
 * @param int $terms_id terms (category/tag...) ID
 * @param string $key The meta key to retrieve
 * @param bool $single Whether to return a single value
 * @return mixed The meta value or meta value list
 */
function wp_get_terms_meta($terms_id, $key, $single = false) {

    $terms_id = (int) $terms_id;

    $meta_cache = wp_cache_get($terms_id, 'terms_meta');

    if ( !$meta_cache ) {
        
        update_termsmeta_cache($terms_id);
        $meta_cache = wp_cache_get($terms_id, 'terms_meta');
        
    }

    if ( isset($meta_cache[$key]) ) {
        
        if ( $single ) {
            
            return maybe_unserialize($meta_cache[$key][0]);
            
        } else {
            
            return array_map('maybe_unserialize', $meta_cache[$key]);
            
        }
        
    }
    
    return '';
    
}

/**
 * get_all_wp_terms_meta() - Get all meta fields for a terms (category/tag...)
 *
 *
 * @param int $terms_id terms (category/tag...) ID
 * @return array The meta (key => value) list
 */
function get_all_wp_terms_meta($terms_id) {

    $terms_id = (int) $terms_id;

    $meta_cache = wp_cache_get($terms_id, 'terms_meta');

    if ( !$meta_cache ) {
        
        update_termsmeta_cache($terms_id);
        $meta_cache = wp_cache_get($terms_id, 'terms_meta');
        
    }

    return maybe_unserialize($meta_cache);

}

/**
 * update_termsmeta_cache()
 *
 *
 * @uses $wpdb
 *
 * @param array $category_ids
 * @return bool|array Returns false if there is nothing to update or an array of metadata
 */
function update_termsmeta_cache($terms_ids) {

    global $wpdb;
    global $wpaft_table_name;

    if (empty($terms_ids)) {
        
        return false;
        
    }

    if (!is_array($terms_ids)) {
        
        $terms_ids = preg_replace('|[^0-9,]|', '', $terms_ids);
        $terms_ids = explode(',', $terms_ids);
        
    }

    $terms_ids = array_map('intval', $terms_ids);

    $ids = array();
    
    foreach ((array) $terms_ids as $id) {
        
        if ( false === wp_cache_get($id, 'terms_meta') ) {
            
            $ids[] = $id;
            
        }
        
    }

    if (empty($ids)) {
        
        return false;
        
    }

    // Get terms-meta info
    $id_list = join(',', $ids);
    $cache = array();
    
    if ($meta_list = $wpdb->get_results("SELECT terms_id, meta_key, meta_value FROM $wpaft_table_name WHERE terms_id IN ($id_list) ORDER BY terms_id, meta_key", ARRAY_A)) {
        
        foreach ((array) $meta_list as $metarow) {
            
            $mpid = (int) $metarow['terms_id'];
            $mkey = $metarow['meta_key'];
            $mval = $metarow['meta_value'];

            // Force subkeys to be array type:
            if (!isset($cache[$mpid]) || !is_array($cache[$mpid])) {
                
                $cache[$mpid] = array();
                
            }
            
            if (!isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey])) {
                
                $cache[$mpid][$mkey] = array();
                
            }

            // Add a value to the current pid/key:
            $cache[$mpid][$mkey][] = $mval;
            
        }
        
    }

    foreach ( (array) $ids as $id ) {
        
        if (!isset($cache[$id])) {
            
            $cache[$id] = array();
            
        }
        
    }

    foreach ( array_keys($cache) as $terms) {
        
        wp_cache_set($terms, $cache[$terms], 'terms_meta');
        
    }

    return $cache;
    
}

/**
 * Function that saves the meta from form.
 *
 * @param $id : terms (category) ID
 * @return void;
 */
function wpaft_save_meta_tags($id) {

    $metaList = get_option("wpaft_configuration");
    
    // Check that the meta form is posted
    $wpaft_edit = $_POST["wpaft_edit"];
    
    if (isset($wpaft_edit) && !empty($wpaft_edit)) {
        
        foreach ($metaList as $inputName => $inputType) {
        
            if ($inputType['taxonomy'] == $_POST['taxonomy']) {
                
                // Replace spaces with underscores for nomn-sanitized input names
                $inputValue = $_POST['wpaft_'.str_replace(' ','_',$inputName)];
                
                delete_terms_meta($id, $inputName);
                
                if (isset($inputValue) && !empty($inputValue)) {
                    
                    add_terms_meta($id, $inputName, $inputValue);
                    
                }
                
            }
        
        }
        
    }
}

/**
 * Function that deletes the meta for a terms (category/..)
 *
 * @param $id : terms (category) ID
 * @return void
 */
function wpaft_delete_meta_tags($id) {
    
    $metaList = get_option("wpaft_configuration");
    
    foreach($metaList as $inputName => $inputType) {
        
        delete_terms_meta($id, $inputName);
        
    }
    
}

/**
 * Function that display the meta text input.
 *
 * @return void.
 */
function wpaft_add_meta_textinput($tag) {
    
    global $category, $wp_version, $taxonomy;
    
    $category_id = '';
    
    if ($wp_version >= '3.0') {
        
        $category_id = (is_object($tag))?$tag->term_id:null;
        
    } else {
        
        $category_id = $category;
        
    }
    
    $metaList = get_option("wpaft_configuration");
    
    if (is_object($category_id)) {
        
        $category_id = $category_id->term_id;
        
    }
    
    if (!is_null($metaList) && count($metaList) > 0 && $metaList != '' && isset($_GET['tag_ID'])) { ?>
        
        <h3 class='hndle'><span><?php _e('Term meta', 'wp-category-meta');?></span></h3>
        
        <div class="inside">
            
            <input value="wpaft_edit" type="hidden" name="wpaft_edit" /> 
            <input type="hidden" name="image_field" id="image_field" value="" />
            <table class="form-table">
            
            <?php
            
            foreach ($metaList as $inputName => $inputData) {
                
                $inputType = '';
                $inputTaxonomy = 'category';
                
                if (is_array($inputData)) {
                    
                    $inputType = $inputData['type'];
                    $inputTaxonomy = $inputData['taxonomy'];
                    
                } else {
                    
                    $inputType = $inputData;
                    
                }
                
               
                if ($wp_version < '3.0' || $inputTaxonomy == $taxonomy) {
                    
                    $inputValue = htmlspecialchars(stripcslashes(wp_get_terms_meta($category_id, $inputName, true)));
                    
                    if ($inputType == 'text') { ?>
                        
                    	<tr class="form-field">
                    		<th scope="row" valign="top">
                                <label for="category_nicename"><?php echo $inputName;?></label>
                            </th>
                    		<td>
                                <input value="<?php echo $inputValue ?>" type="text" size="40" name="<?php echo 'wpaft_'.$inputName;?>" /><br />
                    			
                            </td>
                    	</tr>
                        
                	<?php } elseif ($inputType == 'textarea') { ?>
                        
                    	<tr class="form-field">
                    		<th scope="row" valign="top">
                                <label for="category_nicename"><?php echo $inputName;?></label>
                            </th>
                    		<td>
                                <textarea name="<?php echo "wpaft_".$inputName?>" rows="5" cols="50" class="large-text"><?php echo $inputValue ?></textarea><br />
                               
                            </td>
                    	</tr>
                    
                	<?php } elseif ($inputType == 'editor') { ?>
                        
                        <? $inputValue = wp_get_terms_meta($category_id, $inputName, true); ?>
                        
                    	<tr>
                    		<th scope="row" valign="top">
                                <label for="category_nicename"><?php echo $inputName;?></label>
                            </th>
                    		<td>
                                <?php wp_editor($inputValue,"wpaft_".str_replace(' ','_',$inputName),array('textarea_name'=>"wpaft_".str_replace(' ','_',$inputName))); ?>
                               
                            </td>
                    	</tr>
                    
                	<?php } elseif ($inputType == 'image') { ?>
                        
                        <?php $current_image_url = wp_get_terms_meta($category_id, $inputName, true); ?>
                        
                    	<tr class="form-field">
                    		<th scope="row" valign="top">
                                <label for="<?php echo "wpaft_".str_replace(' ','_',$inputName);?>" class="wpaft_meta_name_label"><?php echo $inputName;?></label>
                            </th>
                    		<td>
                                <div id="<?php echo "wpaft_".str_replace(' ','_',$inputName);?>_selected_image" class="wpaft_selected_image">
                                    <?php if ($current_image_url != '') echo '<img src="'.$current_image_url.'" style="max-width:100%;"/>';?>
                                </div>
                                <input type="text" name="<?php echo "wpaft_".str_replace(' ','_',$inputName);?>" id="<?php echo "wpaft_".str_replace(' ','_',$inputName);?>" value="<?php echo $current_image_url;?>" /><br />
                                <br />
                        		<img src="images/media-button-image.gif" alt="Add photos from your media" /> 
                                <a href="media-upload.php?type=image&#038;wpaft_send_label=<?php echo str_replace(' ','_',$inputName); ?>&#038;TB_iframe=1&#038;tab=library&#038;height=500&#038;width=640" onclick="image_photo_url_add('<?php echo "wpaft_".str_replace(' ','_',$inputName);?>')" class="thickbox" title="Add an Image"> 
                                    <strong>
                                        <?php echo _e('Click here to add/change your image', 'wp-texonomy-meta');?>
                                    </strong>
                        		</a><br />
                        	
                            </td>
                        </tr>
                    
                	<?php } elseif ($inputType == 'checkbox') { ?>
                    
                        <tr class="form-field">
                            <th scope="row" valign="top">
                                <label for="category_nicename"><?php echo $inputName;?></label>
                            </th>
                            <td>
                                <input value="checked" type="checkbox" <?php echo $inputValue ? 'checked="checked" ' : ''; ?> name="<?php echo 'wpaft_'.$inputName;?>" /><br />
                               
                            </td>
                        </tr>
                        
                	<?php } // end ELSEIF
                    
                }//end FOREACH
                
            }//end IF ?>
                
                
            </table>
            <textarea id="content_temp" name="content_temp" rows="100" cols="10" tabindex="2" onfocus="image_url_add()" style="width: 1px; height: 1px; padding: 0px; border: none;display :   none;"></textarea>
            <script type="text/javascript">edCanvas_temp = document.getElementById('content_temp');enable=false;</script>
        
        </div>
        
    <?php }// end IF ?>
    
<?php
}

?>