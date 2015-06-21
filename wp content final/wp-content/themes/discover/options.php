<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 * 
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet (lowercase and without spaces)
	$themename = wp_get_theme(get_stylesheet_directory() . '/style.css');
	$themename = $themename['Name'];
	$themename = preg_replace("/\W/", "", strtolower($themename) );
	$themename = 'discover';
	
	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = $themename;
	update_option('optionsframework', $optionsframework_settings);
	
	// echo $themename;
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the "id" fields, make sure to use all lowercase and no spaces.
 *  
 */

function optionsframework_options() {
	
	// Multicheck array
	$slider_caption_array = array("on" => "on","off" => "off");
	$wel_button_array = array("on" => "on","off" => "off");	
	
	
	// Pull all the categories into an array
	$options_categories = array();  
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
    	$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	// Pull all the pages into an array
	$options_pages = array();  
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
    	$options_pages[$page->ID] = $page->post_title;
	}
		
	// If using image radio buttons, define a directory path
	$imagepath =  get_stylesheet_directory_uri() . '/images/';
		
	$options = array();
		
	$options[] = array( "name" => __('Homepage', 'discover' ),
						"type" => "heading");																															
						
	$options[] = array( "name" => __('Homepage Box 1 heading', 'discover' ),
						"desc" => __('Heading for homepage box1.', 'discover' ),
						"id" => "box_head1",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Homepage Box 1 text', 'discover' ),
						"desc" => __('Textarea for homepage box1.', 'discover' ),
						"id" => "box_text1",
						"std" => "",
						"type" => "textarea");
						
	$options[] = array( "name" => __('Homepage Box 1 thumbnail image', 'discover' ),
						"desc" => __('215px x 130px exact. Upload your image for homepage box 1.', 'discover' ),						
						"id" => "box_image1",
						"type" => "upload");						
						
	$options[] = array( "name" => __('Homepage Box 1 link', 'discover' ),	
						"desc" => __('Paste here the link of the page or post.', 'discover' ),						
						"id" => "box_link1",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Homepage Box 2 heading', 'discover' ),
						"desc" => __('Heading for homepage box2.', 'discover' ),
						"id" => "box_head2",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Homepage Box 2 text', 'discover' ),
						"desc" => __('Textarea for homepage box2.', 'discover' ),
						"id" => "box_text2",
						"std" => "",
						"type" => "textarea");
						
	$options[] = array( "name" => __('Homepage Box 2 thumbnail image', 'discover' ),
						"desc" => __('215px x 130px exact. Upload your image for homepage box 2.', 'discover' ),
						"id" => "box_image2",
						"type" => "upload");						
						
	$options[] = array( "name" => __('Homepage Box 2 link', 'discover' ),
						"desc" => __('Paste here the link of the page or post.', 'discover' ),
						"id" => "box_link2",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Homepage Box 3 heading', 'discover' ),
						"desc" => __('Heading for homepage box3.', 'discover' ),
						"id" => "box_head3",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Homepage Box 3 text', 'discover' ),
						"desc" => __('Textarea for homepage box3.', 'discover' ),
						"id" => "box_text3",
						"std" => "",
						"type" => "textarea");
						
	$options[] = array( "name" => __('Homepage Box 3 thumbnail image', 'discover' ),
						"desc" => __('215px x 130px exact. Upload your image for homepage box 3.', 'discover' ),
						"id" => "box_image3",
						"type" => "upload");						
						
	$options[] = array( "name" => __('Homepage Box 3 link', 'discover' ),
						"desc" => __('Paste here the link of the page or post.', 'discover' ),
						"id" => "box_link3",
						"std" => "",
						"type" => "text");	
						
	$options[] = array( "name" => __('Homepage Box 4 heading', 'discover' ),
						"desc" => __('Heading for homepage box4.', 'discover' ),
						"id" => "box_head4",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Homepage Box 4 text', 'discover' ),
						"desc" => __('Textarea for homepage box4.', 'discover' ),
						"id" => "box_text4",
						"std" => "",
						"type" => "textarea");
						
	$options[] = array( "name" => __('Homepage Box 4 thumbnail image', 'discover' ),
						"desc" => __('215px x 130px exact. Upload your image for homepage box 4.', 'discover' ),
						"id" => "box_image4",
						"type" => "upload");						
						
	$options[] = array( "name" => __('Homepage Box 4 link', 'discover' ),
						"desc" => __('Paste here the link of the page or post.', 'discover' ),
						"id" => "box_link4",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Color', 'discover' ),
						"type" => "heading");					
						
	$options[] = array( "name" => __('Theme Border Top Color', 'discover' ),
						"desc" => __('Default used if no color is selected.', 'discover' ),						
						"id" => "tb_color",
						"std" => "#fd7800",
						"type" => "color");
						
	$options[] = array( "name" => __('Homepage Button Color', 'discover' ),
						"desc" => __('Default used if no color is selected.', 'discover' ),						
						"id" => "hbut_color",
						"std" => "#fd7800",
						"type" => "color");												
						
	$options[] = array( "name" => __('Homepage Button Hover Color', 'discover' ),	
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "hbuthov_color",
						"std" => "#ce6200",
						"type" => "color");
					
	$options[] = array( "name" => __('Menu li Hover Color', 'discover' ),
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "mli_color",
						"std" => "#444444",
						"type" => "color");																								
						
	$options[] = array( "name" => __('Menu li ul li Hover Color', 'discover' ),	
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "mliul_color",
						"std" => "#fd7800",
						"type" => "color");																														
						
	$options[] = array( "name" => __('Link Color', 'discover' ),	
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "link_color",
						"std" => "#fd7800",
						"type" => "color");
						
	$options[] = array( "name" => __('Sidebar Widget li link Color', 'discover' ),		
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "side_link_color",
						"std" => "#fd7800",
						"type" => "color");
						
	$options[] = array( "name" => __('Footer Widget li link Color', 'discover' ),			
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "footer_link_color",
						"std" => "#fd7800",
						"type" => "color");
						
	$options[] = array( "name" => __('Blog Metadata link Color', 'discover' ),		
						"desc" => __('Default used if no color is selected.', 'discover' ),
						"id" => "bmeta_link_color",
						"std" => "#fd7800",
						"type" => "color");																																																																																																																																	
						
	$options[] = array( "name" => __('Slider' , 'discover' ),
						"type" => "heading");
						
	$options[] = array( "name" => __('Slider Button', 'discover' ),		
						"desc" => __('Select option to display welcome button of slider(download now).', 'discover' ),						
						"id" => "wel_button",
						"std" => "on",
						"type" => "select",
						"class" => "mini", //mini, tiny, small
						"options" => $wel_button_array);							
						
	$options[] = array( "name" => __('Slider Caption', 'discover' ),			
						"desc" => __('Select option to display caption of slider.', 'discover' ),											
						"id" => "slider_caption",
						"std" => "on",
						"type" => "select",
						"class" => "mini", //mini, tiny, small
						"options" => $slider_caption_array);						
						
	$options[] = array( "name" => __('Welcome headline', 'discover' ),			
						"desc" => __('Hompage slider welcome headline content.', 'discover' ),					
						"id" => "welcome_head",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => __('Welcome text', 'discover' ),			
						"desc" => __('Hompage slider welcome text content.', 'discover' ),						
						"id" => "welcome_text",
						"std" => "",
						"type" => "textarea");
						
	$options[] = array( "name" => __('Welcome Button text', 'discover' ),				
						"desc" => __('Hompage slider welcome button text.', 'discover' ),	
						"id" => "welcome_button",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Welcome Button link', 'discover' ),				
						"desc" => __('Hompage slider welcome button link.', 'discover' ),						
						"id" => "welcome_button_link",
						"std" => "",
						"type" => "text");						
						
	$options[] = array( "name" => __('Slider heading 1', 'discover' ),				
						"desc" => __('Heading for the slider.', 'discover' ),							
						"id" => "slider_head1",
						"std" => "",
						"type" => "text");			
						
	$options[] = array( "name" => __('Slider image 1', 'discover' ),				
						"desc" => __('637px x 298px minimum. Upload your image for homepage slider.', 'discover' ),							
						"id" => "slider_image1",
						"type" => "upload");
						
	$options[] = array( "name" => __('Slider image link', 'discover' ),				
						"desc" => __('Paste here the link of the page or post.', 'discover' ),						
						"id" => "slider_link1",
						"std" => "",
						"type" => "text");
						
	$options[] = array( "name" => __('Slider heading 2', 'discover' ),				
						"desc" => __('Heading for the slider.', 'discover' ),						
						"id" => "slider_head2",
						"std" => "",
						"type" => "text");						
						
	$options[] = array( "name" => __('Slider image 2', 'discover' ),				
						"desc" => __('637px x 298px minimum. Upload your image for homepage slider.', 'discover' ),	
						"id" => "slider_image2",
						"type" => "upload");
						
	$options[] = array( "name" => __('Slider image 2 link', 'discover' ),				
						"desc" => __('Paste here the link of the page or post.', 'discover' ),
						"id" => "slider_link2",
						"std" => "",
						"type" => "text");		
						
	$options[] = array( "name" => __('Favicon Settings' , 'discover' ),
						"type" => "heading");						
						
	$options[] = array( "name" => __('Favicon image', 'discover' ),				
						"desc" => __('Upload your favicon image over here or enter url.', 'discover' ),						
						"id" => "favicon_image",
						"type" => "upload");										
						
	$options[] = array( "name" => __('Footer' , 'discover' ),	
						"type" => "heading");
						
	$options[] = array( "name" => __('Footer copyright text', 'discover' ),				
						"desc" => __('Enter your company name here.', 'discover' ),						
						"id" => "footer_cr",
						"std" => "",
						"type" => "text");	
						
	$options[] = array( "name" => __('Google Analytics Code', 'discover' ),				
						"desc" => __('You can paste your Google Analytics or other tracking code in this box.', 'discover' ),							
						"id" => "go_code",
						"std" => "",
						"type" => "textarea");

	$options[] = array( "name" => __('Custom Style' , 'discover' ),		
						"type" => "heading");
						
	$options[] = array( "name" => __('Custom CSS', 'discover' ),				
						"desc" => __('Add css to modify the theme here instead of adding it to style.css file.', 'discover' ),							
						"id" => "custom_css",
						"std" => "",
						"type" => "textarea");						
															
	return $options;
}