<?php
/*
Plugin Name: ProGo Customer Testimonials 
Plugin URI: http://www.progo.com/
Description: Showcase Testimonials praising your site/product(s), with easy (CPT) control of the content, Widgets to display Testimonials in sidebars, and Shortcodes.
Version: 1.2.3
Author: ProGo Themes
Author URI: http://www.progo.com/
*/

function progo_testimonials_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__) .'/customer-testimonials.php' ) ) {
		$links[] = '<a href="edit.php?post_type=progo_testimonials">'.__('Testimonials').'</a>';
	}

	return $links;
}
add_filter( 'plugin_action_links', 'progo_testimonials_action_links', 10, 2 );

function progo_testimonials_init() {	
	// add "Testimonials" Custom Post Type
	register_post_type( 'progo_testimonials',
		array(
			'labels' => array(
				'name' => 'Customer Testimonials',
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
			'register_meta_box_cb' => 'progo_testimonials_metaboxes',
			'taxonomies' => array('progo_testimonials_cats'),
		)
	);
	
	$labels = array(
		'name' => _x( 'Testimonial Categories', 'taxonomy general name' ),
		'singular_name' => _x( 'Testimonial Category', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Testimonial Categories' ),
		'all_items' => __( 'All Testimonial Categories' ),
		'parent_item' => __( 'Parent Category' ),
		'parent_item_colon' => __( 'Parent Category:' ),
		'edit_item' => __( 'Edit Testimonial Category' ), 
		'update_item' => __( 'Update Category' ),
		'add_new_item' => __( 'Add New Testimonial Category' ),
		'new_item_name' => __( 'New Testimonial Category Name' ),
		'menu_name' => __( 'Categories' ),
	); 	
	
	register_taxonomy('progo_testimonials_cats',array('progo_testimonials'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'testimonialcats' ),
	));
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
	$default_order = 'menu_order';
	$default_order = apply_filters( 'progo_testimonials_default_order', $default_order, $atts );
	
	extract( shortcode_atts( array(
		'num' => 1,
		'order' => $default_order,
		'cat' => false
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
		$order = 'menu_order'; // in case default filter sets order to something WP doesnt understand
	}
	
	$oot = '';
	$args = array('post_type' => 'progo_testimonials', 'numberposts' => $num, 'orderby' => $order );
	
	if ( $cat != false ) {
		if ( is_numeric($cat) ) {
			// look up by slug and conver to #
			$cat = progo_testimonials_term_id_to_slug(absint($cat));
		}
		$args['progo_testimonials_cats'] = $cat;
		//$oot .= '<pre style="display:none" title="scarg">'. print_r($args,true) .'</pre>';
	}
	
	$testimonials = get_posts($args);
	foreach($testimonials as $t) {
		$auth = get_post_meta($t->ID,'_progo_testimonials',true);
		
		$oot .= progo_testimonials_output( $t, $auth, 'shortcode' );
	}
	return $oot;
}
add_shortcode( 'testimonials', 'progo_testimonials_shortcode' );

function progo_testimonials_output( $testimonial, $author, $fromwhere ) {
	$oot = '';
	
	$start = '<div class="quote">';
	$start = apply_filters( 'progo_testimonials_open_tag', $start, $testimonial, $fromwhere );
	
	$prequote = '<span class="lq">&ldquo;</span>';
	$prequote = apply_filters( 'progo_testimonials_pre_quote', $prequote, $testimonial, $fromwhere );
	
	$quote = wp_kses(nl2br($testimonial->post_content),array('br'=>array(),'em'=>array(),'strong'=>array()));
	$quote = apply_filters( 'progo_testimonials_quote_body', $quote, $testimonial, $fromwhere );
	
	$postquote = '&rdquo;<br /><br />';
	$postquote = apply_filters( 'progo_testimonials_post_quote', $postquote, $testimonial, $fromwhere );
	
	$auth = '<div class="by">'. wp_kses($author['auth'],array()) .'<br />'. wp_kses($author['loc'],array()) .'</div>';
	$auth = apply_filters( 'progo_testimonials_byline', $auth, $testimonial, $author, $fromwhere );
	
	$close = '</div>';
	$close = apply_filters( 'progo_testimonials_close_tag', $close, $testimonial, $fromwhere );
	
	$oot .= $start . $prequote . $quote . $postquote . $auth . $close;
	
	return $oot;
}

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
//wp_die('<pre>'. print_r($columns,true) .'</pre>');
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Title",
    "quote" => "Quote",
    "auth" => "Author",
    "loc" => "Location",
	"taxonomy-progo_testimonials_cats" => "Categories",
	"date" => "Date",
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

function progo_testimonials_term_slug_to_id( $slug ) {
	// convert cat slug to cat ID
	$term = get_term_by( 'slug', $slug, 'progo_testimonials_cats' );
	//echo '<pre>'. print_r($term,true) .'</pre>';
	$id = $term->term_id;
	return $id;
}

function progo_testimonials_term_id_to_slug( $id ) {
	// convert cat slug to cat ID
	$term = get_term_by( 'id', $id, 'progo_testimonials_cats' );
	//echo '<pre>'. print_r($term,true) .'</pre>';
	$slug = $term->slug;
	return $slug;
}

/*
 * massive props to mikeschinkel for
 * http://wordpress.stackexchange.com/questions/578/adding-a-taxonomy-filter-to-admin-list-for-a-custom-post-type
 */
add_action('restrict_manage_posts','progo_testimonials_restrict_by_cat');
function progo_testimonials_restrict_by_cat() {
    global $typenow;
    global $wp_query;
    if ($typenow=='progo_testimonials') {
        $taxonomy = 'progo_testimonials_cats';
        $catstax = get_taxonomy($taxonomy);
		
		$selected = $wp_query->query['progo_testimonials_cats'];
		
		if ( !is_numeric($selected) ) {
			// convert cat slug to cat ID
			$selected = progo_testimonials_term_slug_to_id($selected);
		}
		
        wp_dropdown_categories(array(
            'show_option_all' =>  __("Show All {$catstax->labels->menu_name}"),
            'taxonomy'        =>  $taxonomy,
            'name'            =>  'progo_testimonials_cats',
            'orderby'         =>  'name',
            'selected'        =>  $selected,
            'hierarchical'    =>  true,
            'depth'           =>  3,
            'show_count'      =>  true, // Show # listings in parens
            'hide_empty'      =>  true, // Don't show businesses w/o listings
        ));
    }
}

add_filter('parse_query','progo_testimonials_testimonialcat_to_query_term');
function progo_testimonials_testimonialcat_to_query_term($query) {
    global $pagenow;
    $qv = &$query->query_vars;
	if ( $pagenow == 'edit.php' ) {
		//echo '<pre>'. print_r($qv,true) .'</pre>';
	}
    if ($pagenow=='edit.php' &&
            isset($qv['progo_testimonials_cats']) && is_numeric($qv['progo_testimonials_cats'])) {
        $qv['progo_testimonials_cats'] = progo_testimonials_term_id_to_slug($qv['progo_testimonials_cats']);
    }
}

/*
 * to do :
 * shortcode filter by category
 * clean up "Post published..." messages...
 * instructions on filters?
 * add ability to totally hide Categories if you don't want them?
 * OR , start with 1 default category?
 * and at least hide the filter dropdown on the Testimonials edit page, if there are no cats?
 */