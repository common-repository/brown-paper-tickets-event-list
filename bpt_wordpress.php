<?php
/*
Plugin Name: Brown Paper Tickets Wordpress Plugin
Plugin URI: https://github.com/BrownPaperTickets/bpt_wordpress_plugin
Description: A plugin making use of the BPT API to perform a variety of BPT functions.
Version: 0.7.3
Author: Brown Paper Tickets
Author URI: http://www.brownpapertickets.com
License: GPL2
*/
/*
	Copyright (c) 2013 Brown Paper Tickets

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

require('inc/bptPHP/bpt_php.php');

function show_event_calendar ( $dev_id, $client_id, $event_id ) {
	$bpt = new BptApi;

	$event_list = '<div class="bpt_event_calendar"></div>';
	$event_list .= '<script type="text/javascript">';
	$event_list .='	jQuery(document).ready(function() {
						jQuery(".bpt_event_calendar").eventCalendar({
							jsonData: bpt_events, // link to events json
							showDescription: true,'.
							#jsonDateFormat: "human",
							'startWeekOnMonday: false,
							openEventInNewWindow: true,
						});
					});
	var bpt_events = ';

	$events = $bpt->event_list_call($dev_id, $client_id, $event_id, true, false );

	foreach ( $events as $event ) {

		$all_events = array();

		foreach ( $event['dates'][0] as $date ) {

			$date_start = $date['date_start'];
			$time_start = $date['time_start'];

			$unix_time = $date_start.' '.$time_start;
			$unix_time = strtotime($unix_time);
			$unix_time = $unix_time * 1;

			#$formatted_date = strftime("%Y-%m-%d", $date_start);

			#$time_start = strtotime($date['time_start']);
			#$formatted_time_start = strftime("%I:%M:00", $time_start);

			$title = $event['event_title'];
			$short_description = $event['short_description'];
			$event_id = $event['event_id'];
			$date_id = $date['date_id'];

			$single_date = '{';
			$single_date .= '"date": "'.$unix_time.'000",';
			$single_date .= '"type": "event",';
			$single_date .= '"title": "'.$title.'",';
			$single_date .= '"description": "'.htmlspecialchars($short_description).'",';
			$single_date .= '"url": "http://www.brownpapertickets.com/event/'.$event_id.'?date='.$date_id.'"';
			$single_date .= '}';

			$single_event[] = $single_date;
		}

		$all_events = array_merge( $all_events, $single_event);


	}


	$all_events = '['.implode(',', $all_events ).']';

	$event_list .= $all_events;

	#return $events_json;
	$event_list .= '</script>';

	return $event_list;
}

function show_event_list( $dev_id, $client_id, $event_id, $atts ) {
	$bpt = new BptApi;
	/* building the event list */

	$event_list = '<div id="bpt_events">';

	$events = $bpt->event_list_call( $dev_id, $client_id, $event_id, true, true );

	foreach ( $events as $event ) {
	$event_list .= '<div class="single_bpt_event">';

		$event_list .= '<h2 class="bpt_event_title event_'.$event['event_id'].'">'.$event['event_title'].'</h2>';
		$event_list .= '<div class="bpt_event_location">';
			$event_list .='<p class="event_address1">'.$event['event_address1'].'</p>';
			$event_list .='<p class="event_address2">'.$event['event_address2'].'</p>';
			$event_list .='<p class="event_city_state">';
				$event_list .='<span class="event_city">'.$event['event_city'].', </span>';
				$event_list .='<span class="event_state">'.$event['event_state'].'</span>';
			$event_list .= '</p>';
			$event_list .='<p class="event_zip">'.$event['event_zip'].'</p>';
		$event_list .= '</div>';
		$event_list .= '<div class="bpt_event_descriptions">';
			$event_list .= '<div class="bpt_short_description">'.$event['short_description'];
				$event_list .= '<br />';
				$event_list .= '<a class="show_full" data-eventdescription="'.$event['event_id'].'" href="#'.$event['event_id'].'">Show Full Description</a></div>';
			$event_list .= '<div class="bpt_full_description" data-eventdescription="'.$event['event_id'].'">';
				$event_list .= htmlspecialchars_decode( $event['full_description'] );
			$event_list .='</div>';
		$event_list .= '</div>';


		$event_list .= '<form method="post" class="add_to_cart event_'.$event['event_id'].'" action="http://www.brownpapertickets.com/addtocart.html">';

			$event_list .= '<div class="bpt_date_list" data-eventid="'.$event['event_id'].'">';
				$event_list .= '<label for="dates_for_'.$event['event_id'].'">Select a date: </label>';
				$event_list .= '<select id="dates_for_'.$event['event_id'].'" class="event_dates" name="date_id">';
					foreach ( $event['dates'][0] as $date ) {
						
						
						if ( $date['date_live'] == 'n' AND strtotime($date['date_start']) < time() ) {
							
						}

						else if ( $date['date_live'] == 'n' AND strtotime($date['date_start']) >= time() ) {
							/*
							Converts to dates that can be read by regular humans.
							*/

							$date_start = strtotime( $date['date_start'] );
							$formatted_date_start = strftime("%B %e, %Y", $date_start);

							$time_start = strtotime( $date['time_start'] );
							$formatted_time_start = strftime("%l:%M%p", $time_start);
							$event_list .= '<option class="bpt_single_date" data-dateid="'.$date['date_id'].'" value="'.$date['date_id'].' sold_out" />';
								$event_list .= '<span>No Tickets Available ('.$formatted_date_start.' at '.$formatted_time_start.')</span>';
							$event_list .= '</option>';
						} 
						
						else if ( $date['date_live'] == 'y' ) {
							/*
							Converts to dates that can be read by regular humans.
							*/
							$date_start = strtotime( $date['date_start'] );
							$formatted_date_start = strftime("%B %e, %Y", $date_start);

							$time_start = strtotime( $date['time_start'] );
							$formatted_time_start = strftime("%l:%M%p", $time_start);

							$event_list .= '<option class="bpt_single_date" data-dateid="'.$date['date_id'].'" value="'.$date['date_id'].'" />';
								$event_list .= '<span>'.$formatted_date_start.' at '.$formatted_time_start.'</span>';
							$event_list .= '</option>';
						}

						else {
							$event_list .= '<option class="bpt_single_date" data-dateid="'.$date['date_id'].'" value="'.$date['date_id'].' sold_out" />';
								$event_list .= '<span>No Tickets Available</span>';
							$event_list .= '</option>';
						}
					}
				$event_list .= '</select>';
			$event_list .= '</div>';

				foreach ( $event['dates'][0] as $date ) {
					$date_start = strtotime( $date['date_start'] );
					$formatted_date_start = strftime("%B %e, %Y", $date_start);

					$time_start = strtotime( $date['time_start'] );
					$formatted_time_start = strftime("%l:%M%p", $time_start);

					$event_list .= '<ul class="bpt_price_list" data-dateid="'.$date['date_id'].'">';
					$event_list .= '<fieldset>';
					$event_list .= '<legend class="bpt_prices_header">Prices for '.$formatted_date_start.' at '.$formatted_time_start.'</legend>';

					if ( !isset($date['prices'][0]) ) {
						$event_list .= '<li class="bpt_single_price">';
						$event_list .= '<strong>Sold Out</strong>';
						$event_list .= '</li>';
					}

					else {
						foreach ( $date['prices'][0] as $price )
							if ( $price['price_live'] == "y" ) {
								$event_list .= '<li class="bpt_single_price">';
									$event_list .= '<span class="bpt_price_name">'.$price['price_name'].' </span>';
									$event_list .= '<span class="bpt_price_value">$'.$price['price_value'].'</span>';
										$event_list .= '<label for="price_'.$price['price_id'].'"> Qty:</label>';
										$event_list .= '<select id="price_'.$price['price_id'].'" class="price_quantity" name="price_'.$price['price_id'].'">';
											$event_list .= '<option>0</option>';
											$event_list .= '<option>1</option>';
											$event_list .= '<option>2</option>';
											$event_list .= '<option>3</option>';
											$event_list .= '<option>4</option>';
											$event_list .= '<option>5</option>';
											$event_list .= '<option>6</option>';
											$event_list .= '<option>7</option>';
											$event_list .= '<option>8</option>';
											$event_list .= '<option>9</option>';
											$event_list .= '<option>10</option>';
											$event_list .= '<option>11</option>';
											$event_list .= '<option>12</option>';
											$event_list .= '<option>13</option>';
											$event_list .= '<option>14</option>';
											$event_list .= '<option>15</option>';
											$event_list .= '<option>16</option>';
											$event_list .= '<option>17</option>';
											$event_list .= '<option>18</option>';
											$event_list .= '<option>19</option>';
											$event_list .= '<option>20</option>';
											$event_list .= '<option>21</option>';
											$event_list .= '<option>22</option>';
											$event_list .= '<option>23</option>';
											$event_list .= '<option>24</option>';
											$event_list .= '<option>25</option>';
											$event_list .= '<option>26</option>';
											$event_list .= '<option>27</option>';
											$event_list .= '<option>28</option>';
											$event_list .= '<option>29</option>';
											$event_list .= '<option>30</option>';
										$event_list .= '</select>';
										$event_list .= '<p class="service_fees"> (Plus a service fee of $'.$price['price_service_fee'];
										$event_list .= ' and a venue fee of $'.$price['price_venue_fee'].')</p>';
										
								$event_list .= '</li>';
						}
					}
					$event_list .= '</fieldset>';
					#$event_list .= '<li class="shipping_options">';
					$event_list .= shipping_options( $date, $atts );
					#$event_list .= '</li>';
					$event_list .= '</ul>';

				}

		$event_list .= '<input type="hidden" name="event_id" value="'.$event['event_id'].'" />';
		$event_list .= '<input type="hidden" name="country_id" value="228" />';
		$event_list .= '<input class="add_to_cart" type="submit" value="Add to Cart" />';
		$event_list .= '</form>';
		$event_list .='</div>';
	}

	$event_list .= '</div>';
	return $event_list;
}

function shipping_options( $date, $atts ) {

	$shipping_list = '<label for="shipping_'.$date['date_id'].'"/>Shipping: </label>';
	$shipping_list .= '<select id="shipping_'.$date['date_id'].'" name="shipping_'.$date['date_id'].'" />';
	if ( isset( $atts['print_at_home'] ) and $atts['print_at_home'] == 'yes' ) { $shipping_list .= '<option value="5">Print-At-Home (No additional fee!)</option>'; }
	if ( isset( $atts['will_call'] ) and $atts['will_call'] == 'yes' ) { $shipping_list .= '<option value="4">Will-Call (No additional fee!)</option>'; }
	if ( isset( $atts['physical'] ) and $atts['physical'] == 'yes' ) { 
			$shipping_list .= '<option value="1">Physical Tickets - USPS 1st Class (No additional fee!)</option>'; 
			$shipping_list .= '<option value="2">Physical Tickets - USPS Priority Mail ($5.05)</option>';
			$shipping_list .= '<option value="3">Physical Tickets - USPS Priority Mail ($18.11)</option>';
	}
	if (isset( $atts['mobile'] )  and $atts['mobile'] = 'yes' ) {
		$shipping_list .= '<option value="7">Mobile (No additional fee!)</option>';
	}

	else {
		$shipping_list .= '<option value="5">Print-At-Home (No additional fee!)</option>';
		$shipping_list .= '<option value="4">Will-Call (No additional fee!)</option>';
	}
 	$shipping_list .= '</select>';
	return $shipping_list;
}

# Configure the shortcode
add_shortcode( 'list_events', 'bpt_api_list_events' );

function bpt_api_list_events( $atts ) {

	$dev_id = get_option('dev_id');
	$client_id = get_option('client_id');

	extract( shortcode_atts( array( 'event_id' => '', 'calendar' => '', 'print_at_home' => '', 'physical' => '', 'will_call' => '', 'mobile' => ''  ), $atts ) );

	if ( $calendar == 'yes' && !isset($event_id) ) {

		
		return $events = show_event_calendar( $dev_id, $client_id, null );
	}

	else if ( $calendar == 'yes' && isset( $event_id ) ) {

		return $events = show_event_calendar( $dev_id, $client_id, $event_id );
	}

	else if ( isset( $event_id ) ) {

		return $events = show_event_list( $dev_id, $client_id, $event_id, $atts );
	}

	else  {
		
		return $events = show_event_list( $dev_id, $client_id, null, $atts );
	}

	return $atts;

}

# Adds the plugin's css/javascript
add_action( 'wp_enqueue_scripts', 'add_bpt_api_css', 11 );

function add_bpt_api_css() {
	# enqueue css
	wp_register_style(
		'bpt_event_css',
		plugins_url('/css/style.css', __FILE__ )
	);
	wp_register_style(
		'bpt_calendar_css',
		plugins_url('/css/eventCalendar.css', __FILE__ )
	);
	wp_register_style(
		'bpt_theme_responsive',
		plugins_url('/css/eventCalendar_theme_responsive.css', __FILE__ )
	);
	wp_enqueue_style( 'bpt_calendar_css' );
	wp_enqueue_style( 'bpt_theme_responsive' );
	wp_enqueue_style( 'bpt_event_css' );

	# enqueue plugin javascript
	wp_register_script(
		'bpt_event_js',
		plugins_url('/js/functions.js', __FILE__ ),
		array()
	);

	#enqueue calendar plugin javascript #
	wp_register_script(
		'jquery_event_calendar',
		plugins_url('/js/jquery.eventCalendar.js', __FILE__ ),
		array()
	);

	if ( wp_script_is( 'jquery', $list = 'queue' ) == false ) {

		wp_enqueue_script( 'jquery' );
	}

	# load javascript
	wp_enqueue_script( 'bpt_event_js' );
	wp_enqueue_script( 'jquery_event_calendar' );



}

# Add the plugin's options menu to the Wordpress Admin
add_action ( 'admin_menu', 'add_bpt_settings_menu' );

function add_bpt_settings_menu() {

	add_options_page( 'Brown Paper Tickets API Settings', 'BPT API Settings', 'administrator', __FILE__, 'bpt_api_settings_page' );
}

add_action( 'admin_init', 'register_bpt_settings' );

function register_bpt_settings() {

	register_setting( 'bpt_api_settings', 'client_id' );
	register_setting( 'bpt_api_settings', 'dev_id' );

	add_settings_section('api_credentials', 'API Credentials', 'section_cb', __FILE__);

	add_settings_field( 'dev_id', 'Developer ID', 'developer_id', __FILE__, 'api_credentials');
	add_settings_field( 'client_id', 'Client ID', 'client_id', __FILE__, 'api_credentials');

}

function developer_id() {
	$dev_id = get_option('dev_id');
	echo '<input name="dev_id" type="text" value="'.$dev_id.'" />';
}

function client_id() {
	$client_id = get_option('client_id');
	echo '<input name ="client_id" type="text" value="'.$client_id.'" />';
}

function section_cb() {

}

function validate_settings( $bpt_api_settings ) {
	return $bpt_api_settings;
}

function bpt_api_settings_page() {
?>

<div class="wrap">
	<h1>Brown Paper Tickets API Suite Options</h1>
	<h2>Brown Paper Tickets Account Setup</h2>
	<form method="post" action="options.php">
		<?php settings_fields('bpt_api_settings'); ?>
		<?php do_settings_sections(__FILE__); ?>
		<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
	<h3>Developer ID</h3>
	<p>
		To use this plugin you must first have Developer Tools added to your Brown Paper Tickets account.
	</p>
	<p>
		To add those tools, when logged into Brown Paper Tickets, go to <a href="https://www.brownpapertickets.com/user/functions.html" target="_blank">Account Functions</a>.
	</p>
	<p>
		There you'll' see a box for "Developer Tools". Click it and then click, "Add Developer Tools". 
	</p>
	<p>
		Now, notice a new link in the Brown Paper Tickets navigation titled <a href="https://www.brownpapertickets.com/developer/index.html" target="_blank">Developer</a>? 
		Go there and you'll find your Developer ID which you will need to paste <b>exactly</b> as it appears into the Developer ID field above.
	</p>
	<h3>Client ID</h3>
	<p>
		Your Client ID is the Brown Paper Tickets <em>username</em> of your (or the producer whose events you want to list) account.
	</p>
	<p>
		Brown Paper Tickets will allow you to log in using either your username or the email address associated with your account. 
		<br />
		Sometimes they are the same, sometimes they are not.
	</p>
	<p>
		If you're receving the error "<b>Notice: Error retrieving XML. Please check your Client ID and Developer ID.</b>", first double check that your Developer ID is correct.
		<br />
		Second, check that the you can log into Brown Paper Tickets using the Client ID you're pasting above. If you can, then most likely your username is different from your email address.
	</p>
	<p>
		In order to obtain the actual log in, you'll need to call Brown Paper Tickets' Client Services department at <a href="tel:1.800.838.3006">1.800.838.3006</a>. 
		<br />
		Or, you could send them an <a href="mailto:support@brownpapertickets.com">email</a>. They'll be able to tell you right away what your username is (after verifying you're the account owner).
	</p>
	<p>
		If you're certain that your username is correct, it's possible that there is an extra space at the end of it or something similar. You'll need to contact Client Services to
		have that figured out.
	</p>
	<p>

	<h2>Brown Paper Tickets Plugin Usage</h2>
	<h4>There are some caveats to using this plugin. PLEASE READ!</h4>
	<p>
		The data returned by the <a href="http://www.brownpapertickets.com/apidocs/2.0/pricelist.html">pricelist</a> API call does not make a distinction between password protected prices and regular prices.
		<br />
		As a result, prices that are typically hidden by passwords on BPT will show up via the plugin.
		<br />
		<strong>DO NOT use this plugin if you intend to use the event list feature or want your password protected prices to stay hidden.</strong>
		<br />
		Calendar format should be OK as it does not make the price list API call.
	</p>
	
	<h3>Single Event Listing</h3>
	<p>Place the following shortcode in your post to display a single event</p>
	<code>[list_events event_id="XXXXXX"] where XXXXXX is the ID of the event.</code>

	<h3>Shipping Options</h3>
	<p>Use the following shortcode to display various shipping options.</p>

	<p>If no options are passed, the plugin will display will-call and print at home shipping options by default.</p>
	<code>[list_events print_at_home="yes" physical="yes" will_call="yes" mobile="yes"]</code>
	<p>
		<strong>
			The plugin has no way to ensure that the shipping options displayed actually exist on your BPT event.
			You need to be certain that the options are correct.
			<br />
			If you display the wrong shipping options, ticket buyers will get an error upon being transferred to the BPT checkout telling them that the shipping option no longer exists.
		</strong>
	</p>

	<h3>Calendar Format</h3>

	<p>Display a calendar listing all of your events:</p>
	<code>[list_events calendar="yes"]</code>

	<h4>About the Calendar</h4>

	<p>NOTE: This feature is currently somewhat buggy.</p>

	<p>The calendar is powered by the <a href="http://www.vissit.com/projects/eventCalendar/">jQuery Event Calendar plugin</a>.</p>

	<p>It was created by <a href="http://www.vissit.com/jquery-event-calendar-plugin-english-version">Jaime Fernández</a>.</p>

	<p>
	Some modifications have been made to the jQuery plugin for use in Wordpress and the way it displays dates.
	</p>
	<ul>
		<li>Replaced $ variable with jQuery. <a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_script#jQuery_noConflict_Wrappers">More Info</a>.</li>
		<li>Switched the getYear, getMonth, getDate, etc methods to their getUTC counterparts. </li>
		<li>Displays dates in the 12 hour format.</li>
	</ul>
	<h3>All Events</h3>
	<code>[list_events]</code>

	<h3>Customization</h3>

	<p>You can style the output of the plugin by editing the css/style.css file
	located in the plugin's folder</p>

	<h2>About the Plugin</h2>
	<p>The BPT Wordpress Plugin is Free and Open Source, released under the <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU GPL v2</a>.
	<p>It's source code can be found on <a href="https://github.com/BrownPaperTickets/bpt_wordpress_plugin" target="_blank">GitHub</a>.
	<p>If you encounter any bugs please submit them to the GitHub <a href="https://github.com/BrownPaperTickets/bpt_wordpress_plugin/issues?state=open" target="_blank">issues page</a>. 

</div>

<?php
}
?>