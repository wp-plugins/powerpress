<?php
	// powerpress-playlist.php


function powerpress_playlist_episodes($args)
{
	global $wpdb;
	
	$defaults = array(
		'limit' => 10,
		'feed_slug' => 'podcast',
	);
	$args = wp_parse_args( $args, $defaults );
	
	$return = array();
	$query = "SELECT p.ID, p.post_title, p.post_date, pm.meta_value ";
	$query .= "FROM {$wpdb->posts} AS p ";
	$query .= "INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id ";
	
	if( $args['feed_slug'] == 'podcast' )
		$query .= "WHERE (pm.meta_key = 'enclosure') ";
	else
		$query .= "WHERE (pm.meta_key = '_".$args['feed_slug'].":enclosure') ";
	$query .= "AND p.post_type != 'revision' ";
	$query .= "GROUP BY p.ID ";
	$query .= "ORDER BY p.post_date DESC ";
	$query .= "LIMIT 0, ".$args['limit'];
	
	$return = array();
	$results_data = $wpdb->get_results($query, ARRAY_A);
	if( $results_data )
	{
		while( list($null,$row) = each($results_data) )
		{
			if( empty($row['meta_value']) )
				continue;
			
			$EnclosureData = powerpress_get_enclosure_data($row['ID'], $args['feed_slug'], $row['meta_value']);
			$return[ $row['ID'] ] = array();
			$return[ $row['ID'] ]['ID'] = $row['ID'];
			$return[ $row['ID'] ]['post_title'] = $row['post_title'];
			$return[ $row['ID'] ]['post_date'] = $row['post_date'];
			$return[ $row['ID'] ]['enclosure'] = $EnclosureData;
		}
	}
	return $return;
}

/**
 * Output the templates used by playlists.
 *
 */
function powerpress_underscore_playlist_templates() {
?>
<script type="text/html" id="tmpl-wp-playlist-current-item">
	<# if ( data.image ) { #>
	<img src="{{ data.thumb.src }}" />
	<# } #>
	<div class="wp-playlist-caption">
		<# if ( data.meta.artist && data.meta.link ) { #>
		<span class="wp-playlist-item-meta wp-playlist-item-title"><a href="{{ data.meta.link }}">{{ data.title }}</a></span>
		<# } else { #>
		<span class="wp-playlist-item-meta wp-playlist-item-title">{{ data.title }}</span>
		<# } #>
		<# if ( data.meta.album ) { #><span class="wp-playlist-item-meta wp-playlist-item-album">{{ data.meta.album }}</span><# } #>
		<# if ( data.meta.artist ) { #><span class="wp-playlist-item-meta wp-playlist-item-artist">{{ data.meta.artist }}</span><# } #>
	</div>
</script>
<script type="text/html" id="tmpl-wp-playlist-item">
	<div class="wp-playlist-item">
		<a class="wp-playlist-caption" href="{{ data.src }}">
			{{ data.index ? ( data.index + '. ' ) : '' }}
			<# if ( data.caption ) { #>
				{{ data.caption }}
			<# } else { #>
				<span class="wp-playlist-item-title">&#8220;{{{ data.title }}}&#8221;</span>
				<# if ( data.artists && data.meta.artist ) { #>
				<span class="wp-playlist-item-artist"> &mdash; {{ data.meta.artist }}</span>
				<# } #>
			<# } #>
		</a>
		<# if ( data.meta.length_formatted ) { #>
		<div class="wp-playlist-item-length">{{ data.meta.length_formatted }}</div>
		<# } #>
	</div>
</script>
<?php
}

/**
 * Output and enqueue default scripts and styles for playlists.
 *
 * @since 3.9.0
 *
 * @param string $type Type of playlist. Accepts 'audio' or 'video'.
 */
function powerpress_playlist_scripts( $type ) {
	wp_enqueue_style( 'wp-mediaelement' );
	wp_enqueue_script( 'wp-playlist' );
?>
<!--[if lt IE 9]><script>document.createElement('<?php echo esc_js( $type ) ?>');</script><![endif]-->
<?php
	add_action( 'wp_footer', 'powerpress_underscore_playlist_templates', 0 );
	add_action( 'admin_footer', 'powerpress_underscore_playlist_templates', 0 );
}
add_action( 'powerpress_playlist_scripts', 'powerpress_playlist_scripts' );

/**
 * The playlist shortcode.
 *
 * This implements the functionality of the playlist shortcode for displaying
 * a collection of WordPress audio or video files in a post.
 *
 * @since 3.9.0
 *
 * @param array $attr Playlist shortcode attributes.
 * @return string Playlist output. Empty string if the passed type is unsupported.
 */
function powerpress_playlist_shortcode( $attr ) {
	global $content_width;
	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) ) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	/**
	 * Filter the playlist output.
	 *
	 * Passing a non-empty value to the filter will short-circuit generation
	 * of the default playlist output, returning the passed value instead.
	 *
	 * @since 3.9.0
	 *
	 * @param string $output Playlist output. Default empty.
	 * @param array  $attr   An array of shortcode attributes.
	 */
	$output = apply_filters( 'post_playlist', '', $attr );
	if ( $output != '' ) {
		return $output;
	}

	/*
	 * We're trusting author input, so let's at least make sure it looks
	 * like a valid orderby statement.
	 */
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract( shortcode_atts( array(
		'type'		=> 'audio',
		'order'		=> 'DESC',
		'orderby'	=> 'date',
		'include'	=> '',
		'exclude'   => '',
		'style'		=> 'light', /* */
		'tracklist' => true,
		'tracknumbers' => true,
		'images'	=> true,
		'artists'	=> true,
		'feedslug'=>'podcast',
		'limit'=>10
	), $attr, 'powerpressplaylist' ) );
	$tracknumbers = false;
	$images = true;
	$artists = true; // Program title

	$args = array(
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => $type,
		'order' => $order,
		'orderby' => $orderby
	);

	if ( ! empty( $include ) ) {
		$args['include'] = $include;
		$_attachments = get_posts( $args );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $exclude ) ) {
		$args['post_parent'] = $id;
		$args['exclude'] = $exclude;
		$attachments = get_children( $args );
	} else {
		//$args['post_parent'] = $id;
		$attachments = get_children( $args );
	}
	
	$episodes = powerpress_playlist_episodes( $args );
	//return print_r($results, true);
	
	if ( empty( $episodes ) ) {
		return '';
	}

	if ( is_feed() ) {
		return '';
		/*
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id ) . "\n";
		}
		return $output;
		*/
	}

	$outer = 22; // default padding and border of wrapper

	$default_width = 640;
	$default_height = 360;

	$theme_width = empty( $content_width ) ? $default_width : ( $content_width - $outer );
	$theme_height = empty( $content_width ) ? $default_height : round( ( $default_height * $theme_width ) / $default_width );

	$data = compact( 'type' );

	// don't pass strings to JSON, will be truthy in JS
	foreach ( array( 'tracklist', 'tracknumbers', 'images', 'artists' ) as $key ) {
		$data[$key] = filter_var( $$key, FILTER_VALIDATE_BOOLEAN );
	}

	$tracks = array();
	foreach ( $episodes as $episode ) {
		//$url = wp_get_attachment_url( $attachment->ID );
		$url = $episode['enclosure']['url'];
		//$ftype = wp_check_filetype( $url, wp_get_mime_types() );
		$track = array(
			'src' => $url,
			'type' => $episode['enclosure']['type'],
			'title' => $episode['post_title'],
			'caption' => '',
			'description' => $episode['post_title']
		);

		$track['meta'] = array();
		
		$track['meta']['artist'] = 'Talent Name';
		$track['meta']['album'] = ('Podcast Title here');
		$track['meta']['genre'] = 'Artisto';
		$track['meta']['year'] = '2014';
		$track['meta']['length_formatted'] = $episode['enclosure']['duration'];
		
		$track['meta']['link'] = 'http://www.google.com/';
		
		//$meta = wp_get_attachment_metadata( $attachment->ID );
		$meta = false;
		if ( ! empty( $meta ) ) {

			foreach ( wp_get_attachment_id3_keys( $attachment ) as $key => $label ) {
				if ( ! empty( $meta[ $key ] ) ) {
					$track['meta'][ $key ] = $meta[ $key ];
				}
			}

			if ( 'video' === $type ) {
				if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
					$width = $meta['width'];
					$height = $meta['height'];
					$theme_height = round( ( $height * $theme_width ) / $width );
				} else {
					$width = $default_width;
					$height = $default_height;
				}

				$track['dimensions'] = array(
					'original' => compact( 'width', 'height' ),
					'resized' => array(
						'width' => $theme_width,
						'height' => $theme_height
					)
				);
			}
		}
		
		$images = false;
		if ( $images ) {
			$id = get_post_thumbnail_id( $attachment->ID );
			if ( ! empty( $id ) ) {
				list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'full' );
				$track['image'] = compact( 'src', 'width', 'height' );
				list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'thumbnail' );
				$track['thumb'] = compact( 'src', 'width', 'height' );
			} else {
				$src = wp_mime_type_icon( $attachment->ID );
				$width = 48;
				$height = 64;
				$track['image'] = compact( 'src', 'width', 'height' );
				$track['thumb'] = compact( 'src', 'width', 'height' );
			}
		}

		$tracks[] = $track;
	}
	$data['tracks'] = $tracks;

	$safe_type = esc_attr( $type );
	$safe_style = esc_attr( $style );

	ob_start();

	if ( 1 === $instance ) {
		/**
		 * Print and enqueue playlist scripts, styles, and JavaScript templates.
		 *
		 * @since 3.9.0
		 *
		 * @param string $type  Type of playlist. Possible values are 'audio' or 'video'.
		 * @param string $style The 'theme' for the playlist. Core provides 'light' and 'dark'.
		 */
		do_action( 'powerpress_playlist_scripts', $type, $style );
	} ?>
<div class="wp-playlist wp-<?php echo $safe_type ?>-playlist wp-playlist-<?php echo $safe_style ?>">
	<?php if ( 'audio' === $type ): ?>
	<div class="wp-playlist-current-item"></div>
	<?php endif ?>
	<<?php echo $safe_type ?> controls="controls" preload="none" width="<?php
		echo (int) $theme_width;
	?>"<?php if ( 'video' === $safe_type ):
		echo ' height="', (int) $theme_height, '"';
	endif; ?>></<?php echo $safe_type ?>>
	<div class="wp-playlist-next"></div>
	<div class="wp-playlist-prev"></div>
	<noscript>
	<ol><?php
	foreach ( $attachments as $att_id => $attachment ) {
		printf( '<li>%s</li>', wp_get_attachment_link( $att_id ) );
	}
	?></ol>
	</noscript>
	<script type="application/json"><?php echo json_encode( $data ) ?></script>
</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'podcastlist', 'powerpress_playlist_shortcode' );
add_shortcode( 'podcastplaylist', 'powerpress_playlist_shortcode' );
add_shortcode( 'powerpressplaylist', 'powerpress_playlist_shortcode' );
add_shortcode( 'powerpress_playlist', 'powerpress_playlist_shortcode' );
