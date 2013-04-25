<?php
/**
 * ProGo Themes' Customer Testimonials plugin Random Widget Class
 *
 * Creates a "Testimonials : Random" widget to pull in a Testimonial at random
 *
 * @since 1.0
 *
 * @package ProGo
 * @subpackage Testimonials
 */

class ProGo_Testimonials_Random extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */
	function ProGo_Testimonials_Random() {
		$this->prefix = 'testimonials';
		$this->textdomain = 'testimonials';

		$widget_ops = array( 'classname' => 'testimonials', 'description' => __( 'Testimonials, chosen at random.', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-random", __( 'Testimonials : Random', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Testimonials') : $instance['title'], $instance, $this->id_base);
		$num = absint($instance['number']);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			
		//echo "<p>random $num testimonial here...</p>";
		$args = array('post_type' => 'progo_testimonials', 'numberposts' => $num, 'orderby' => 'rand' );
		$testimonials = get_posts($args);
		foreach($testimonials as $t) {
			$auth = get_post_meta($t->ID,'_progo_testimonials',true);
			echo '<div class="quote"><span class="lq">&ldquo;</span>'. wp_kses(nl2br($t->post_content),array('br'=>array(),'em'=>array(),'strong'=>array())) .'&rdquo;<br /><br />';
			echo '<div class="by">'. wp_kses($auth[auth],array()) .'<br />'. wp_kses($auth[loc],array()) .'</div></div>';
		}
		
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0
	 */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 1;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Testimonials:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}

?>