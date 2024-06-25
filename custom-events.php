<?php
/**
 * Plugin Name: Custom Events
 * Description: Allows users to add custom events using a shortcode.
 * Version: 1.0
 * Author: Komal Arora
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
		
		$finalhtml .='<div class="academic-calendar-card-items-body-item '.$post_id.'" data-start="'.$Start_date.'" data-end="'.$End_date.'">
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
    </div> 
	<div>
		<form  method="get" id="searchform">
			<input type="text" value="" name="s" id="s" placeholder="Search for the Events" />
			<input type="button" id="searchsubmit" value="Search" />
    	</form>
	</div>
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
	$finalhtml .= '<div class="academic-calendar-card-items-body  '.$post_id.'" >';
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
				if (($End_date < $cur_date || $Start_date < $cur_date && $End_date > $cur_date) && $post_id !== 41508)  {
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
			$finalhtml .='<div class="academic-calendar-card-items-body-item  '.$post_id.'" data-id='.$post_id.' data-start="'.$Start_date.'" data-end="'.$End_date.'">
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

    $finalhtml .= '<div class="academic-calendar-card-items-body  '.$post_id.'">';
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
				if( ($End_date < $cur_date || $Start_date < $cur_date && $End_date > $cur_date) && $post_id !== 41508) {
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
			$finalhtml .='<div class="academic-calendar-card-items-body-item  '.$post_id.'" data-start="'.$Start_date.'" data-end="'.$End_date.'">
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
	$finalhtml .= '<div class="academic-calendar-card-items-body  '.$post_id.'">';
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
			$finalhtml .='<div class="academic-calendar-card-items-body-item  '.$post_id.'" data-start="'.$Start_date.'" data-end="'.$End_date.'">
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
		
		jQuery.ajax({
			type : "POST",
			url :  "https://humber.ca/innovativelearning/wp-admin/admin-ajax.php",
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
		jQuery.ajax({
			type : "POST",
			url :  "https://humber.ca/innovativelearning/wp-admin/admin-ajax.php",
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
					url :  "https://humber.ca/innovativelearning/wp-admin/admin-ajax.php",
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
		}
		
		jQuery.ajax({
			type : "POST",
			url :  "https://humber.ca/innovativelearning/wp-admin/admin-ajax.php",
			data : {action:"get_upcomin_events_post", dataattr:'current'
				   },
			success : function(response) { 
				jQuery('#event-container').html(response);
				jQuery('.type-1').removeClass('active');
				
			}
		});
		
	});
	
	jQuery(document).on('click', '.no_popupthis', function(e){
		//console.log('clicked');
		e.preventDefault();
		
	});

	// Jquery for search funactionality

	$('#searchsubmit').on('click', function(e) {
		// let mythis = jQuery(this);
		 let checkClass = jQuery('.acadmic-cat .type-1');
		   checkClass.each(function(){
			   if(jQuery(this).hasClass('active')){
				    jQuery(".clear-filter-btn").trigger("click");
			   }
		  });
		
			jQuery('.old_events').removeClass('active');
			jQuery('.current_events').removeClass('active');
		
        var searchQuery = $('#s').val().trim(); 
		console.log(searchQuery);
        if (searchQuery !== '') {
            $.ajax({
                url: 'https://humber.ca/innovativelearning/wp-admin/admin-ajax.php', 
                type: 'POST',
                data: {
                    action: 'search_events_function', 
                    searchQuery: searchQuery, 
                },
                success: function(response) {
					jQuery('#event-container').html(response);
					jQuery('#s').val('')
                    //console.log(response); 
                   
                },
                error: function(xhr, status, error) {
                   // console.error(xhr.responseText); 
                }
            });
        }
    });
    jQuery('#s').keypress(function(e){
		if(e.keyCode === 13){
			e.preventDefault();
			jQuery('#searchsubmit').click();
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
</style>
<?php } );

?>
<?php
