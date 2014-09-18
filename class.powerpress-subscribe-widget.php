<?php
/**
 * @package PowerPressSubscribe_Widget
 */
class PowerPressSubscribe_Widget extends WP_Widget {

	function __construct() {
		load_plugin_textdomain( 'powerpress' );
		
		parent::__construct(
			'powerpress_subscribe_widget',
			__( 'Subscribe to Podcast' , 'powerpress'),
			array( 'description' => __( 'Display subscribe to podcast links.' , 'powerpress') )
		);

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'css' ) );
		}
	}

	function css() {
?>

<style type="text/css">
.a-stats {
	width: auto;
}
.a-stats a {
	background: #7CA821;
	background-image:-moz-linear-gradient(0% 100% 90deg,#5F8E14,#7CA821);
	background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#7CA821),to(#5F8E14));
	border: 1px solid #5F8E14;
	border-radius:3px;
	color: #CFEA93;
	cursor: pointer;
	display: block;
	font-weight: normal;
	height: 100%;
	-moz-border-radius:3px;
	padding: 7px 0 8px;
	text-align: center;
	text-decoration: none;
	-webkit-border-radius:3px;
	width: 100%;
}
.a-stats a:hover {
	text-decoration: none;
	background-image:-moz-linear-gradient(0% 100% 90deg,#6F9C1B,#659417);
	background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#659417),to(#6F9C1B));
}
.a-stats .count {
	color: #FFF;
	display: block;
	font-size: 15px;
	line-height: 16px;
	padding: 0 13px;
	white-space: nowrap;
}
</style>

<?php
	}

	function form( $instance ) {
		if ( empty($instance['title']) ) {
			$instance['title'] = __( 'Subscribe to Podcast' , 'powerpress');
		}
		if ( empty($instance['subscribe_type']) ) {
			$instance['subscribe_type'] = '';
		}
		if ( empty($instance['subscribe_post_type']) ) {
			$instance['subscribe_post_type'] = '';
		}
		if ( empty($instance['subscribe_feed_slug']) ) {
			$instance['subscribe_feed_slug'] = '';
		}
		if ( empty($instance['subscribe_category_id']) ) {
			$instance['subscribe_category_id'] = '';
		}

?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:' , 'powerpress'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('subscribe_type'); ?>"><?php _e( 'Select Podcast Type:', 'powerpress' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('subscribe_type'); ?>" name="<?php echo $this->get_field_name('subscribe_type'); ?>">
		<?php
		$types = array(''=>__('Default Podcast','powerpress'), 'channel'=>__('Podcast Channel','powerpress'), 'category'=>__('Category Podcasting','powerpress'), 'post_type'=>__('Post Type Podcasting','powerpress') ); // , 'ttid'=>__('Taxonomy Podcasting','powerpress'));
		while( list($type, $label) = each($types) ) {
			echo '<option value="' . $type . '"'
				. selected( $instance['subscribe_type'], $type, false )
				. '>' . $label . "</option>\n";
		}
		?>
		</select>
		</p>
		
		<p id="<?php echo $this->get_field_id('subscribe_post_type_section'); ?>">
		<label for="<?php echo $this->get_field_id('subscribe_post_type'); ?>"><?php _e( 'Select Post Type:', 'powerpress' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('subscribe_post_type'); ?>" name="<?php echo $this->get_field_name('subscribe_post_type'); ?>">
		<option value=""><?php echo __('Select Post Type', 'powerpress'); ?></option>
<?php
		$post_types = powerpress_admin_get_post_types(false);
		while( list($index, $label) = each($post_types) ) {
			echo '<option value="' . $label . '"'
				. selected( $instance['subscribe_post_type'], $label, false )
				. '>' . $label . "</option>\n";
		}
?>
		</select>
		</p>
		
		
		<p id="<?php echo $this->get_field_id('subscribe_feed_slug_section'); ?>">
		<label for="<?php echo $this->get_field_id( 'subscribe_feed_slug' ); ?>"><?php esc_html_e( 'Feed Slug:' , 'powerpress'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'subscribe_feed_slug' ); ?>" name="<?php echo $this->get_field_name( 'subscribe_feed_slug' ); ?>" type="text" value="<?php echo esc_attr( $instance['subscribe_feed_slug'] ); ?>" />
		</p>
		
		<p id="<?php echo $this->get_field_id('subscribe_category_id_section'); ?>">
		<label for="<?php echo $this->get_field_id( 'subscribe_category_id' ); ?>"><?php esc_html_e( 'Category ID:' , 'powerpress'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'subscribe_category_id' ); ?>" name="<?php echo $this->get_field_name( 'subscribe_category_id' ); ?>" type="text" value="<?php echo esc_attr( $instance['subscribe_category_id'] ); ?>" />
		</p>
		
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		// $FeedAttribs = array('type'=>'general', 'feed_slug'=>'', 'category_id'=>0, 'term_taxonomy_id'=>0, 'post_type'=>'');
		$instance['subscribe_type'] = strip_tags( $new_instance['subscribe_type'] ); // general, channel, category, ttid, post_type
		$instance['subscribe_post_type'] = strip_tags( $new_instance['subscribe_post_type'] );; // eg channel
		$instance['subscribe_feed_slug'] = strip_tags( $new_instance['subscribe_feed_slug'] );; // e.g. podcast
		$instance['subscribe_category_id'] = strip_tags( $new_instance['subscribe_category_id'] );; // e.g. podcast
		return $instance;
	}

	function widget( $args, $instance ) {
		//$count = get_option( 'akismet_spam_count' );
		$count = 500;

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
?>

	<div class="a-stats">
		<a href="http://akismet.com" target="_blank" title=""><?php printf( _n( '<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>', '<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>', $count , 'akismet'), number_format_i18n( $count ) ); ?></a>
		<?php print_r($instance); ?>
	</div>

<?php
		echo $args['after_widget'];
	}
}

function powerpress_subscribe_register_widget() {
	register_widget( 'PowerPressSubscribe_Widget' );
}

add_action( 'widgets_init', 'powerpress_subscribe_register_widget' );
