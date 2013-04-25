<?php
/**
 * ProGo Themes' Testimonials plugin Testimonials Widget Class
 *
 * Creates a "Testimonials : Testimonials" widget to pull in a number of Testimonials, in whatever order is given
 *
 * @since 1.0
 *
 * @package ProGo
 * @subpackage Testimonials
 */

class ProGo_Testimonials_Testimonials extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */
	function ProGo_Testimonials_Testimonials() {
		$this->prefix = 'testimonials';
		$this->textdomain = 'testimonials';

		$widget_ops = array( 'classname' => 'testimonials', 'description' => __( 'Some number of Testimonials', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-testimonials", __( 'Testimonials : Testimonials', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Testimonials') : $instance['title'], $instance, $this->id_base);
		$autoscroll = $instance['autoscroll'] == 'yes' ? 'yes' : 'no';
		$num = absint($instance['number']);
		if ( $num == 0 ) {
			$num = -1; // actually need -1 as arg to list ALL
		}
		
		$order = $instance['number'];
		if ( !in_array( $order, array( 'date', 'menu_order', 'title', 'ID', 'rand' ) ) ) {
			$order = 'menu_order';
		}
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			
		//echo "<p>random $num testimonial here...</p>";
		$args = array('post_type' => 'progo_testimonials', 'numberposts' => $num, 'orderby' => $order );
		$testimonials = get_posts($args);
		foreach($testimonials as $t) {
			$auth = get_post_meta($t->ID,'_progo_testimonials',true);
			echo '<div class="quote"><span class="lq">&ldquo;</span>'. wp_kses(nl2br($t->post_content),array('br'=>array(),'em'=>array(),'strong'=>array())) .'&rdquo;<br /><br />';
			echo '<div class="by">'. wp_kses($auth[auth],array()) .'<br />'. wp_kses($auth[loc],array()) .'</div></div>';
		}
		if($autoscroll == 'yes') { ?>
<script type="text/javascript">
function autoscroll<?php echo str_replace('-','',$this->id); ?>() {
	jQuery('.<?php echo $this->id; ?> .inside').autoscroll({direction: 'down',step: 30})
}
jQuery(function() {
	jQuery('.<?php echo $this->id; ?> .inside').scrollTop(0);
	setTimeout(autoscroll<?php echo str_replace('-','',$this->id); ?>,5000);
});
</script>
<?php }
		
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
		$instance['autoscroll'] = $new_instance['autoscroll'] == 'yes' ? 'yes' : 'no';
		
		$order = strip_tags($new_instance['order']);
		if ( !in_array( $order, array( 'date', 'menu_order', 'title', 'ID', 'rand' ) ) ) {
			$order = 'menu_order';
		}
		$instance['order'] = $order;
		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number' => 1, 'order' => 'menu_order', 'autoscroll' => 'no') );
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 1;
		$order = isset($instance['order']) ? esc_attr($instance['order']) : 'menu_order';
		$autoscroll = $instance['autoscroll'] == 'yes' ? 'yes' : 'no';
		
		$orders = array(
			'menu_order' => 'Menu Order',
			'rand' => 'Random',
			'date' => 'Post Date',
			'title' => 'Title',
			'ID' => 'Post ID'
		);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Testimonials:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /><br /><em>enter 0 to list all Testimonials</em></p>

		<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order:'); ?></label>
		<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>"><?php
        foreach ( $orders as $k => $v ) {
			echo '<option value="'. $k .'"';
			if ( $k == $order ) {
				echo ' selected="selected"';
			}
			echo '>'. $v .'</option>';
		}
        ?></select>
        </p>
        <p><input class="checkbox" type="checkbox" <?php checked($instance['autoscroll'], 'yes') ?> id="<?php echo $this->get_field_id('autoscroll'); ?>" name="<?php echo $this->get_field_name('autoscroll'); ?>" value="yes" /> <label for="<?php echo $this->get_field_id('autoscroll'); ?>"><?php _e('Autoscroll?'); ?></label></p>
<?php
	}
}

?>