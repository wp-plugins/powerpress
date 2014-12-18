<?php
	// PowerPress Player administration
	
	
// Handle post processing here for the players page.
function powerpress_admin_players_init()
{
	//wp_enqueue_script('jquery');
	//echo "powerpres player init";
	
	
	wp_enqueue_style('mediaelement');
	wp_enqueue_style('wp-mediaelement');
	wp_enqueue_script('mediaelement');
	// wp_enqueue_script( 'wp-mediaelement' );
	//wp_enqueue_script( 'powerpress-mejs', powerpress_get_root_url() .'powerpress-mejs.js');
	
	$Settings = false; // Important, never remove this
	$Step = 1;
	
	$action = (isset($_GET['action'])?$_GET['action']: (isset($_POST['action'])?$_POST['action']:false) );
	//$type = (isset($_GET['type'])?$_GET['type']: (isset($_POST['type'])?$_POST['type']:'audio') );
	
	if( !$action )
		return;
		
	switch($action)
	{
		case 'powerpress-select-player': {
			
			$SaveSettings = array();
			//$SaveSettings = $_POST['Player'];
			if( isset($_POST['Player']) )
				$SaveSettings = $_POST['Player'];
			if( isset($_POST['VideoPlayer']) )
				$SaveSettings += $_POST['VideoPlayer'];
			if( isset($_POST['MobilePlayer']) )
				$SaveSettings += $_POST['MobilePlayer'];
			powerpress_save_settings($SaveSettings, 'powerpress_general');
			powerpress_page_message_add_notice( __('Player activated successfully.', 'powerpress') );
			
		}; break;
		case 'powerpress-audio-player': {
		
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_audio-player');
			powerpress_page_message_add_notice( __('Audio Player settings saved successfully.', 'powerpress') );
		
		}; break;
		case 'powerpress-flashmp3-maxi': {
			
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_flashmp3-maxi');
			powerpress_page_message_add_notice( __('Flash Mp3 Maxi settings saved successfully.', 'powerpress') );
			
		} ; break; 
		case 'powerpress-audioplay':
		{
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_audioplay');
			powerpress_page_message_add_notice( __('AudioPlay settings saved successfully.', 'powerpress') );
		}; break;
		//TODO: PowerPress 5.0
		//case 'powerpress-mediaelement':
		//{
		//	$SaveSettings = $_POST['Player'];
		//	powerpress_save_settings($SaveSettings, 'powerpress_mediaelement');
		//	powerpress_page_message_add_notice( __('MediaElement.js settings saved successfully.', 'powerpress') );
		//}; break;
	}
}

// add_action('init', 'powerpress_admin_players_init');

function powerpress_admin_page_videoplayer_error()
{

}



?>