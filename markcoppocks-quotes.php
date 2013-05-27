<?php 
/* 
Plugin Name: Quotes Post Types
Plugin URI: http://markcoppock.com/
Version 0.1
Author: Mark Coppock
Author URI: http://markcoppock.com
Description: Quotations plugin
License: GPL2
*/

/* Content Types */

add_action('init', 'post_type_quotes');
register_activation_hook( __FILE__, 'activate_quote_type' );

function activate_quote_type() {
	post_type_quotes();
	$GLOBALS['wp_rewrite']->flush_rules();
}

function post_type_quotes() {
	
	register_post_type('quote', 
		array(
		'labels' => array(
			'name' => __( 'Quotes' ), 
			'singular_name' => __( 'Quote' ), 
			'add_new' => __( 'Add New' ),
			'add_new_item' => __( 'Add New Quote' ),
			'new_item' => __( 'New Quote' ),
			'edit_item' => __( 'Edit Quote: ' ), 
			'add_new_item' => __( 'Add New Quote' ), 
			'view_item' => __( 'View quote' ), 
			'search_items' => __( 'Search Quotes' ),
			'not_found' => __( 'No Quotes found' ),
			'not_found_in_trash' => __( 'No Quotes found in Trash' ),
			'menu_name' => __( 'Quotes' )
		),
		'description' => 'Quotation custom post type with additional and useful parameters.',
		'public' => true,
		'show_ui' => true,
		'register_meta_box_cb' => 'quote_meta_boxes',
		'capability_type' => 'post',
		'menu_icon' => plugins_url( '/inc/img/quote_menu.png', __FILE__ ),
		'hierarchical' => false,
		'rewrite' => false,
		'query_var' => true,
		'taxonomies' => array('post_tag'),
		'supports' => array('title', 'editor', 'excerpt', 'tags', 'comments', 'post_formats')
	));
}

/* Taxonomies for the Quote type */


add_action('init', 'create_quote_tax');
register_activation_hook( __FILE__, 'activate_quote_tax' );
 
function activate_quote_tax() {
	create_quote_tax();
	$GLOBALS['wp_rewrite']->flush_rules();
}

function create_quote_tax() {
	register_taxonomy('quotetype',
		'quote',
		array(
			'labels' => array('name' => __( 'Type of Quote'), 'singular_name' => __( 'Type' ) ), 
			'hierarchical' => true
		)
	);
	register_taxonomy('quoteauthor',
		'quote',
		array(
			'labels' => array('name' => __( 'Author / Speaker'), 'singular_name' => __( 'Author or Speaker' ) ), 
			'helps' => __( 'Enter author/speaker\'s name. Single names as appropriate (Jesus, Aristotle, etc.)' )
		)
	);
}

/* Custom Fields */

function quote_meta_boxes() {
	add_meta_box( 'quote_source_meta', __( 'Quote Source' ), 'quote_source_meta_box', 'quote', 'normal', 'high' );
}

function quote_source_meta_box() {
	global $post;
	$source = get_post_meta($post->ID, '_quote_source', true);
	if ( function_exists('wp_nonce_field') ) wp_nonce_field('quote_source_nonce', '_quote_source_nonce');
	?>
	<label for="_quote_source">Quote Source (URL)</label>
	<input type="text" name="_quote_source" style="width:90%;" value="<?php echo esc_html(stripslashes($source), 1); ?>" />
	<?php
}

add_action( 'save_post', 'save_quote_meta_data' );

function save_quote_meta_data( $post_id ) {
	// ignore autosaves
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
	
	// check capabilites
	if (isset($_POST['post_type']) && 'quote' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) ) return $post_id;
	
	if (isset($_POST['post_type']) && 'quote' == $_POST['post_type']) {
	    // check nonces
		check_admin_referer('quote_source_nonce', '_quote_source_nonce');
	}
	
	// save the custom fields
	if (empty($_POST['_quote_source'])) {
		// check original value
		$storedsource = get_post_meta($post_id, '_quote_source', true);
		// remove from database
		delete_post_meta($post_id, '_quote_source', $storedsource);
	} else {
		update_post_meta($post_id, '_quote_source', $_POST['_quote_source']);
	}
}


/* Custom Edit Columns */

add_filter("manage_edit-quote_columns", "quote_taxonomy_custom_columns");

function quote_taxonomy_custom_columns($defaults) {
	
	// stash these to place as last two columns
	$comments = $defaults['comments'];
	$date = $defaults['date'];	
	// remove these defaults 'til the end 
	unset($defaults['comments']);
	unset($defaults['date']);
	
	// don't need this at all
	unset($defaults['author']);
	
	// custom taxonomy columns
	$defaults['quotetype'] = __('Quote Type');
	$defaults['quoteauthor'] = __('Author/Speaker');
	
	$defaults['comments'] = $comments;
	$defaults['date'] = $date;
	
	return $defaults;
}