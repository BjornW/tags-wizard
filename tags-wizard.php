<?php
/*******************************************************************************
Plugin Name: Tags Wizard
Plugin URI: http://www.burobjorn.nl
Description: Tags Wizard adds metaboxes below Post & Page screen aiding in adding tags
Author: Bjorn Wijers <burobjorn at burobjorn dot nl>
Version: 1.1
Author URI: http://www.burobjorn.nl
*******************************************************************************/

/*  Copyright 2011 & 2012


Tags Wizard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Tags Wizard is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists('TagWizard')) {
  class TagWizard {

    /**
     * @var string The options string name for this plugin
    */
    var $options_name = 'twoptions';

    /**
     * @var string $localization_domain Domain used for localization
    */
    var $localization_domain = 'tw';

    /**
     * @var string $plugin_url The path to this plugin
    */
    var $plugin_url = '';

    /**
     * @var string $plugin_path The path to this plugin
    */
    var $plugin_path = '';

    /**
     * @var array $options Stores the options for this plugin
    */
    var $options = array();

    /**
     * PHP 4 Compatible Constructor
    */
    function TagWizard(){ $this->__construct(); }

    /**
     * PHP 5 Constructor
    */
    function __construct()
    {
      // language setup
      $locale = get_locale();
      $mo     = dirname(__FILE__) . '/languages/' . $this->localization_domain . '-' . $locale . '.mo';
      load_textdomain($this->localization_domain, $mo);

      // 'constants' setup
      $this->plugin_url  = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
      $this->plugin_path = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)).'/';

      // initialize the options
      //This is REQUIRED to initialize the options when the plugin is loaded!
      $this->get_options();

      // Wordpress actions
      add_action("admin_menu", array(&$this,"admin_menu_link"));
      add_action( 'add_meta_boxes', array( &$this, 'setup_metaboxes' ) );
      add_action('admin_head',array(&$this, 'admin_head_hook') );



    }
	function register_twsettings() {

  		register_setting( $this->option_name, 'tw_title' );
  		register_setting( $this->option_name, 'tw_description' );

	}
    function admin_head_hook()
	  {

		  wp_register_script('tags-wizard', $this->plugin_url . '/js/tag-wizard.js', array('jquery'), false);
		  //wp_localize_script('page-tagger','pageTaggerL10n',
			//	array(
			//		'tagsUsed' =>  __('Tags used on this page:'),
			//		'addTag' => esc_attr(__('Add new tag')),
			//	)
		  //);
      wp_print_scripts('tags-wizard');
    }




    /**
     * Retrieves the plugin options from the database.
     * @return array
    */
    function get_options()
    {
      // don't forget to set up the default options
      if ( ! $the_options = get_option( $this->options_name) ) {
        $the_options = array(
          'version'=>'1'
        );
        update_option($this->options_name, $the_options);
      }
      $this->options = $the_options;
    }

    /**
     * Saves the admin options to the database.
    */
    function save_admin_options()
    {
      return update_option($this->options_name, $this->options);
    }

    /**
     * @desc Adds the options subpanel
    */
    function admin_menu_link()
    {
      // If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
      // reflect the page filename (ie - options-general.php) of the page your plugin is under!
      add_options_page('Tags Wizard', 'Tags Wizard', 'manage_options', basename(__FILE__), array(&$this,'admin_options_page'));
      add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
	  add_action( 'admin_init', array(&$this, 'register_twsettings') );

    }

    /**
     * @desc Adds the Settings link to the plugin activate/deactivate page
    */
    function filter_plugin_actions($links, $file)
    {
      // If your plugin is under a different top-level menu than
      // Settiongs (IE - you changed the function above to something other than add_options_page)
      // Then you're going to want to change options-general.php below to the name of your top-level page
      $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
      array_unshift( $links, $settings_link ); // before other links
      return $links;
    }

    /**
    * Adds settings/options page
    */
    function admin_options_page()
    {

	    if($_POST['tw_save']) {
	    	/*$title = $this->options['title'];
	    	$descr = $this->options['description'];
	   		$tags = $this->options['tags'];
	    	$postPage = $this->options['postPage'];
	    	*/

	    	$title = array();
	    	$descr = array();
	    	$tags = array();
	    	$postPage = array();


	    	$aantalWizards = ($_POST["aantalWizards"]=="" ? 0 : $_POST["aantalWizards"]);

	    	for($i=0; $i<$aantalWizards; $i++) {
	    		if(($_POST['tw_title' . $i]!="") && ($_POST['tw_tags' . $i]!="")) {
	    			$title[] = $_POST['tw_title' . $i];
	    			$descr[] = $_POST['tw_description' . $i];
		    		$tags[] = $_POST['tw_tags'. $i];
		    		$postPage[] = implode(',', $_POST['tw_post_page' . $i]);
		    	}
	    	}

	    	if($_POST['tw_title']!="" && $_POST['tw_tags']!="") {

		    	$title[] = $_POST['tw_title'];
		    	$descr[] = $_POST['tw_description'];
		    	$tags[] = $_POST['tw_tags'];
		    	$postPage[] = implode(',',$_POST['tw_post_page']);
	    	}


		 	$this->options = array(
          	'title'=>$title,
          	'description'=>$descr,
          	'tags'=>$tags,
          	'postPage' => $postPage
        	);

        delete_option($this->options_name);

        add_option($this->options_name, $this->options);

		}



      // build the options page
      $html .= "<div class=\"wrap\">\n";
      $html .= "<h2>Tags Wizard</h2>\n";
      $html .= "<p>" . __('Easily create lists of tags', $this->localization_domain) . "</p>\n";
	  $html .= "<p>To add or edit a Tag Wizard, use the button 'Add or update Tag Wizard Metaboxes'</p>";
	  $html .= "<p>Delete the title and the tags of an existing Tag Wizard to remove that Tag Wizard<p>";
	//Show the created options

	  $html .= "<table cellpadding=5 cellspacing=5 border=10>";

	  $html .= "</table>";



      $html .= "<form method=\"post\" id=\"tw_options\">";

      //$html .= wp_nonce_field('tw-update-options', $name = 'tw_wpnonce', $referer = true, $echo = false);
      $html .= "<table width=\"100%\" cellspacing=\"2\" cellpadding=\"5\" class=\"form-table\">\n";

	//begin new tag
	  $html .= "<tr><td colspan=2><h2>Add a new Tags Wizard Metabox</h2></td></tr>";
      $html .= "<tr valign=\"top\">\n";
      $html .= "\t<th width=\"33%\" scope=\"row\">" .  __('Title:', $this->localization_domain) . "</th>\n";
      $html .= "\t<td><input name=\"tw_title\" type=\"text\" id=\"tw_title\" size=\"45\" value=\"\" /></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr valign=\"top\">\n";
      $html .= "\t<th width=\"33%\" scope=\"row\">" .  __('Description:', $this->localization_domain) . "</th>\n";
      $html .= "\t<td><input name=\"tw_description\" type=\"text\" id=\"tw_description\" size=\"45\" value=\"\" /></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr valign=\"top\">\n";
      $html .= "\t<th width=\"33%\" scope=\"row\">" . __('Tags:', $this->localization_domain) . "</th>\n";
      $html .= "\t<td><textarea rows='2' cols='40' name='tw_tags' id='tw_tags'></textarea></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr valign=\"top\">\n";
      $html .= "\t<th width=\"33%\" scope=\"row\">" . __('Context:', $this->localization_domain) . "</th>\n";
      $html .= "\t<td>";
      $html .= "<label>Post</label><input type='checkbox' value='post' name='tw_post_page[]' checked/>";
      $html .= "<label>Page</label><input type='checkbox' value='page' name='tw_post_page[]' />";
      $html .="</td>\n";
      $html .= "</tr>\n";

      /*$html .= "<tr valign=\"top\">\n";
      $html .= "\t<th width=\"33%\" scope=\"row\">" . __('Placement:', $this->localization_domain) . "</th>\n";
      $html .= "\t<td>";
      $html .= "<label>side</label><input type='radio' value='side' />";
      $html .= "<label>normal</label><input type='radio' value='normal' />";
      $html .= "<label>advanced</label><input type='radio' value='advanced' />";
      $html .="</td>\n";
      $html .= "</tr>\n";*/

	//start created tags
	 for($i=0; $i<count($this->options['title']); $i++) {

	 	  $html .= "<tr><td colspan=2><h2>Tags Wizard Metabox " . ($i+1) . "</h2></td></tr>";
		  $html .= "<tr valign=\"top\">\n";
	      $html .= "\t<th width=\"33%\" scope=\"row\">" .  __('Title:', $this->localization_domain) . "</th>\n";
	      $html .= "\t<td><input name=\"tw_title$i\" type=\"text\" id=\"tw_title$i\" size=\"45\" value=\"" . $this->options['title'][$i] . "\" /></td>\n";
	      $html .= "</tr>\n";

	      $html .= "<tr valign=\"top\">\n";
	      $html .= "\t<th width=\"33%\" scope=\"row\">" .  __('Description:', $this->localization_domain) . "</th>\n";
	      $html .= "\t<td><input name=\"tw_description$i\" type=\"text\" id=\"tw_description$i\" size=\"45\" value=\"". $this->options['description'][$i] . "\" /></td>\n";
	      $html .= "</tr>\n";

	      $html .= "<tr valign=\"top\">\n";
	      $html .= "\t<th width=\"33%\" scope=\"row\">" . __('Tags:', $this->localization_domain) . "</th>\n";
	      $html .= "\t<td><textarea rows='2' cols='40' name='tw_tags$i' id='tw_tags$i'>" . $this->options['tags'][$i] . "</textarea></td>\n";
	      $html .= "</tr>\n";

	      $html .= "<tr valign=\"top\">\n";
	      $html .= "\t<th width=\"33%\" scope=\"row\">" . __('Context:', $this->localization_domain) . "</th>\n";
	      $html .= "\t<td>";
	      $html .= "<label>Post</label><input type='checkbox' value='post' name='tw_post_page" . $i . "[]' " . (strrpos($this->options['postPage'][$i], 'post')===false ? "" : "checked=checked") . " />";
	      $html .= "<label>Page</label><input type='checkbox' value='page' name='tw_post_page" . $i . "[]' " . (strrpos($this->options['postPage'][$i], 'page')===false ? "" : "checked=checked") . " />";
	      $html .="</td>\n";
	      $html .= "</tr>\n";


	  }
	  $html .= "<tr><td colspan=2><input type='hidden' name='aantalWizards' value='$i'></td></tr>";

	//end created tags


      $html .= "<tr>\n";
      $html .= "\t<th colspan=2><input type=\"submit\" id=\"tw_save\" name=\"tw_save\" value=\"" . __('Add or Update de Tags Wizard Metaboxes', $this->localization_domain) . "\"/></th>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
      $html .= "</form>\n";
      // show the built page
      echo $html;
    }





    /**
     * Upon initialization add a metabox to the WordPress admin post interface
     * this allows a user to add their post to one or more streams
     *
     * @access public
     * @return void
     */
    function setup_metaboxes()
    {
     switch_to_blog(1);
     //take all the tag wizards from the cockpit

     $options_cockpit = get_option($this->options_name);
      $max_cockpit = count($options_cockpit['title']);

     for($i=0; $i<$max_cockpit; $i++) {
     	$displays = explode(',', $options_cockpit['postPage'][$i]);
     	for($j=0; $j<count($displays); $j++) {
	     	$tw_boxes[] = array(
	     		'title'         => $options_cockpit['title'][$i],
		        'id'            => 'tw' . $i,
		        'page'          => $displays[$j],
		        'context'       => 'side',
		        'priority'      => 'core',
		        'callback_args' => array(
		          'description' => $options_cockpit['description'][$i],
		          'options'     => explode(',', $options_cockpit['tags'][$i])
		        )

     		);
     	}



     }

     restore_current_blog();


      $options = get_option($this->options_name);
      $max = count($options['title']);

     for($i=0; $i<$max; $i++) {
     	$displays = explode(',', $options['postPage'][$i]);

     	for($j=0; $j<count($displays); $j++) {
	     	$tw_boxes[] = array(
	     		'title'         => $options['title'][$i],
		        'id'            => 'tw' . $i,
		        'page'          => $displays[$j],
		        'context'       => 'side',
		        'priority'      => 'core',
		        'callback_args' => array(
		          'description' => $options['description'][$i],
		          'options'     => explode(',', $options['tags'][$i])
		        )

     		);
     	}



     }


      if( is_array($tw_boxes) && sizeof($tw_boxes) > 0 ) {
        foreach($tw_boxes as $box) {
          if( is_array($box) ) {
            extract($box);
            add_meta_box(
              $id,
              $title,
              $callback = array(&$this, 'render_metabox_gui'),
              $page,
              $context,
              $priority,
              $callback_args
            );
          }
        }
      }
    }

    /**
     * Renders (echo) a metabox for add_meta_box
     *
     * @access public
     * @return string html
     */
    function render_metabox_gui( $post, $metabox )
    {
      if( is_array($metabox) && array_key_exists('args', $metabox) ) {
        $params = $metabox['args'];

        // initialize vars
        $description = null;
        $options     = null;

        // overwrite existing vars with values
        extract($params, EXTR_IF_EXISTS);

        // create html
        $html  = "";
        $html .= '<p class="description">';
        $html .= esc_html($description);
        $html .= '</p>';

        if( is_array($options) && sizeof($options) > 0 ) {
          //$html .= "<ul>\n";
          foreach($options as $option) {
            $html .= '<a href="#" class="tagwizard">' .esc_html($option) . '</a> ';
          }
          //$html .= "</ul>\n";
        }

      }
      echo $html;
    }




  }
} else {
  error_log('Class TagWizard already exists.');
}

// instantiate the class
if ( class_exists('TagWizard') ) {
  $tw_var = new TagWizard();
}
?>
