<?php
/**
 * Plugin Name: Custom Events
 * Description: Allows users to add custom events using a shortcode.
 * Version: 1.0
 * Author: your Name
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin code goes here.
// ####################### Custom post for Projects ##########################


add_action( 'init', 'register_events_posttype' );
	
function register_events_posttype() {
	$labels = array(
		'name'               => _x( 'Events', 'post type general name', 'twentytwenty' ),
		'singular_name'      => _x( 'Events', 'post type singular name', 'twentytwenty' ),
		'menu_name'          => _x( 'Events', 'admin menu', 'twentytwenty' ),
		'name_admin_bar'     => _x( 'Events', 'add new on admin bar', 'twentytwenty' ),
		'add_new'            => _x( 'Add New', 'Event', 'twentytwenty' ),
		'add_new_item'       => __( 'Add New Event', 'twentytwenty' ),
		'new_item'           => __( 'New Event', 'twentytwenty' ),
		'edit_item'          => __( 'Edit Event', 'twentytwenty' ),
		'view_item'          => __( 'View Event', 'twentytwenty' ),
		'all_items'          => __( 'All Events', 'twentytwenty' ),
		'search_items'       => __( 'Search Event', 'twentytwenty' ),
		'parent_item_colon'  => __( 'Parent Event:', 'twentytwenty' ),
		'not_found'          => __( 'No Event found.', 'twentytwenty' ),
		'not_found_in_trash' => __( 'No Event found in Trash.', 'twentytwenty' )
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __( 'Add Event here.', 'twentytwenty' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'rewrite'            => array('slug' => 'event'),
		'menu_icon' 		 => 'dashicons-screenoptions',
		'supports'           => array( 'title','thumbnail','editor' )
	);

	register_post_type( 'events', $args );
	
	// Add new taxonomy, NOT hierarchical (like tags)
	$labels = array(
		'name'                       => _x( 'Event Category', 'taxonomy general name', 'twentytwenty' ),
		'singular_name'              => _x( 'Event Category', 'taxonomy singular name', 'twentytwenty' ),
		'search_items'               => __( 'Search Event Category', 'twentytwenty' ),
		'popular_items'              => __( 'Popular Event Category', 'twentytwenty' ),
		'all_items'                  => __( 'All Event Category', 'twentytwenty' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Event Category', 'twentytwenty' ),
		'update_item'                => __( 'Update Event Category', 'twentytwenty' ),
		'add_new_item'               => __( 'Add New Event Category', 'twentytwenty' ),
		'new_item_name'              => __( 'New Event Category', 'twentytwenty' ),
		'separate_items_with_commas' => __( 'Separate Event Category with commas', 'twentytwenty' ),
		'add_or_remove_items'        => __( 'Add or remove Event Category', 'twentytwenty' ),
		'choose_from_most_used'      => __( 'Choose from the most used Event Category', 'twentytwenty' ),
		'not_found'                  => __( 'No Event Category found.', 'twentytwenty' ),
		'menu_name'                  => __( 'Event Category', 'twentytwenty' ),
	);

	$args = array(
		'hierarchical'          => true,
		'labels'                => $labels,
		'publicly_queryable'	=> false,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'event-category' ),
	);

	register_taxonomy( 'event-category', 'events', $args );
}

// -- Showing Events in calendar---
function get_all_events_calendar_function() {
    $current_date = date('Y-m-d');
    $events = array(
        'post_type'      => 'events',
		'post_status'    => 'publish',
		'posts_per_page' => -1, 
		'meta_key'       => 'start_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		//'orderby'        => 'post_date',
		'meta_query'     => array(
			 'relation' => 'AND',
				array(
					'key'     => 'end_date',
					'value'   => $current_date,
					'compare' => '>=',
					'type'    => 'DATE',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'start_date',
						'value'   => $current_date,
						'compare' => '<=',
						'type'    => 'DATE',
					),
					array(
						'key'     => 'end_date',
						'value'   => $current_date,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			),
	);
    $events_query = new WP_Query($events);
    $events_data = array();

    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $start_date = get_post_meta(get_the_ID(), 'start_date', true); 
            $end_date = get_post_meta(get_the_ID(), 'end_date', true);
			$display_end_date  = date('Ymd', strtotime($end_date.'+1 day'));
			$location_event = get_post_meta(get_the_ID(), 'location_event', true);
        	$time_event = get_post_meta(get_the_ID(), 'time_event', true);
			$Event_link_Meta = (get_post_meta($post_id, 'email', true) !== '') ? get_post_meta($post_id, 'email', true) : '';
			//echo $start_date .'-'.$end_date;
            $event_data = array(
                'title' => get_the_title(),
                'start' => $start_date,
                'end' => $display_end_date,
                'description' => get_the_content(),
				'original_end' => $end_date,
				'event_link_meta' =>$Event_link_Meta,
				'location_event' => $location_event,
            	'time_event' => $time_event
            );

            $events_data[] = $event_data;
			//echo '<pre>'; print_r($events_data); echo '</pre>';
        }
        wp_reset_postdata(); 
    }

    wp_send_json_success($events_data);
    die();
}

add_action('wp_ajax_get_all_events_calendar_function', 'get_all_events_calendar_function');
add_action('wp_ajax_nopriv_get_all_events_calendar_function', 'get_all_events_calendar_function');





//calendar event ends here//

add_shortcode('events', 'wp_events_function');
function wp_events_function(){
	$taxonomy = 'event-category'; 

	$terms = get_terms(array(
		'taxonomy' => $taxonomy,
		'hide_empty' => false,
	));

	$taxnomy_HTML = '';
	if ($terms && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			$taxnomy_HTML .= '<li class="type-1 '.esc_html($term->name).'" data-id="'.esc_html($term->term_id).'"><a href="#" data-id = '.esc_html($term->term_id).'>' . esc_html($term->name) . '</a></li>';
		}

	}

	$current_date = date('Y-m-d');
	$args = array();
	$args = array(
		'post_type'      => 'events',
		'post_status'    => 'publish',
		'posts_per_page' => -1, 
		'meta_key'       => 'start_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		//'orderby'        => 'post_date',
		'meta_query'     => array(
			 'relation' => 'AND',
				array(
					'key'     => 'end_date',
					'value'   => $current_date,
					'compare' => '>=',
					'type'    => 'DATE',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'start_date',
						'value'   => $current_date,
						'compare' => '<=',
						'type'    => 'DATE',
					),
					array(
						'key'     => 'end_date',
						'value'   => $current_date,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			),
	);
	
	$query = new WP_Query( $args );
	//echo '<pre>'; print_r($query); echo'</pre>';
	$finalhtml .= '<div class="academic-calendar-card-items-body komal">';
	if($query->have_posts()){
	while($query->have_posts()){
		$query->the_post();
		$post_id = $query->post->ID;
		$terms = get_the_terms($post_id, 'event-category');
		$category = '';
		if ($terms && !is_wp_error($terms)) {
			foreach ($terms as $term) {
				$category = $term->name;
				break;
			}
		}
		if($category){
			$category = $category;
		}
		
		$post_title = get_the_title($post_id);
		$post_content = get_post_field('post_content', $post_id);
		$url_post = get_permalink($post_id);
		$post_content = get_post_field('post_content', $post_id);
    $Location_event = get_post_meta( $post_id, 'location_event', true );
    $time_event = get_post_meta($post_id,'time_event', true);
		$get_external_link = (get_post_meta($post_id, 'external_link_for_post', true) != '') ? get_post_meta($post_id, 'external_link_for_post', true) : '';

		$heading_Meta = (get_post_meta($post_id, 'heading', true) !== '') ? get_post_meta($post_id, 'heading', true) : '';
		$Start_date = (get_post_meta($post_id, 'start_date', true) !== '') ? get_post_meta($post_id, 'start_date', true) : '';
		$End_date = (get_post_meta($post_id, 'end_date', true) !== '') ? get_post_meta($post_id, 'end_date', true) : '';
		$Back_to_events = 'https://humber.ca/innovativelearning/programs-and-events/';
		$Start_date_Meta = strtotime($Start_date);
		$End_date_Meta = strtotime($End_date);
	
		$acf_field = get_field('show_day', $post_id);
		//echo '<pre>'; print_r($acf_field. $post_id); echo '</pre>';
		if($acf_field === 'Yes' ){
			$start_day_name = date_i18n( "l", $Start_date_Meta );
 			$end_day_name = date_i18n( "l", $End_date_Meta );
			$final_name = $start_day_name .'-'.$end_day_name;
		}
		else{
			$start_day_name = '';
 			$end_day_name = '';
			$final_name = $start_day_name . $end_day_name;
		}
		
		$Apple_Meta = (get_post_meta($post_id, 'apple_callender', true) !== '') ? get_post_meta($post_id, 'apple_callender', true) : '';
   		$Google_Meta = 'https://calendar.google.com';
		$Event_link_Meta = (get_post_meta($post_id, 'email', true) !== '') ? get_post_meta($post_id, 'email', true) : '';
		$addnewclass= '';
		if($get_external_link != ''){
			$url_post = $get_external_link;
			
		}
		else{
			$url_post= '#';
			$addnewclass = 'no_popupthis';
		}
		

		$cur_date = current_time('Ymd');
		
		//echo $cur_date .'-'. $Start_date .'-'. $End_date;
		//$reminder_value = get_field('reminder_button', $post_id);
		$value = get_field_object('registration', $post_id);
		$selected_value = $value['value'];
		$add_reg = '';

			if($selected_value == 'reminder'){
				$newregisterlinkpopup = '<a class="no_popupthis reg_reminder" data-id='.$post_id.' href="#"></a>';
				$url_post = '';
 				$add_reg = 'reg_reminder_li';
			}
			else{
				if (($End_date < $cur_date || $Start_date < $cur_date && $End_date > $cur_date) && $post_id !== 41508) {
					$newregisterlinkpopup = '<a class="no_popupthis reg_closed check" data-id='.$post_id.' href="#">Registration Closed</a>';
					$url_post = '';
 					$add_reg = 'reg_closed_li';	
				}
				else{
					if($selected_value == 'register here'){
						if( $get_external_link != ''){
							$newregisterlinkpopup = '<a class="reg_here" data-id='.$post_id.' href="'.$get_external_link.'">Register Here</a>';
							$add_reg = 'reg_here_li';
						}
						else{
							$newregisterlinkpopup = '<a class="no_popupthis reg_here" data-id='.$post_id.' href="#">Register Here</a>';
							$add_reg = 'reg_here_li';
						}
					}
					elseif($selected_value == 'registration coming'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_coming" data-id='.$post_id.' href="#">Registration Coming</a>';
						$url_post = '';
						$add_reg = 'reg_coming_li';
					}
					elseif($selected_value == 'registration closed'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
						$url_post = '';
		 				$add_reg = 'reg_closed_li';
					}
				}
			}

		
		
		
// 		//-----For email and calander content-----------// 		
		$Emailsubject = rawurlencode($post_title);
		$finalSubaject = 'Reply to: '.$Emailsubject;
		$Emailbody = 'Save The Date on ' . date('F j, Y', strtotime($Start_date)) . "\n";
			if (!empty($Emailsubject)) {
				$Emailbody .=   $post_title ."\n";
			}
			if (!empty($get_external_link)) {
				$Emailbody .=  $get_external_link . "\n";
			}
			if (!empty($time_event)) {
				$Emailbody .=  $time_event . "\n";
			}
			if (!empty($Location_event)) {
				$Emailbody .=  $Location_event . "\n";
			}
			$Emailbody .= $Back_to_events. "\n";
			//$Emailbody = str_replace('%20', ' ', $Emailbody);
			$email_body_encoded =  rawurlencode($Emailbody);
			
	
		
		//$startDate = rawurlencode($Start_date);
		//$endDAte = rawurlencode($End_date);
		$startDate = date('Y-m-d\TH:i:s\Z', strtotime($Start_date));
		$endDAte = date('Y-m-d\TH:i:s\Z', strtotime($End_date));
		$calanderTitle = urlencode($post_title);
		$calanderBody = urlencode('Save The Date For');
		
// 		$Applesubject = urlencode($post_title);
// 		$Applebody = urlencode('Save The Date For');


// 		if ($Apple_Meta == '') {
// 			$Apple_Meta = 'data:text/calendar;charset=utf8,BEGIN:VCALENDAR%0AVERSION:2.0%0ABEGIN:VEVENT%0ASUMMARY:Your%20Event%20Title%0ADESCRIPTION:Your%20Event%20Description%0ALOCATION:Your%20Event%20Location%0ADTSTART:' . $startDate . '%0ADTEND:' . $endDAte . '%0AEND:VEVENT%0AEND:VCALENDAR';
// 		}

// 		$appleCalendarLink = $Apple_Meta . '&subject=' . $Applesubject . '&body=' . $Applebody;

		//-----END---------// 		
		$Date_TimeHTML = '';
		if($Start_date_Meta == $End_date_Meta || $End_date_Meta == '' ){
			$Date_TimeHTML = '<div class="date-info">
     
      		<span class="day-info">'.$start_day_name.'</span>
      		<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).', '.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
      		<span class="year-info"></span>
            <span class="time-event">'.$time_event.'</span>
			 <span class="locations">'.$Location_event.'</span>

					</div>'; 
		}else{
			$start_year = date_i18n( "Y", $Start_date_Meta );
			$end_year = date_i18n( "Y", $End_date_Meta );
			if($start_year == $end_year){
				$Date_TimeHTML = '<div class="date-info">
        	<span class="year-info">'.$final_name.'</span>
			<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
            <span class="time-event">'.$time_event.'</span>
		    <span class="locations">'.$Location_event.'</span>

				</div>';
			}else{
				$Date_TimeHTML = '<div class="date-info">
        
        <span class="year-info">'.$final_name.'</span>
			<span class="day-info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.$start_year.'-'.$end_year.'</strong></span>
            <span class="time-event">'.$time_event.'</span>
			<span class="locations">'.$Location_event.'</span>
				</div>';
			}
		}
		$terms = get_the_terms($post_id, 'event-category');
		$customecatcode = '';
		foreach ($terms as $term) {
				$category = $term->name;
			$customecatcode .='	<li class="'.$category.'">
								<img src="'.$img.'" alt="">
								<span>'.$category.'</span>
              </li>';
		}
		
		$finalhtml .='<div class="academic-calendar-card-items-body-item" data-start="'.$Start_date.'" data-end="'.$End_date.'">
				<div class="event-info">
					<a class ="head_title '.$addnewclass.'" href="'.$url_post.'" data-id='.$post_id.'><h3>'.$post_title.'</h3></a>
          <p class="event-desc">'.$post_content.'</p>
						<ul class="selected-academic-calendar-date-types">
							'.$customecatcode.'
              <li class="click-here '.$add_reg.'">
								<span class="custompopup" data-id='.$post_id.'>'.$newregisterlinkpopup.'</span>
							</li>
						</ul>
					<div class="share-icons">
						<a href="https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt='.$startDate.'&enddt='.$endDAte.'&subject='.$calanderTitle.'&body='.$calanderBody.' '.$calanderTitle.'">
					<span>Microsoft Calendar <i class="fa fa-windows"></i></span>
						</a>

						<a href="mailto:'.$Event_link_Meta.'?subject='.$finalSubaject.'&body='.$email_body_encoded.'">
    						<span>Share <i class="fa fa-envelope"></i></span>
						</a>
					</div>
				</div>
					'.$Date_TimeHTML.'
			</div>';
		
	
		
 	}
 	wp_reset_postdata();
 	}
	 $html_output = '<div class="academic-calendar-filters-wrp second">
        <div class="filter-group-items filter-date-type-wrp">
            <span>Filter by Topic:</span>
            <ul class="academic-date-types-list acadmic-cat">
                ' . $taxnomy_HTML . '
            </ul>
        </div>
        <div class="filter-group-items justify-content-end">
            <button type="button" class="clear-filter-btn">Clear Filters X</button>
        </div>
    </div>
    <div class="filter-group-items toggle-btns events_btn">
        <button type="button" class="current_events active" data-title="current" data-current="current">Events</button>
        <button type="button" class="old_events" data-title="old" data-old="old">PAST EVENTS</button>
		<button type="button" class="calender-button_new view">Calendar View</button>
    </div> 
	
	<div>
		<form  method="get" id="searchform">
			<input type="text" value="" name="s" id="s" placeholder="Search Custom Post Type" />
			<input type="button" id="searchsubmit" value="Search" />
    	</form>
	</div>
	<div id="calendar_new" style="display:none"></div>
    <div id="event-container">' . $finalhtml . '</div>';

    // Custom search form


    // Modify search query
   function custom_search_filter($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $search_query = get_search_query();
        if (!empty($search_query)) {
            $query->set('post_type', 'events');
            $query->set('s', $search_query);
            $query->set('post_title', $search_query);
        } else {
            $query->set('post_type', 'events');
        }
    }
}
//add_action('pre_get_posts','custom_search_filter');
    return $html_output;
}












// -----------Fillter Upcoming and Old Events-----------
add_action( 'wp_ajax_nopriv_get_upcomin_events_post', 'get_upcomin_events_post' );
add_action( 'wp_ajax_get_upcomin_events_post', 'get_upcomin_events_post' );
function get_upcomin_events_post(){
	$new_olddata= $_POST['dataattr'];
	$today = current_time('ymd');
// 	if($new_olddata == 'new'){
// 				$args = array(
// 			'post_type'      => 'events',
// 			'posts_per_page' => -1,
// 			'meta_key'       => 'start_date',
//     		'orderby'        => 'meta_value',
//     		'order'          => 'ASC',
// 			'meta_query'     => array(
// 				array(
// 					'key'     => 'start_date',
// 					'value'   => $today,
// 					'compare' => '>',
// 					'type'    => 'DATE',
// 				),
// 			),
// 		);
// 	}
	if($new_olddata == 'current'){
			$args = array(
			'post_type'      => 'events',
			'posts_per_page' => -1,
			'meta_key'       => 'start_date',
    		'orderby'        => 'meta_value',
    		'order'          => 'ASC',
			'meta_query'     => array(
			 'relation' => 'AND',
				array(
					'key'     => 'end_date',
					'value'   => $today,
					'compare' => '>=',
					'type'    => 'DATE',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'start_date',
						'value'   => $today,
						'compare' => '<=',
						'type'    => 'DATE',
					),
					array(
						'key'     => 'end_date',
						'value'   => $today,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			),
		);
	}
	else{
			$args = array(
			'post_type'      => 'events',
			'posts_per_page' => -1,
			'meta_key'       => 'end_date', 
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => 'end_date',
					'value'   => $today,
					'compare' => '<',
					'type'    => 'DATE',
				),
			),
		);
	}

	$query = new WP_Query( $args );
	$finalhtml .= '<div class="academic-calendar-card-items-body">';
	if($query->have_posts()){
		while($query->have_posts()){
			$query->the_post();
			$post_id = $query->post->ID;
			$terms = get_the_terms($post_id, 'event-category');
			$category = '';
			if ($terms && !is_wp_error($terms)) {
				foreach ($terms as $term) {
					$category = $term->name;
					break;
				}
			}
			if($category){
				$category = $category;
			}

			$post_title = get_the_title($post_id);
			$post_content = get_post_field('post_content', $post_id);
			$url_post = get_permalink($post_id);
			$post_content = get_post_field('post_content', $post_id);
     		$Location_event = get_post_meta( $post_id, 'location_event', true );
      		$time_event = get_post_meta($post_id,'time_event', true);
			$get_external_link = (get_post_meta($post_id, 'external_link_for_post', true) != '') ? get_post_meta($post_id, 'external_link_for_post', true) : '';

			$heading_Meta = (get_post_meta($post_id, 'heading', true) !== '') ? get_post_meta($post_id, 'heading', true) : '';
			$Start_date = (get_post_meta($post_id, 'start_date', true) !== '') ? get_post_meta($post_id, 'start_date', true) : '';
			$End_date = (get_post_meta($post_id, 'end_date', true) !== '') ? get_post_meta($post_id, 'end_date', true) : '';
			$Back_to_events = 'https://humber.ca/innovativelearning/programs-and-events/';
			$Start_date_Meta = strtotime($Start_date);
			$End_date_Meta = strtotime($End_date);
			
			$acf_field = get_field('show_day', $post_id);
			//echo '<pre>'; print_r($acf_field. $post_id); echo '</pre>';
			if($acf_field === 'Yes' ){
				$start_day_name = date_i18n( "l", $Start_date_Meta );
				$end_day_name = date_i18n( "l", $End_date_Meta );
				$final_name = $start_day_name . '-' .$end_day_name;
			}
			else{
				$start_day_name = '';
				$end_day_name = '';
				$final_name = $start_day_name . $end_day_name;
			}
			
// 		//-----For email and calander content-----------// 		
		$Emailsubject = rawurlencode($post_title);
		$finalSubaject = 'Reply to: '.$Emailsubject;
		$Emailbody = 'Save The Date on ' . date('F j, Y', strtotime($Start_date)) . "\n";
			if (!empty($Emailsubject)) {
				$Emailbody .=   $post_title ."\n";
			}
			if (!empty($get_external_link)) {
				$Emailbody .=  $get_external_link . "\n";
			}
			if (!empty($time_event)) {
				$Emailbody .=  $time_event . "\n";
			}
			if (!empty($Location_event)) {
				$Emailbody .=  $Location_event . "\n";
			}
			$Emailbody .= $Back_to_events. "\n";
			//$Emailbody = str_replace('%20', ' ', $Emailbody);
			$email_body_encoded =  rawurlencode($Emailbody);
			//echo '<pre>'; print_r($Emailbody.$post_id); echo '</pre>';
			
			$Apple_Meta = (get_post_meta($post_id, 'apple_callender', true) !== '') ? get_post_meta($post_id, 'apple_callender', true) : '';
     		$Google_Meta = 'https://calendar.google.com';
			$Event_link_Meta = (get_post_meta($post_id, 'email', true) !== '') ? get_post_meta($post_id, 'email', true) : '';

			if($get_external_link != ''){
				$url_post = $get_external_link;
			}
			else{
			$url_post= '#';
			$addnewclass = 'no_popupthis';
			}
			
			
			$cur_date = current_time('Ymd');
			$value = get_field_object('registration', $post_id);
			$selected_value = $value['value'];
			$add_reg = '';

			if($selected_value == 'reminder'){
				$newregisterlinkpopup = '<a class="no_popupthis reg_reminder" data-id='.$post_id.' href="#"></a>';
				$url_post = '';
 				$add_reg = 'reg_reminder_li';
			}
			else{
				if (($End_date < $cur_date || $Start_date < $cur_date && $End_date > $cur_date) && $post_id !== 41508) {
					$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
					$url_post = '';
 					$add_reg = 'reg_closed_li';	
				}
				else{
					if($selected_value == 'register here'){
						if( $get_external_link != ''){
						$newregisterlinkpopup = '<a class="reg_here" data-id='.$post_id.' href="'.$get_external_link.'">Register Here</a>';
						$add_reg = 'reg_here_li';
						}
						else{
							$newregisterlinkpopup = '<a class="no_popupthis reg_here" data-id='.$post_id.' href="#">Register Here</a>';
							$add_reg = 'reg_here_li';
						}
					}
					elseif($selected_value == 'registration coming'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_coming" data-id='.$post_id.' href="#">Registration Coming</a>';
						$url_post = '';
						$add_reg = 'reg_coming_li';
					}
					elseif($selected_value == 'registration closed'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
						$url_post = '';
		 				$add_reg = 'reg_closed_li';
					}
				}
			}
			

		
			//$startDate = rawurlencode($Start_date);
			//$endDAte = rawurlencode($End_date);
			$startDate = date('Y-m-d\TH:i:s\Z', strtotime($Start_date));
			$endDAte = date('Y-m-d\TH:i:s\Z', strtotime($End_date));
			$calanderTitle = rawurlencode($post_title);
			$calanderBody = rawurlencode('Save The Date For');
			
// 			$Applesubject = urlencode($post_title);
// 			$Applebody = urlencode('Save The Date For');
// 			if ($Apple_Meta == '') {
// 				$Apple_Meta = 'data:text/calendar;charset=utf8,BEGIN:VCALENDAR%0AVERSION:2.0%0ABEGIN:VEVENT%0ASUMMARY:Your%20Event%20Title%0ADESCRIPTION:Your%20Event%20Description%0ALOCATION:Your%20Event%20Location%0ADTSTART:' . $startDate . '%0ADTEND:' . $endDAte . '%0AEND:VEVENT%0AEND:VCALENDAR';
// 			}

// 			$appleCalendarLink = $Apple_Meta . '&subject=' . $Applesubject . '&body=' . $Applebody;
			//-----END---------// 		
			$Date_TimeHTML = '';
			if($Start_date_Meta == $End_date_Meta || $End_date_Meta == '' ){
				$Date_TimeHTML = '<div class="date-info">
       			  <span class="year-info">'.$start_day_name.'</span>
				  <span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).', '.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
                  <span class="time-event">'.$time_event.'</span>
				  <span class="locations">'.$Location_event.'</span>
				</div> '; 
			}else{
				$start_year = date_i18n( "Y", $Start_date_Meta );
				$end_year = date_i18n( "Y", $End_date_Meta );
				if($start_year == $end_year){
					$Date_TimeHTML = '<div class="date-info">
        			 <span class="year-info">'.$final_name.'</span>
					<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.date_i18n( "Y", $Start_date_Meta ).'</strong></span>							
                    <span class="time-event">'.$time_event.'</span>
					<span class="locations">'.$Location_event.'</span>
                    </div>'; 
				}else{
					$Date_TimeHTML = '<div class="date-info">
          			<span class="year-info">'.$final_name.'</span>
					<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.$start_year.'-'.$end_year.'</strong></span>										
                    <span class="time-event">'.$time_event.'</span>
					<span class="locations">'.$Location_event.'</span>
				</div>'; 
				}
			}
			
			$terms = get_the_terms($post_id, 'event-category');
			$customecatcode = '';
			foreach ($terms as $term) {
				$category = $term->name;
				$customecatcode .='	<li class="'.$category.'">
										<img src="'.$img.'" alt="">
										<span>'.$category.'</span>
					  </li>';
			}
			$finalhtml .='<div class="academic-calendar-card-items-body-item" data-id='.$post_id.' data-start="'.$Start_date.'" data-end="'.$End_date.'">
							<div class="event-info">
							<a class="head_title '.$addnewclass.'" href="'.$url_post.'"><h3>'.$post_title.'</h3></a>
							<p class="event-desc">'.$post_content.'</p>
							<ul class="selected-academic-calendar-date-types">
								'.$customecatcode.'
                  <li class="click-here '.$add_reg.'">
									<span class="custompopup" data-id='.$post_id.'>'.$newregisterlinkpopup.'</span>
								</li>
							</ul>
							<div class="share-icons">
								<a href="https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt='.$startDate.'&enddt='.$endDAte.'&subject='.$calanderTitle.'&body='.$calanderBody.' '.$calanderTitle.'">
					<span>Microsoft Calendar <i class="fa fa-windows"></i></span>
								</a>
								<a data-id='.$post_id.' href="mailto:'.$Event_link_Meta.'?subject='.$finalSubaject.'&body='.$email_body_encoded.'">
									<span>Share <i class="fa fa-envelope"></i></span>
								</a>
							</div>
						</div>
						'.$Date_TimeHTML.'
						
				</div>';
		}
		wp_reset_postdata();
	}else{
		$finalhtml = '<div class="notfound_post"><p>No events found</p></div>';
	}
	echo $finalhtml;
	die();
}

//------------ Fillter Events by Category-----------
add_action('wp_ajax_nopriv_fillters_events_function', 'fillters_events_function');
add_action('wp_ajax_fillters_events_function', 'fillters_events_function');

function fillters_events_function(){
	$EventTitlebtn = $_POST['eventsTitle'];
    $CatID = $_POST['CatID'];
    $today = current_time('ymd');
    $args = array();

    if ($EventTitlebtn == 'current' && $CatID != '') {	
        $args = array(
            'post_type'      => 'events',
			'posts_per_page' => -1,
            'meta_key'       => 'start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => 'end_date',
                    'value'   => $today,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'event-category',
                    'field'    => 'term_id',
                    'terms'    => $CatID,
                ),
            ),
        );
	}else{
		$args = array(
            'post_type'      => 'events',
            'meta_key'       => 'start_date',
            'orderby'        => 'meta_value',
           'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => 'end_date',
					'value'   => $today,
					'compare' => '<',
					'type'    => 'DATE',
				),
			),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'event-category',
                    'field'    => 'term_id',
                    'terms'    => $CatID,
                ),
            ),
        );
	}
    $query = new WP_Query($args);

    $finalhtml .= '<div class="academic-calendar-card-items-body">';
	if($query->have_posts()){
		while($query->have_posts()){
			$query->the_post();
			$post_id = $query->post->ID;
			$terms = get_the_terms($post_id, 'event-category');
			$category = '';
			if ($terms && !is_wp_error($terms)) {
				foreach ($terms as $term) {
					$category = $term->name;
					break;
				}
			}
			if($category){
				$category = $category;
			}
			$post_title = get_the_title($post_id);
			$post_content = get_post_field('post_content', $post_id);
			$url_post = get_permalink($post_id);
			$post_content = get_post_field('post_content', $post_id);

			$get_external_link = (get_post_meta($post_id, 'external_link_for_post', true) != '') ? get_post_meta($post_id, 'external_link_for_post', true) : '';
      $Location_event = get_post_meta( $post_id, 'location_event', true );
      $time_event = get_post_meta($post_id,'time_event', true);
			$heading_Meta = (get_post_meta($post_id, 'heading', true) !== '') ? get_post_meta($post_id, 'heading', true) : '';
			$Start_date = (get_post_meta($post_id, 'start_date', true) !== '') ? get_post_meta($post_id, 'start_date', true) : '';
			$End_date = (get_post_meta($post_id, 'end_date', true) !== '') ? get_post_meta($post_id, 'end_date', true) : '';
			$Back_to_events = 'https://humber.ca/innovativelearning/programs-and-events/';
			$Start_date_Meta = strtotime($Start_date);
			$End_date_Meta = strtotime($End_date);
			$acf_field = get_field('show_day', $post_id);
			//echo '<pre>'; print_r($acf_field. $post_id); echo '</pre>';
			if($acf_field === 'Yes' ){
				$start_day_name = date_i18n( "l", $Start_date_Meta );
				$end_day_name = date_i18n( "l", $End_date_Meta );
				$final_name = $start_day_name .'-'.$end_day_name;
			}
			else{
				$start_day_name = '';
				$end_day_name = '';
				$final_name = $start_day_name . $end_day_name;
			}
			$Apple_Meta = (get_post_meta($post_id, 'apple_callender', true) !== '') ? get_post_meta($post_id, 'apple_callender', true) : '';
      		$Google_Meta = 'https://calendar.google.com';
			$Event_link_Meta = (get_post_meta($post_id, 'email', true) !== '') ? get_post_meta($post_id, 'email', true) : '';

			if($get_external_link != ''){
				$url_post = $get_external_link;
			}
			else{
			$url_post= '#';
			$addnewclass = 'no_popupthis';
		}
		
		$cur_date = current_time('Ymd');
		$value = get_field_object('registration', $post_id);
		$selected_value = $value['value'];
		$add_reg = '';
		//echo '<pre>'; print_r($reminder_value.$post_id); echo '</pre>';

		if($selected_value == 'reminder'){
				$newregisterlinkpopup = '<a class="no_popupthis reg_reminder" data-id='.$post_id.' href="#"></a>';
				$url_post = '';
 				$add_reg = 'reg_reminder_li';
			}
			else{
				if (($End_date < $cur_date || $Start_date < $cur_date && $End_date > $cur_date) && $post_id !== 41508) {
					$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
					$url_post = '';
 					$add_reg = 'reg_closed_li';	
				}
				else{
					if($selected_value == 'register here'){
						if( $get_external_link != ''){
						$newregisterlinkpopup = '<a class="reg_here" data-id='.$post_id.' href="'.$get_external_link.'">Register Here</a>';
						$add_reg = 'reg_here_li';
					}
					else{
						$newregisterlinkpopup = '<a class="no_popupthis reg_here" data-id='.$post_id.' href="#">Register Here</a>';
						$add_reg = 'reg_here_li';
					}
					}
					elseif($selected_value == 'registration coming'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_coming" data-id='.$post_id.' href="#">Registration Coming</a>';
						$url_post = '';
						$add_reg = 'reg_coming_li';
					}
					elseif($selected_value == 'registration closed'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
						$url_post = '';
		 				$add_reg = 'reg_closed_li';
					}
				}
			}
			
			
// 		//-----For email and calander content-----------// 		
		$Emailsubject = rawurlencode($post_title);
		$finalSubaject = 'Reply to: '.$Emailsubject;
		$Emailbody = 'Save The Date on ' . date('F j, Y', strtotime($Start_date)) . "\n";
			if (!empty($Emailsubject)) {
				$Emailbody .=   $post_title ."\n";
			}
			if (!empty($get_external_link)) {
				$Emailbody .=  $get_external_link . "\n";
			}
			if (!empty($time_event)) {
				$Emailbody .=  $time_event . "\n";
			}
			if (!empty($Location_event)) {
				$Emailbody .=  $Location_event . "\n";
			}
			$Emailbody .= $Back_to_events. "\n";
			//$Emailbody = str_replace('%20', ' ', $Emailbody);
			$email_body_encoded =  rawurlencode($Emailbody);
			
			
			//$startDate = rawurlencode($Start_date);
			//$endDAte = rawurlencode($End_date);
			$startDate = date('Y-m-d\TH:i:s\Z', strtotime($Start_date));
			$endDAte = date('Y-m-d\TH:i:s\Z', strtotime($End_date));
			$calanderTitle = rawurlencode($post_title);
			$calanderBody = rawurlencode('Save The Date For');
			
// 			$Applesubject = urlencode($post_title);
// 			$Applebody = urlencode('Save The Date For');
// 			if ($Apple_Meta == '') {
// 				$Apple_Meta = 'data:text/calendar;charset=utf8,BEGIN:VCALENDAR%0AVERSION:2.0%0ABEGIN:VEVENT%0ASUMMARY:Your%20Event%20Title%0ADESCRIPTION:Your%20Event%20Description%0ALOCATION:Your%20Event%20Location%0ADTSTART:' . $startDate . '%0ADTEND:' . $endDAte . '%0AEND:VEVENT%0AEND:VCALENDAR';
// 			}

// 			$appleCalendarLink = $Apple_Meta . '&subject=' . $Applesubject . '&body=' . $Applebody;
			//---------End---------
		
			$Date_TimeHTML = '';
			if($Start_date_Meta == $End_date_Meta || $End_date_Meta == '' ){
				$Date_TimeHTML = '<div class="date-info">
       			 <span class="year-info">'.$start_day_name.'</span>
				<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).','.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
                 <span class="time-event">'.$time_event.'</span>
				 <span class="locations">'.$Location_event.'</span>
								</div>';
			}else{
				$start_year = date_i18n( "Y", $Start_date_Meta );
				$end_year = date_i18n( "Y", $End_date_Meta );
				if($start_year == $end_year){
					$Date_TimeHTML = '<div class="date-info">
        			<span class="year-info">'.$final_name.'</span>
										<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
										<span class="year-info"></span>
                    <span class="time-event">'.$time_event.'</span>
					<span class="locations">'.$Location_event.'</span>

									</div>';
				}else{
					$Date_TimeHTML = '<div class="date-info">
                     <span class="year-info">'.$final_name.'</span>
										<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.$start_year.'-'.$end_year.'</strong></span>
                     <span class="time-event">'.$time_event.'</span>
					 <span class="locations">'.$Location_event.'</span>

									</div>';
				}
			}
			
			$terms = get_the_terms($post_id, 'event-category');
				$customecatcode = '';
				foreach ($terms as $term) {
						$category = $term->name;
					$customecatcode .='	<li class="'.$category.'">
										<img src="'.$img.'" alt="">
										<span>'.$category.'</span>
					  </li>';
				}
			$finalhtml .='<div class="academic-calendar-card-items-body-item" data-start="'.$Start_date.'" data-end="'.$End_date.'">
					<div class="event-info">
						<a class="head_title '.$addnewclass.'" href="'.$url_post.'"><h3>'.$post_title.'</h3></a>
						<p class="event-desc">'.$post_content.'</p>
						<ul class="selected-academic-calendar-date-types">
						'.$customecatcode.'
                <li class="click-here '.$add_reg.'">
								<span class="custompopup" data-id='.$post_id.'>'.$newregisterlinkpopup.'</span>
							</li>
						</ul>
						<div class="share-icons">
							<a href="https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt='.$startDate.'&enddt='.$endDAte.'&subject='.$calanderTitle.'&body='.$calanderBody.' '.$calanderTitle.'">
					<span>Microsoft Calendar <i class="fa fa-windows"></i></span>
							</a>
							<a href="mailto:'.$Event_link_Meta.'?subject='.$finalSubaject.'&body='.$email_body_encoded.'">
								<span>Share <i class="fa fa-envelope"></i></span>
							</a>
						</div>
					</div>
						'.$Date_TimeHTML.'
						
				</div>';
		}
		wp_reset_postdata();
		}else{
			$finalhtml = '<div class="notfound_post"><p>No events found</p></div>';
		}
		echo $finalhtml;
		die();
}

//search box ajax 
add_action('wp_ajax_nopriv_search_events_function', 'search_events_function');
add_action('wp_ajax_search_events_function', 'search_events_function');

function search_events_function(){
    $SearchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
    $args = array();
    $search_args = array(
        'post_type'      => 'events',
        's'              => $SearchQuery, 
        'posts_per_page' => -1,
    );

    $search_query = new WP_Query($search_args);
	$finalhtml .= '<div class="academic-calendar-card-items-body">';
	if($search_query->have_posts()){
		while($search_query->have_posts()){
			$search_query->the_post();
			$post_id = $search_query->post->ID;
			$terms = get_the_terms($post_id, 'event-category');
			$category = '';
			if ($terms && !is_wp_error($terms)) {
				foreach ($terms as $term) {
					$category = $term->name;
					break;
				}
			}
			if($category){
				$category = $category;
			}
			$post_title = get_the_title($post_id);
			$post_content = get_post_field('post_content', $post_id);
			$url_post = get_permalink($post_id);
			$post_content = get_post_field('post_content', $post_id);

			$get_external_link = (get_post_meta($post_id, 'external_link_for_post', true) != '') ? get_post_meta($post_id, 'external_link_for_post', true) : '';
      $Location_event = get_post_meta( $post_id, 'location_event', true );
      $time_event = get_post_meta($post_id,'time_event', true);
			$heading_Meta = (get_post_meta($post_id, 'heading', true) !== '') ? get_post_meta($post_id, 'heading', true) : '';
			$Start_date = (get_post_meta($post_id, 'start_date', true) !== '') ? get_post_meta($post_id, 'start_date', true) : '';
			$End_date = (get_post_meta($post_id, 'end_date', true) !== '') ? get_post_meta($post_id, 'end_date', true) : '';
			$Back_to_events = 'https://humber.ca/innovativelearning/programs-and-events/';
			$Start_date_Meta = strtotime($Start_date);
			$End_date_Meta = strtotime($End_date);
			$acf_field = get_field('show_day', $post_id);
			//echo '<pre>'; print_r($acf_field. $post_id); echo '</pre>';
			if($acf_field === 'Yes' ){
				$start_day_name = date_i18n( "l", $Start_date_Meta );
				$end_day_name = date_i18n( "l", $End_date_Meta );
				$final_name = $start_day_name .'-'.$end_day_name;
			}
			else{
				$start_day_name = '';
				$end_day_name = '';
				$final_name = $start_day_name . $end_day_name;
			}
			$Apple_Meta = (get_post_meta($post_id, 'apple_callender', true) !== '') ? get_post_meta($post_id, 'apple_callender', true) : '';
      		$Google_Meta = 'https://calendar.google.com';
			$Event_link_Meta = (get_post_meta($post_id, 'email', true) !== '') ? get_post_meta($post_id, 'email', true) : '';

			if($get_external_link != ''){
				$url_post = $get_external_link;
			}
			else{
			$url_post= '#';
			$addnewclass = 'no_popupthis';
		}
		
		$cur_date = current_time('Ymd');
		$value = get_field_object('registration', $post_id);
		$selected_value = $value['value'];
		$add_reg = '';
		//echo '<pre>'; print_r($reminder_value.$post_id); echo '</pre>';

		if($selected_value == 'reminder'){
				$newregisterlinkpopup = '<a class="no_popupthis reg_reminder" data-id='.$post_id.' href="#"></a>';
				$url_post = '';
 				$add_reg = 'reg_reminder_li';
			}
			else{
				if ($End_date < $cur_date || $Start_date < $cur_date && $End_date > $cur_date) {
					$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
					$url_post = '';
 					$add_reg = 'reg_closed_li';	
				}
				else{
					if($selected_value == 'register here'){
						if( $get_external_link != ''){
						$newregisterlinkpopup = '<a class="reg_here" data-id='.$post_id.' href="'.$get_external_link.'">Register Here</a>';
						$add_reg = 'reg_here_li';
					}
					else{
						$newregisterlinkpopup = '<a class="no_popupthis reg_here" data-id='.$post_id.' href="#">Register Here</a>';
						$add_reg = 'reg_here_li';
					}
					}
					elseif($selected_value == 'registration coming'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_coming" data-id='.$post_id.' href="#">Registration Coming</a>';
						$url_post = '';
						$add_reg = 'reg_coming_li';
					}
					elseif($selected_value == 'registration closed'){
						$newregisterlinkpopup = '<a class="no_popupthis reg_closed" data-id='.$post_id.' href="#">Registration Closed</a>';
						$url_post = '';
		 				$add_reg = 'reg_closed_li';
					}
				}
			}
			
			
// 		//-----For email and calander content-----------// 		
		$Emailsubject = rawurlencode($post_title);
		$finalSubaject = 'Reply to: '.$Emailsubject;
		$Emailbody = 'Save The Date on ' . date('F j, Y', strtotime($Start_date)) . "\n";
			if (!empty($Emailsubject)) {
				$Emailbody .=   $post_title ."\n";
			}
			if (!empty($get_external_link)) {
				$Emailbody .=  $get_external_link . "\n";
			}
			if (!empty($time_event)) {
				$Emailbody .=  $time_event . "\n";
			}
			if (!empty($Location_event)) {
				$Emailbody .=  $Location_event . "\n";
			}
			$Emailbody .= $Back_to_events. "\n";
			//$Emailbody = str_replace('%20', ' ', $Emailbody);
			$email_body_encoded =  rawurlencode($Emailbody);
			
			
			//$startDate = rawurlencode($Start_date);
			//$endDAte = rawurlencode($End_date);
			$startDate = date('Y-m-d\TH:i:s\Z', strtotime($Start_date));
			$endDAte = date('Y-m-d\TH:i:s\Z', strtotime($End_date));
			$calanderTitle = rawurlencode($post_title);
			$calanderBody = rawurlencode('Save The Date For');
			
// 			$Applesubject = urlencode($post_title);
// 			$Applebody = urlencode('Save The Date For');
// 			if ($Apple_Meta == '') {
// 				$Apple_Meta = 'data:text/calendar;charset=utf8,BEGIN:VCALENDAR%0AVERSION:2.0%0ABEGIN:VEVENT%0ASUMMARY:Your%20Event%20Title%0ADESCRIPTION:Your%20Event%20Description%0ALOCATION:Your%20Event%20Location%0ADTSTART:' . $startDate . '%0ADTEND:' . $endDAte . '%0AEND:VEVENT%0AEND:VCALENDAR';
// 			}

// 			$appleCalendarLink = $Apple_Meta . '&subject=' . $Applesubject . '&body=' . $Applebody;
			//---------End---------
		
			$Date_TimeHTML = '';
			if($Start_date_Meta == $End_date_Meta || $End_date_Meta == '' ){
				$Date_TimeHTML = '<div class="date-info">
       			 <span class="year-info">'.$start_day_name.'</span>
				<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).','.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
                 <span class="time-event">'.$time_event.'</span>
				 <span class="locations">'.$Location_event.'</span>
								</div>';
			}else{
				$start_year = date_i18n( "Y", $Start_date_Meta );
				$end_year = date_i18n( "Y", $End_date_Meta );
				if($start_year == $end_year){
					$Date_TimeHTML = '<div class="date-info">
        			<span class="year-info">'.$final_name.'</span>
										<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.date_i18n( "Y", $Start_date_Meta ).'</strong></span>
										<span class="year-info"></span>
                    <span class="time-event">'.$time_event.'</span>
					<span class="locations">'.$Location_event.'</span>

									</div>';
				}else{
					$Date_TimeHTML = '<div class="date-info">
                     <span class="year-info">'.$final_name.'</span>
										<span class="date_info"><strong>'.date_i18n( "M d", $Start_date_Meta ).' to '.date_i18n( "M d", $End_date_Meta ).', '.$start_year.'-'.$end_year.'</strong></span>
                     <span class="time-event">'.$time_event.'</span>
					 <span class="locations">'.$Location_event.'</span>

									</div>';
				}
			}
			
			$terms = get_the_terms($post_id, 'event-category');
				$customecatcode = '';
				foreach ($terms as $term) {
						$category = $term->name;
					$customecatcode .='	<li class="'.$category.'">
										<img src="'.$img.'" alt="">
										<span>'.$category.'</span>
					  </li>';
				}
			$finalhtml .='<div class="academic-calendar-card-items-body-item" data-start="'.$Start_date.'" data-end="'.$End_date.'">
					<div class="event-info">
						<a class="head_title '.$addnewclass.'" href="'.$url_post.'"><h3>'.$post_title.'</h3></a>
						<p class="event-desc">'.$post_content.'</p>
						<ul class="selected-academic-calendar-date-types">
						'.$customecatcode.'
                <li class="click-here '.$add_reg.'">
								<span class="custompopup" data-id='.$post_id.'>'.$newregisterlinkpopup.'</span>
							</li>
						</ul>
						<div class="share-icons">
							<a href="https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt='.$startDate.'&enddt='.$endDAte.'&subject='.$calanderTitle.'&body='.$calanderBody.' '.$calanderTitle.'">
					<span>Microsoft Calendar <i class="fa fa-windows"></i></span>
							</a>
							<a href="mailto:'.$Event_link_Meta.'?subject='.$finalSubaject.'&body='.$email_body_encoded.'">
								<span>Share <i class="fa fa-envelope"></i></span>
							</a>
						</div>
					</div>
						'.$Date_TimeHTML.'
						
				</div>';
		}
		wp_reset_postdata();
		}else{
			$finalhtml = '<div class="notfound_post"><p>No events found</p></div>';
		}
		echo $finalhtml;
		die();
}




add_action( 'wp_head', function () { ?>

<script>
	jQuery(document).ready(function( $ ){	
		
		jQuery('.current_events').click(function(){
			//console.log('current');
		 let mythis = jQuery(this);
		 let checkClass = jQuery('.acadmic-cat .type-1');
		   checkClass.each(function(){
			   if(jQuery(this).hasClass('active')){
				    jQuery(".clear-filter-btn").trigger("click");
			   }
		  });
		 let dataattr = mythis.data('current');
			//console.log(dataattr);
		 if(mythis.hasClass('active')) return;
		 	mythis.addClass('active');
			//jQuery('.upcoming_events').removeClass('active');
			jQuery('.old_events').removeClass('active');
			jQuery('.calender-button_new').removeClass('active');
			jQuery('#calendar_new').css('display', 'none');
            jQuery('#calendar_new').removeClass('active-now');
			 jQuery('#event-container').css('display', 'block');
		
		jQuery.ajax({
			type : "POST",
			url :  "/wp-admin/admin-ajax.php",
			data : {action:"get_upcomin_events_post" ,dataattr:dataattr },
			success : function(response) { 
				
				jQuery('#event-container').html(response);
				
			}
		});
		
		
	});
	
	 jQuery('.old_events').click(function(){
		 let mythis = jQuery(this);
		 let checkClass = jQuery('.acadmic-cat .type-1');
		   checkClass.each(function(){
			   if(jQuery(this).hasClass('active')){
				    jQuery(".clear-filter-btn").trigger("click");
			   }
		  });
		 let dataattr = mythis.data('old');
		 if(mythis.hasClass('active')) return;
		 	mythis.addClass('active');
			//jQuery('.upcoming_events').removeClass('active');
			jQuery('.current_events').removeClass('active');
		 	jQuery('.calender-button_new').removeClass('active');
		 	jQuery('#calendar_new').css('display', 'none');
            jQuery('#calendar_new').removeClass('active-now');
		  	jQuery('#event-container').css('display', 'block');
		jQuery.ajax({
			type : "POST",
			url :  "/wp-admin/admin-ajax.php",
			data : {action:"get_upcomin_events_post" ,dataattr:dataattr },
			success : function(response) { 
				
				jQuery('#event-container').html(response);
				
			}
		});
		
		
	});
	jQuery(document).on('click', '.type-1', function(event){
		event.preventDefault();
		if(jQuery(this).hasClass('active')) return
			jQuery('.type-1').removeClass('active');
			jQuery(this).addClass('active');
		let CatID = jQuery(this).attr('data-id');
		let eventbtn = jQuery('.events_btn');
		let eachBtn = eventbtn.find('button');
		eachBtn.each(function(){
			if(jQuery(this).hasClass('active')){
				console.log(CatID);
				let eventsTitle = jQuery(this).attr('data-title');
				jQuery.ajax({
					type : "POST",
					url :  "/wp-admin/admin-ajax.php",
					data : {action:"fillters_events_function" ,eventsTitle:eventsTitle,  CatID:CatID},
					success : function(response) { 
						jQuery('#event-container').html(response);

					}
				});
			}
		})
		
	});
	jQuery(document).on('click', '.clear-filter-btn', function(event){
		event.preventDefault();
		if(jQuery('.current_events').hasClass('active')){
		}else{
			jQuery('.current_events').addClass('active');
			//jQuery('.upcoming_events').removeClass('active');
			jQuery('.old_events').removeClass('active');
			jQuery('.calender-button_new').removeClass('active');
		 	jQuery('#calendar_new').css('display', 'none');
            jQuery('#calendar_new').removeClass('active-now');
		  	jQuery('#event-container').css('display', 'block');
		}
		
		jQuery.ajax({
			type : "POST",
			url :  "/wp-admin/admin-ajax.php",
			data : {action:"get_upcomin_events_post", dataattr:'current'
				   },
			success : function(response) { 
				jQuery('#event-container').html(response);
				jQuery('.type-1').removeClass('active');
				
			}
		});
		
	});
// 	jQuery('.calender-button_new').click(function(){
// 		if(jQuery('#calendar_new').hasClass('active-now')){
// 			jQuery(this).addClass('active');
// 			jQuery('.old_events').removeClass('active');
// 			jQuery('.current_events').removeClass('active');
// 		}
// 		else{
// // 			jQuery(this).removeClass('active');
// // 			jQuery('.old_events').removeClass('active');
// // 			jQuery('.current_events').addClass('active');
// 		}
// 	});
	
	jQuery(document).on('click', '.no_popupthis', function(e){
		//console.log('clicked');
		e.preventDefault();
		
	});
		
});

</script>
<script>
	jQuery(document).ready(function($) {
  
    function fetchAllEventsCalendar() {
        $.ajax({
            url: ajax_params.ajax_url,
            type: 'GET',
            data: {
                action: 'get_all_events_calendar_function',
            },
            success: function(response) {
                console.log('All events:', response);
                if (response.success) {
                    var events = response.data;
                    $('#calendar_new').fullCalendar('removeEvents');
                    $('#calendar_new').fullCalendar('renderEvents', events, true);
                } else {
                    console.log('Error fetching events:', response.data);
                }
            },
            error: function(error) {
                console.log('AJAX error:', error);
            }
        });
    }

    $('#calendar_new').fullCalendar({
		minTime: '00:00',
   		maxTime: '24:00',
//        eventRender: function(event, element) {
// 		   //console.log(element);
//             element.qtip({
//                 content: event.description
//             });
//         },
        eventClick: function(event) {
			//console.log('hello');
			 //console.log(event);
            openPopup(event); 
        }
    });

    jQuery(document).on('click', '.calender-button_new', function(){
        if (jQuery('#calendar_new').hasClass('active-now')) {
			jQuery(this).removeClass('active');
			jQuery('.current_events').addClass('active');
            jQuery('#event-container').css('display', 'block');
            jQuery('#calendar_new').css('display', 'none');
            jQuery('#calendar_new').removeClass('active-now');
        } else {
            fetchAllEventsCalendar();
			jQuery(this).addClass('active');
 			jQuery('.old_events').removeClass('active');
 			jQuery('.current_events').removeClass('active');
            jQuery('#event-container').css('display', 'none');
            jQuery('#calendar_new').css('display', 'block');
            jQuery('#calendar_new').addClass('active-now');
        }
    });
	


var popup = document.getElementById('eventPopup');

var closePopup = document.getElementById('closePopup');

closePopup.addEventListener('click', function() {
	popup.style.display = 'none';
	document.body.classList.remove('popup-open');
});



// Function to format date to DD/MM/YY
function formatDate(date) {
    var day = date.getDate();
    var month = date.getMonth() + 1; 
    var year = date.getFullYear().toString().slice(-2); 

    day = day < 10 ? '0' + day : day;
    month = month < 10 ? '0' + month : month;

    return day + '/' + month + '/' + year;
}
function parseDate(dateStr) {
    var year = dateStr.slice(0, 4);
    var month = dateStr.slice(4, 6) - 1; 
    var day = dateStr.slice(6, 8);
    return new Date(year, month, day);
}
function parseEndDate(dateStr) {
    var year = dateStr.slice(0, 4);
    var month = dateStr.slice(4, 6);
    var day = dateStr.slice(6, 8);
    var isoDateStr = year + '-' + month + '-' + day;
    return new Date(isoDateStr);
}
function formatFullDate(date) {
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
function formatShortMonthDay(date) {
    return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit' });
}
function formatShortMonthDayWithDay(date) {
    var options = { weekday: 'short', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

		
// Function to set popup links
function setPopupLinks(startDate, endDate, calendarTitle, calendarBody, eventLinkMeta, finalSubject, emailBodyEncoded) {
    var calendarLink = document.getElementById('calendarLink');
    var emailLink = document.getElementById('emailLink');

    calendarLink.href ='https://outlook.live.com/calendar/deeplink/compose?path=/calendar/action/compose&rru=addevent&startdt=' + startDate + '&enddt=' + endDate + '&subject=' + calendarTitle + '&body=' + calendarBody + ' ' + calendarTitle;
    emailLink.href = 'mailto:' + eventLinkMeta + '?subject=' + finalSubject + '&body=' + emailBodyEncoded;
}	
		
function openPopup(event) {
    var title = event.title;
    var start = event.start ? formatDate(event.start.toDate()) : '';
    var end = event.end ? formatDate(event.end.toDate()) : '';
	var originalEnd = event.original_end ? formatDate(parseDate(event.original_end)) : '';
    //var description = event.description;
	var fullDate = event.start ? formatFullDate(event.start.toDate()) : '';
	
	//new date format for dates
	var shortStart = event.start ? formatShortMonthDay(event.start.toDate()) : '';
    var shortEnd = event.original_end ? formatShortMonthDay(parseDate(event.original_end)) : '';
    var shortDateRange = shortStart && shortEnd ? `${shortStart} to ${shortEnd}` : '';
	
	//Get days name from start and end date
	var shortStartWithDay = event.start ? formatShortMonthDayWithDay(event.start.toDate()) : '';
	var shortEndWithDay = event.original_end ? formatShortMonthDayWithDay(parseDate(event.original_end)) : '';
	var shortDayRange = shortStartWithDay && shortEndWithDay ? `${shortStartWithDay} to ${shortEndWithDay}` : '';
	//for the calendar links
	var startDate = event.start ? event.start.toDate().toISOString() : '';
   	var endDate = event.original_end ? parseEndDate(event.original_end).toISOString() : '';
    var calendarTitle = encodeURIComponent(title);
    var calendarBody = encodeURIComponent('Save The Date For ' + title);
	var eventLinkMeta = event.event_link_meta ? encodeURIComponent(event.event_link_meta) : '';
    var finalSubject = encodeURIComponent('Reply to: ' + title);
	var Back_to_events = 'https://humber.ca/innovativelearning/programs-and-events/';
	
	// Construct the email body
    var emailBody = `Save The Date on ${shortStart}\n`;
    if (title) {
        emailBody += `${title}\n`;
    }
    if (eventLinkMeta) {
        emailBody += `${eventLinkMeta}\n`;
    }
    if (description) {
        emailBody += `${description}\n`;
    }
    //emailBody += `Back To Events : ${Back_to_events}\n`;
    var emailBodyEncoded = encodeURIComponent(emailBody);

    document.getElementById('popupTitle').innerText = title;
    document.getElementById('popupStartEnd').innerText = shortDateRange;
	document.getElementById('popupstartdate').innerText = fullDate;
	document.getElementById('popupEventDay').innerText = shortDayRange;
 	var description = event.description;
    if (description) {
        document.getElementById('popupDescription').innerText = description;
		//document.getElementById('popupDescription').style.display = 'block';
    } else {
        document.getElementById('popupDescription').style.display = 'none';
    }
	var locationEvent = event.location_event ? event.location_event : '';
	if (locationEvent) {
        document.getElementById('popupLocation').innerText = locationEvent;
		//document.getElementById('popupLocation').style.display = 'block';
    } else {
        document.getElementById('popupLocation').style.display = 'none';
    }
    var timeEvent = event.time_event ? event.time_event : '';
	if (timeEvent) {
        document.getElementById('popupEventTime').innerText = timeEvent;
        //document.getElementById('popupEventTime').style.display = 'block';
    } else {
        document.getElementById('popupEventTime').style.display = 'none';
    }
	setPopupLinks(startDate, endDate, calendarTitle, calendarBody, eventLinkMeta, finalSubject, emailBodyEncoded);
	
    // Show the popup
    popup.style.display = 'block';
	document.body.classList.add('popup-open');
	$('#popupContainer').addClass('popup-active');
}

		
// Function to handle clicks outside the popup
$(document).on('click', function(event) {
    if ($('body').hasClass('popup-open') && $('#popupContainer').hasClass('popup-active')) {
      	//console.log('both');
         if (!$(event.target).closest('#popupContainer').length && !$(event.target).closest('.fc-event').length) {
           // console.log('contained');
            $('#eventPopup').hide();
            $('body').removeClass('popup-open');
            $('#popupContainer').removeClass('popup-active');
        }
		
    }
});

});

</script>
<style>

.academic-calendar-card-items-body-item {
    display: flex;
    flex-flow: row wrap;
    justify-content: space-between;
    box-shadow: 5px 3px 13px 6px rgb(0 0 0 / 6%);
    border-radius: 10px;
}
.academic-calendar-card-items-body-item .date-info {
    display: flex;
    flex-flow: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    padding: 15px;
    flex: 0 1 230px;
    text-align: center;
}
.academic-calendar-card-items-body-item .event-info {
    padding: 15px;
    flex: 2;
    border-right: 1px solid #EDF0F7;
    display: flex;
    flex-flow: column;
    gap: 8px;
}
.academic-calendar-card-items-body {
    display: flex;
    flex-flow: column;
    gap: 20px;
}
.selected-academic-calendar-date-types li {
    border-radius: 4px;
    padding: 5px;
    display: flex;
    flex-flow: row;
    align-items: center;
    gap: 5px;
    font-weight: bold;
}
.selected-academic-calendar-date-types {
    display: flex;
    flex-flow: row wrap;
    margin: 0;
    padding: 0;
    list-style: none;
    gap: 8px;
}
.selected-academic-calendar-date-types li.Tag{
    background: #B4C800;
    color: #000;
}
.selected-academic-calendar-date-types img {
    width: 20px !important;
}
.popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 20px;
    z-index: 1000; 
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); 
    max-width: 80%;
}
#popupContainer {
    position: relative;
}
.popup-content {
    text-align: center;
}

.popup-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 999;
}
.close_calender {
    position: absolute;
    top: -18px;
    right: -6px;
    font-weight: bold;
    font-size: 20px;
    cursor: pointer;
    color: gray;
}

body.popup-open {
    overflow: hidden;
	background-color: rgba(0, 0, 0, 0.5);
}
hr.popup_hr {
    margin-top: -10px;
    border: 2px solid #0390f3;
}

div#popupTitle {
    text-align: start;
	margin-top: 24px;
 	font-weight: bold; 
}

p#popupStartEnd {
    text-align: start;
/*     margin-top: 10px; */
}

div#popup_links {
    text-align: start;
}
h2#popupstartdate {
    text-align: start;
}
p#popupDescription, p#popupLocation, p#popupEventTime, p#popupEventDay {
    text-align: start;
	line-height: 1;
}
.events_data {
    margin-top: 4px;
}
</style>
<?php } );

?>
<?php
