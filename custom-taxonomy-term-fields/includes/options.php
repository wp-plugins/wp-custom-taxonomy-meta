<?php

Class wpaft_admin {

    var $version = '1.0.0';

    function wpaft_admin() {

        // Load language file
        $locale = get_locale();
        if ( !empty($locale) )
        load_textdomain('wp-texonomy-meta', WPTM_ABSPATH.'lang/wp-texonomy-meta-'.$locale.'.mo');

        add_action('admin_head', array(&$this, 'wpaft_options_style'));
		add_action('admin_head', array(&$this, 'wpaft_options_script'));
        add_action('admin_menu', array(&$this, 'wpaft_add_options_panel'));

    }
	
	  //script options page
	 function wpaft_options_script() { 
	    ?>
		<script>
		jQuery(document).ready(function(){
			jQuery("#add_new_meta").click(function(){
			 var mataname = jQuery("#new_meta_name").val();
			 if(mataname ==''){
				jQuery("#new_meta_name").addClass( "required" ); 
				jQuery("#the_required").html( "<span style='color:red'>This is required field.</span>" ); 
				return false;
			 } 
			});
		});
		</script>
		<?php
	 }
    
    //styling options page
    function wpaft_options_style() {
        ?>
        <style type="text/css" media="screen">
            .titledesc {width:300px;}
            .thanks {width:400px; }
            .thanks p {padding-left:20px; padding-right:20px;}
            .info { background: #FFFFCC; border: 1px dotted #D8D2A9; padding: 10px; color: #333; }
            .info a { color: #333; text-decoration: none; border-bottom: 1px dotted #333 }
            .info a:hover { color: #666; border-bottom: 1px dotted #666; }
			.main-heading {
				font-size: 23px;
				font-weight: 400;
				line-height: 29px;
				padding: 9px 15px 4px 0;
				 font-style: italic;
			}
			.manage-column strong ,.title .manage-column { font-style: italic; color:#000000;}
						
			.widefat{border: 1px solid #0000FF; }
			
			.button-primary {
				background: #2ea2cc none repeat scroll 0 0;
				border-color: #0074a2;
				box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset, 0 1px 0 rgba(0, 0, 0, 0.15);
				color: #fff;
				text-decoration: none;
			}
			.no-mta td{ color:red; }
			
			.forminp select {
			padding:3px;
			width: 200px;
			margin: 0;
			-webkit-border-radius:4px;
			-moz-border-radius:4px;
			border-radius:4px;
			-webkit-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset;
			-moz-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset;
			box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset;
			background: #f8f8f8;
			color:#888;
			border:none;
			outline:none;
			display: inline-block;
			-webkit-appearance:none;
			-moz-appearance:none;
			appearance:none;
			cursor:pointer;
			}
			
			.required{
				border: 1px dotted #FF0000 !important;
				
				}
        </style>
    <?php
    }
    
    //Add configuration page into admin interface.
    function wpaft_add_options_panel() {
        add_options_page('Taxonomy Meta Options', 'Taxonomy Meta', 'manage_options', 'texonomy_meta', array(&$this, 'wpaft_option_page'));
    }
    
    //build admin interface
    function wpaft_option_page() 
    {   
        global $wp_version;
        $configuration = get_option("wpaft_configuration");
        if(is_null($configuration) || $configuration == '')
        {
            $configuration = array();
        }
        
        if(isset($_POST['action']) && $_POST['action'] == "add") 
        {
            $new_meta_name = $_POST["new_meta_name"];
            $new_meta_name_sanitize = $_POST["new_meta_name_sanitize"];
            // Sanitize the entered string to avoid special char problems
            if($new_meta_name_sanitize == 1)
            {
                $new_meta_name = sanitize_title($new_meta_name);
            }
            $new_meta_type = $_POST["new_meta_type"];
            $new_meta_taxonomy = $_POST["new_meta_taxonomy"];
            $configuration[$new_meta_name] = array('type' => $new_meta_type, 'taxonomy' => $new_meta_taxonomy);
            
            update_option("wpaft_configuration", $configuration);
            
        }
        else if(isset($_POST['action']) && $_POST['action'] == "delete") 
        {
            $delete_Meta_Name = $_POST["delete_Meta_Name"];
            unset($configuration[$delete_Meta_Name]);
            update_option("wpaft_configuration", $configuration);
        }
    ?>
        <div class="wrap">
            <h2 class="main-heading"><?php _e('Texonomy Meta', 'wp-texonomy-meta'); ?></h2>
            <form method="post">
                <table class="widefat">
                    <thead>
                        <tr class="title">
                            <th scope="col" class="manage-column"><strong><?php _e('Add new Meta', 'wp-texonomy-meta'); ?></strong></th>
                            <th scope="col" class="manage-column"></th>
                        </tr>
                    </thead>
                    <tr class="mainrow">        
                        <td class="titledesc"><?php _e('Meta Key *','wp-texonomy-meta'); ?>:</td>
                        <td class="forminp">
                            <input type="text" id="new_meta_name" name="new_meta_name" value="" />
							<div id="the_required" class="the_required"></div>
                        </td>
                    </tr>
                    <tr class="mainrow">        
                        <td class="titledesc"><?php _e('Sanitize meta name','wp-texonomy-meta'); ?>:</td>
                        <td class="forminp">
                            <input type="radio" id="new_meta_name_sanitize" name="new_meta_name_sanitize" value="1" checked="checked"" />Yes
							
                            <input type="radio" id="new_meta_name_sanitize" name="new_meta_name_sanitize" value="0" />No
							
                        </td>
                    </tr>
                    <tr class="mainrow">        
                        <td class="titledesc"><?php _e('Meta Type','wp-texonomy-meta'); ?>:</td>
                        <td class="forminp">
                            <select id="new_meta_type" name="new_meta_type">
                                <option value="text"><?php _e('Text','wp-texonomy-meta'); ?></option>
                                <option value="textarea"><?php _e('Text Area','wp-texonomy-meta'); ?></option>
                                <option value="editor"><?php _e('WYSIWYG Editor','wp-texonomy-meta'); ?></option>
                                <option value="image"><?php _e('Image','wp-texonomy-meta'); ?></option>
                                <option value="checkbox"><?php _e('Check Box','wp-texonomy-meta'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <?php if($wp_version >= '3.0') {?>
                    <tr class="mainrow">        
                        <td class="titledesc"><?php _e('Meta Taxonomy','wp-texonomy-meta'); ?>:</td>
                        <td class="forminp">
                            <select id="new_meta_taxonomy" name="new_meta_taxonomy">
                                <?php 
                                    $taxonomies=get_taxonomies('','names'); 
                                    foreach ($taxonomies as $taxonomy ) {
                                      echo '<option value="'.$taxonomy.'">'. $taxonomy. '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr class="mainrow">
                        <td class="titledesc">
                        <input type="hidden" name="action" value="add" />
                        </td>
                        <td class="forminp">
                        <input id="add_new_meta" class="button-primary" type="submit" value="<?php _e('Add Meta', 'wp-texonomy-meta') ?>" />
                        </td>
                    </tr>
                </table>
            </form>
			
			<br />
			<table class="widefat fixed">
                <thead>
                    <tr class="title">
                        <th scope="col" class="manage-column"><strong><?php _e('Meta list', 'wp-texonomy-meta'); ?></strong></th>
                        <th scope="col" class="manage-column"></th>
                        <?php if($wp_version >= '3.0') {?>
                        <th scope="col" class="manage-column"></th>
                        <?php } ?>
                        <th scope="col" class="manage-column"></th>
                    </tr>
                    <tr class="title">
                        <th scope="col" class="manage-column"><strong><?php _e('Meta Key', 'wp-texonomy-meta'); ?></strong></th>
                        <th scope="col" class="manage-column"><strong><?php _e('Meta Type', 'wp-texonomy-meta'); ?></strong></th>
                        <?php if($wp_version >= '3.0') {?>
                        <th scope="col" class="manage-column"><strong><?php _e('Meta Taxonomy', 'wp-texonomy-meta'); ?></strong></th>
                        <?php } ?>
                        <th scope="col" class="manage-column"><strong><?php _e('Action', 'wp-texonomy-meta'); ?></strong></th>
                    </tr>
                </thead>
                <?php 
                    foreach($configuration as $name => $data)
                    { 
                        $type = '';
                        $taxonomy = 'category';
                        if(is_array($data)) {
                            $type = $data['type'];
                            $taxonomy = $data['taxonomy'];
                        } else {
                            $type = $data;
                        }
                        ?>
                <tr class="mainrow">        
                    <td class="titledesc"><?php echo $name;?></td>
                    <td class="forminp">
                        <?php echo $type;?>
                    </td>
                    <?php if($wp_version >= '3.0') {?>
                    <td class="forminp">
                        <?php echo $taxonomy;?>
                    </td>
                    <?php } ?>
                    <td class="forminp">
                        <form method="post" id="confirmdelete"  onsubmit="return confirm('Are you sure you want to delete?')">
                        <input type="hidden" name="action" value="delete" />
                        <input type="hidden" name="delete_Meta_Name" value="<?php echo $name;?>" />
                        <input type="submit" id="c"  class="button-primary"  value="<?php _e('Delete', 'wp-texonomy-meta') ?>" />
                        </form>
                    </td>
                </tr>
                    <?php }
					
					if(count($configuration) < 1){
						echo '<tr class="no-mta"><td colspan="4">No meta added.</td></tr>';
						
					}
                ?>
            </table>
            <br/>
        </div>
    <?php 
    }
}
?>