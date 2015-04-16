<?php


class PostGalleryCustomPostType{

private $post_type = 'postgallery';
private $post_label = 'Post Gallery';
private $prefix = '_post_gallery_';
function __construct() {
	add_action("init", array(&$this,"create_post_type"));
	//add_action( 'init', array(&$this, 'post_gallery_register_shortcodes'));
	add_action( 'wp_footer', array(&$this, 'enqueue_styles'));
	//add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts'), 15);
	//add_action( 'wp_loaded', array( $this, 'onWpLoaded' ), 0 );
	add_action( 'cmb2_init', array(&$this,'postgallery_register_metabox' ));
	add_action( 'cmb2_init', array(&$this,'postgallery_register_repeatable_group_field_metabox' ));
	add_action('template_redirect', array(&$this, 'redirect_to_gallery_page'));
}

//head rewrite adopted from wordpress-bootstrap-css/tags/3.2.0-4/src/icwp-processor-css.php
function onWpLoaded(){
	 ob_start( array( $this, 'onOutputBufferFlush' ) );
}

public function onOutputBufferFlush( $sContent ) {
                return $this->rewriteHead( $sContent );
}
protected function rewriteHead( $sContents ) {
	$sIncludeLink = "";
	$sReplace = '${1}';
	$sReplace .= "\n".'<link rel="stylesheet" type="text/css" href="'.$sIncludeLink.'" />';
	$sRegExp = "/(<\bhead\b([^>]*)>)/i";
	return preg_replace( $sRegExp, $sReplace, $sContents, 1 );
}
//end head rewrite

function create_post_type(){
	register_post_type($this->post_type, array(
	         'label' => _x($this->post_label, $this->post_type.' label'), 
	         'singular_label' => _x('All '.$this->post_label, $this->post_type.' singular label'), 
	         'public' => true, // These will be public
	         'show_ui' => true, // Show the UI in admin panel
	         '_builtin' => false, // This is a custom post type, not a built in post type
	         '_edit_link' => 'post.php?post=%d',
	         'capability_type' => 'page',
	         'hierarchical' => false,
	         'rewrite' => array("slug" => $this->post_type), // This is for the permalinks
	         'query_var' => $this->post_type, // This goes to the WP_Query schema
	         //'supports' =>array('title', 'editor', 'custom-fields', 'revisions', 'excerpt'),
	         'supports' =>array('title', 'author'),
	         'add_new' => _x('Add New', 'Event')
	         ));
}

/**************************************************
**********************CMB2*************************
*/


/**
 * Hook in and add a demo metabox. Can only happen on the 'cmb2_init' hook.
 */

function postgallery_register_metabox() {

	// Start with an underscore to hide fields from custom fields list
	//$prefix = '_postgallery_demo_';

	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$cmb_demo = new_cmb2_box( array(
		'id'            => $this->prefix . 'metabox',
		'title'         => __( 'Header and Footer', 'cmb2' ),
		'object_types'  => array( $this->post_type, ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
	) );

	$cmb_demo->add_field( array(
		'name'       => __( 'Headline', 'cmb2' ),
		'desc'       => __( 'field description (optional)', 'cmb2' ),
		'id'         => $this->prefix . 'headline',
		'type'       => 'text',
	) );

	$cmb_demo->add_field( array(
		'name' => __( 'Header Content', 'cmb2' ),
		'desc' => __( 'Place the content you want to appear above the gallery here.', 'cmb2' ),
		'id'   => $this->prefix . 'header',
		'type' => 'wysiwyg',
		'options' => array(
	        	'textarea_rows' => get_option('default_post_edit_rows', 5),
		),
	) );
	
	$cmb_demo->add_field( array(
		'name' => __( 'Footer Content', 'cmb2' ),
		'desc' => __( 'Place the content you want to appear below the gallery here.', 'cmb2' ),
		'id'   => $this->prefix . 'footer',
		'type' => 'wysiwyg',
		'options' => array(
	        	'textarea_rows' => get_option('default_post_edit_rows', 5),
		),
	) );

}




/**
 * Hook in and add a metabox to demonstrate repeatable grouped fields
 */
function postgallery_register_repeatable_group_field_metabox() {

	// Start with an underscore to hide fields from custom fields list
	//$prefix = '_postgallery_group_';

	/**
	 * Repeatable Field Groups
	 */
	$cmb_group = new_cmb2_box( array(
		'id'           => $this->prefix . 'post_metabox',
		'title'        => __( 'Posts', 'cmb2' ),
		'object_types' => array( $this->post_type, ),
	) );

	// $group_field_id is the field id string, so in this case: $prefix . 'demo'
	$group_field_id = $cmb_group->add_field( array(
		'id'          => $this->prefix . 'post_group',
		'type'        => 'group',
		'description' => __( 'Generates reusable tabs', 'cmb2' ),
		'options'     => array(
			'group_title'   => __( 'Post {#}', 'cmb2' ), // {#} gets replaced by row number
			'add_button'    => __( 'Add Another Tab', 'cmb2' ),
			'remove_button' => __( 'Remove Tab', 'cmb2' ),
			'sortable'      => true, // beta
		),
	) );

	/**
	 * Group fields works the same, except ids only need
	 * to be unique to the group. Prefix is not needed.
	 *
	 * The parent field's id needs to be passed as the first argument.
	 */
	$cmb_group->add_group_field( $group_field_id, array(
		'name'       => __( 'Title', 'cmb2' ),
		'id'         => 'title',
		'type'       => 'text',
		//'default'    => ' ',
		// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );
	
	$cmb_group->add_group_field( $group_field_id, array(
		'name'       => __( 'Post', 'cmb2' ),
		'id'         => 'post',
		'type' => 'select',
		//'default' => '',
		'options' => array( $this, 'get_all_post_for_select' ),
		// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
	) );
	
	$cmb_group->add_group_field( $group_field_id, array(
		'name'    => __( 'Description Type', 'cmb2' ),
		'desc'    => __( 'Choose whether you want to use the post excerpt or a custom description.', 'cmb2' ),
		'id'      => 'description_type',
		'type'    => 'radio_inline',
		'options' => array(
			'excerpt' => __( 'Post excerpt', 'cmb2' ),
			'custom'   => __( 'Custom', 'cmb2' ),
			'none'     => __( 'None', 'cmb2' ),
		),
		'default' => 'excerpt',
	) );

	$cmb_group->add_group_field( $group_field_id, array(
		'name'        => __( 'Description', 'cmb2' ),
		'description' => __( 'Enter the description for this tab', 'cmb2' ),
		'id'          => 'description',
		'type'        => 'textarea_small',
	) );


}


/************************************************
*******************End CMB2**********************
*/



function post_gallery_shortcode($atts){
		extract( shortcode_atts( array(
			'id' => '',
		), $atts ) );
		$dir = plugin_dir_path( __FILE__ );
		//$tab2_message = get_post_meta($id, $this->prefix . 'tab2_message', true);
		
		ob_start();
		include $dir.'template/postGalleryTemplate.php';
		return ob_get_clean();
}

function get_all_post(){
	$args = array( 'post_type' => array( 'post', 'page' ), 'posts_per_page'   => -1, );
	return get_posts( $args );
}

public function get_all_post_for_select(){
		$all_post = array();
		$posts = $this->get_all_post();
		$data['empty'] = '';
		foreach($posts as $post) {
		    $data[$post->ID] = $post->post_title;
		}
		return $data;
	}
	
function redirect_to_gallery_page(){
	global $wp;
	global $post;
	if (isset($wp->query_vars["post_type"]) && $wp->query_vars["post_type"] == $this->post_type){
		$id = $post->ID;
		//add_action( 'get_header', array( $this, 'onWpLoaded' ), 0 );
		$headline = get_post_meta($id, $this->prefix . 'headline', true);
		$header = get_post_meta($id, $this->prefix . 'header', true);
		$footer = get_post_meta($id, $this->prefix . 'footer', true);
	         // Then use the campaign-template.php file from this plugin directory
	         $postGroup = get_post_meta($post->ID, $this->prefix . 'post_group', true);
	         $postIds = array();
	         
	       	 foreach ( (array) $postGroup as $key => $entry ) {      
	         	array_push($postIds, $entry['post']);
	         }
	         $listOfPost = get_posts(array('post__in' => $postIds, 'post_type' => array( 'post', 'page' ), 'posts_per_page'   => -1, 'orderby' => 'post__in'));
	        include plugin_dir_path( __FILE__ ).'template/postGalleryTemplate.php';
	         die();
	      }
	}

function post_gallery_register_shortcodes(){
		add_shortcode( 'post_gallery', array(&$this,'post_gallery_shortcode' ));
	}


function activate() {
	// register taxonomies/post types here
	$this->create_post_type();
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

function enqueue_styles(){
	wp_register_style( 'post-gallery-css', plugin_dir_url(__FILE__).'css/postGallery.css' );
	wp_enqueue_style('post-gallery-css');
}
function enqueue_scripts(){
	//wp_enqueue_script('post-gallery-js', plugin_dir_url(__FILE__).'js/postGallery.js');
}



}// end PostGalleryCustomPostType class

new PostGalleryCustomPostType();


?>