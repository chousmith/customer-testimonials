<?php
/*
Plugin Name: ProGo Customer Testimonials 
Description: Showcase Testimonials praising your site/product(s), with easy (CPT) control of the content, Widgets to display Testimonials in sidebars, and Shortcodes.
Author: ProGo Themes / Alex Chousmith
Version: 1.1
Author URI: http://www.progo.com/
*/

function progo_testimonials_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__) .'/customer-testimonials.php' ) ) {
		$links[] = '<a href="#hi">'.__('Testimonials').'</a>';
	}

	return $links;
}
add_filter( 'plugin_action_links', 'progo_testimonials_action_links', 10, 2 );

function progo_testimonials_init() {	
	// add "Testimonials" Custom Post Type
	register_post_type( 'progo_testimonials',
		array(
			'labels' => array(
				'name' => 'Testimonials',
				'singular_name' => 'Testimonial',
				'add_new_item' => 'Add New Testimonial',
				'edit_item' => 'Edit Testimonial',
				'new_item' => 'New Testimonial',
				'view_item' => 'View Testimonial',
				'search_items' => 'Search Testimonials',
				'not_found' =>  'No testimonial found',
				'not_found_in_trash' => 'No testimonials found in Trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Testimonials'
			),
			'public' => true,
			'public_queryable' => true,
			'exclude_from_search' => true,
			'show_in_menu' => true,
			'menu_position' => 41,
			'hierarchical' => false,
			'supports' => array('title','editor','revisions','page-attributes'),
			'register_meta_box_cb' => 'progo_testimonials_metaboxes'
		)
	);
}
add_action( 'init', 'progo_testimonials_init' );

function progo_testimonials_metaboxes() {
	add_meta_box("progo_testimonials_metabox", "Author Info", "progo_testimonials_metabox", "progo_testimonials", "normal", "high");
}

function progo_testimonials_metabox() {
	global $post;
	$custom = get_post_meta($post->ID,'_progo_testimonials');
	$auth = $custom[0];
	$defaults = array(
		'auth' => 'John Q Smith',
		'loc' => ''
	);
	if ( $auth == '' ) {
		$auth = $defaults;
	} else {
		$auth = array_merge($defaults, $auth);
	}
	?>
    <table width="100%">
    <tr><td><label for="progo_testimonials_author[auth]">Name</label></td>
    <td><input type="text" name="progo_testimonials_author[auth]" value="<?php echo esc_attr($auth[auth]); ?>" size="60" maxlength="60" /></td></tr>
    <tr><td><label for="progo_testimonials_author[loc]">Location/Title</label></td>
    <td><input type="text" name="progo_testimonials_author[loc]" value="<?php echo esc_attr($auth[loc]); ?>" size="60" maxlength="60" /></td></tr>
    </table>
<?php
}

function progo_testimonials_save($post_id){
	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	return $post_id;
	
	
	// Check permissions
	if ( $_POST['post_type'] == 'progo_testimonials' ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) return $post_id;
	} else {
	//if ( !current_user_can( 'edit_post', $post_id ) )
	  return $post_id;
	}
	
	// OK, we're authenticated: we need to find and save the data
	$auth = $_POST['progo_testimonials_author'];
	// sanitize
	$fields = array( 'auth', 'loc' );
	foreach ( $fields as $field ) {
		$auth[$field] = wp_kses( substr( $auth[$field], 0, 60 ), array() );
	}
	
	update_post_meta($post_id, "_progo_testimonials", $auth);
	return $auth;
}
add_action('save_post', 'progo_testimonials_save');

function progo_testimonials_widgets() {
	$included_widgets = array( 'Random', 'Testimonials' );
	foreach ( $included_widgets as $wi ) {
		require_once( 'widget-'. strtolower($wi) .'.php' );
		register_widget( 'ProGo_Testimonials_'. $wi );
	}
}
add_action( 'widgets_init', 'progo_testimonials_widgets' );

// [testimonials num=1 order="menu"]
function progo_testimonials_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'num' => 1,
		'order' => 'menu_order'
	), $atts ) );
	
	// sanitize args
	$num = absint($num);
	if ( $num == 0 ) {
		$num = -1; // actually need -1 as arg to list ALL
	}
	if ( $order == 'menu' ) {
		$order = 'menu_order';
	} elseif ( $order == 'random' ) {
		$order = 'rand';
	}
	if ( !in_array( $order, array( 'date', 'menu_order', 'title', 'ID', 'rand' ) ) ) {
		$order = 'menu_order';
	}
	
	$oot = '';
	$args = array('post_type' => 'progo_testimonials', 'numberposts' => $num, 'orderby' => $order );
	$testimonials = get_posts($args);
	foreach($testimonials as $t) {
		$auth = get_post_meta($t->ID,'_progo_testimonials',true);
		$oot .= '<div class="quote"><span class="lq">&ldquo;</span>'. wp_kses(nl2br($t->post_content),array('br'=>array(),'em'=>array(),'strong'=>array())) .'&rdquo;<br /><br />';
		$oot .= '<div class="by">'. wp_kses($auth[auth],array()) .'<br />'. wp_kses($auth[loc],array()) .'</div></div>';
	}
	return $oot;
}
add_shortcode( 'testimonials', 'progo_testimonials_shortcode' );

function progo_testimonials_admin_styles() {
	wp_enqueue_style( 'progo_testimonials_admin', trailingslashit( plugins_url( '', __FILE__ ) ) . 'admin-style.css' );
}
add_action( 'admin_print_styles', 'progo_testimonials_admin_styles' );

function progo_testimonials_favorite($actions) {
	$actions['post-new.php?post_type=progo_testimonials'] = array( 'New Testimonial', 'edit_pages'	);
//	wp_die('<pre>'.print_r($actions,true).'</pre>');
	return $actions;
}
add_filter( 'favorite_actions', 'progo_testimonials_favorite' );

function progo_testimonials_default_body( $content ) {
	global $post_type;
	if ( $post_type == 'progo_testimonials' ) {
		$content = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation";
	}
	return $content;
}
add_filter( 'default_content', 'progo_testimonials_default_body' );

function progo_testimonials_columns($column){
	global $post;
	
	$custom = get_post_meta($post->ID,'_progo_testimonials');
	$auth = $custom[0];
	
	switch ($column) {
		case "quote":
			the_excerpt();
			break;
		case "auth":
			esc_html_e($auth[auth]);
			break;
		case "loc":
			esc_html_e($auth[loc]);
			break;
	}
}
add_action("manage_posts_custom_column",  "progo_testimonials_columns");

function progo_testimonials_edit_columns($columns){
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Title",
    "quote" => "Quote",
    "auth" => "Author",
    "loc" => "Location"
  );
 
  return $columns;
}
add_filter("manage_edit-progo_testimonials_columns", "progo_testimonials_edit_columns");

function progo_testimonials_scripts() {
	if ( !is_admin() ) {
		// to do : only enqueue scripts when the widget is on the page...
		wp_enqueue_script( 'jquery-timers', trailingslashit( plugins_url( '', __FILE__ ) ) . 'jquery.timers.js', array('jquery'), '1.2' );
		wp_enqueue_script( 'jquery-aautoscroll', trailingslashit( plugins_url( '', __FILE__ ) ) . 'jquery.aautoscroll.js', array('jquery', 'jquery-timers'), '2.1' );
	}
}
add_action('wp_print_scripts', 'progo_testimonials_scripts');