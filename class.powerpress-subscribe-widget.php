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
.pp-subscribe-sidebar {
	width: auto;
}
<?php /* ?>
.pp-subscribe-sidebar a {
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
.pp-subscribe-sidebar a:hover {
	text-decoration: none;
	background-image:-moz-linear-gradient(0% 100% 90deg,#6F9C1B,#659417);
	background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#659417),to(#6F9C1B));
}
.pp-subscribe-sidebar .count {
	color: #FFF;
	display: block;
	font-size: 15px;
	line-height: 16px;
	padding: 0 13px;
	white-space: nowrap;
}
<?php */ ?>
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
		$instance['subscribe_type'] = strip_tags( $new_instance['subscribe_type'] ); // general, channel, category, post_type, ttid
		$instance['subscribe_post_type'] = strip_tags( $new_instance['subscribe_post_type'] );; // eg sermons
		$instance['subscribe_feed_slug'] = strip_tags( $new_instance['subscribe_feed_slug'] );; // e.g. podcast
		$instance['subscribe_category_id'] = strip_tags( $new_instance['subscribe_category_id'] );; // e.g. 456
		//$instance['subscribe_term_taxonomy_id'] = strip_tags( $new_instance['subscribe_term_taxonomy_id'] );; // e.g. 345
		return $instance;
	}

	function widget( $args, $instance ) {

		$ExtraData = array('type'=>'general', 'feed'=>'', 'taxonomy_term_id'=>'', 'cat_id'=>'', 'post_type'=>'');
		if( !empty($instance['subscribe_type']) )
			$ExtraData['type'] = $instance['subscribe_type'];
			
		switch( $instance['subscribe_type'] )
		{
			case 'post_type': {
				if( empty($instance['subscribe_post_type']) )
					return;
				$ExtraData['post_type'] = $instance['subscribe_post_type'];
			}; 
			case 'channel': {
				if( empty($instance['subscribe_feed_slug']) )
					return;
				$ExtraData['feed'] = $instance['subscribe_feed_slug'];
			}; break;
			case 'ttid': {
				if( empty($instance['subscribe_term_taxonomy_id']) || !is_numeric($instance['subscribe_term_taxonomy_id']) )
					return;
				$ExtraData['taxonomy_term_id'] = $instance['subscribe_term_taxonomy_id'];
			}; break;
			case 'category': {
				if( empty($instance['subscribe_category_id']) || !is_numeric($instance['subscribe_category_id']) )
					return;
				$ExtraData['cat_id'] = $instance['subscribe_category_id'];
			}; break;
			default: {
				// Doesn't matter, we'r using the default podcast channel 

			};
		}
		
		$Settings = powerpresssubscribe_get_settings(  $ExtraData );
		if( empty($Settings) )
			return;

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
?>
	<div class="pp-subscribe-sidebar">
	<ul>
		<li>
	<!-- include ?mt=2 when linked to itunes -->
	<a href="<?php echo $Settings['itunes_url'] ?>" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets/en_us/images/web/linkmaker/badge_subscribe-lrg.png) no-repeat;width:135px; height:40px;@media only screen{background-image:url(https://linkmaker.itunes.apple.com/htmlResources/assets/en_us//images/web/linkmaker/badge_subscribe-lrg.svg);}"></a>
	</li>
	<li>
			<!-- <a href="<?php echo $Settings['rss_url'] ?>"><img src="" /></a>  -->
			<a href="<?php echo $Settings['rss_url'] ?>"><?php echo __('Subscribe via RSS', 'powerpress'); ?></a>
		</li>
	<!--	<a href="http://akismet.com" target="_blank" title=""><?php printf( _n( '<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>', '<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>', $count , 'akismet'), number_format_i18n( $count ) ); ?></a> -->
	<?php
		if( !empty($Settings['subscribe_page_url']) )
		{
			$label = (empty($Settings['subscribe_page_link_text'])?__('More Subscribe Options', 'powerpress'):$Settings['subscribe_page_link_text']);
	?>
	<li>
			<!-- <a href="<?php echo $Settings['subscribe_page_url'] ?>"><img src="" /></a>  -->
		<a href="<?php echo $Settings['subscribe_page_url'] ?>"><?php echo htmlspecialchars($label); ?></a>
	</li>
	<?php
		}
	?>
	</ul>
	</div>
<?php
		echo $args['after_widget'];
	}
}

function powerpress_subscribe_register_widget() {
	register_widget( 'PowerPressSubscribe_Widget' );
}

add_action( 'widgets_init', 'powerpress_subscribe_register_widget' );
