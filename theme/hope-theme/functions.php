<?php

/* register sitewide custom taxonomonies */ 
add_action('init', 'hcc_create_campus_taxonomy');

/* enqueue the styles for certain sections of the theme */
add_action('wp_enqueue_scripts', 'load_hcc_styles');

function hcc_create_campus_taxonomy() {

	$campus_tax = register_taxonomy('hcc_campus',null,
		array(
			'public'=>true,
			'name'=>'Hope Community Church Campus',
			'description'=>'These are the campuses at Hope Community Church',
			'label'=>'Campus'
		)
	);

	register_taxonomy_for_object_type('hcc_campus','hcc_serving_opp');
	register_taxonomy_for_object_type('hcc_campus','hcc_community_opp');
	register_taxonomy_for_object_type('hcc_campus','hcc_global_opp');


}


function load_hcc_styles() {

	global $post;
	//load special styles for the serve and message notes pages
	if (strstr(get_the_permalink($post->ID), 'serve/')) {
				
		wp_enqueue_style('hcc-serve-in-the-church-styles', get_stylesheet_directory_uri() . '/css/hope-serve-styles.css','front-all');		
		wp_enqueue_script('hcc-serve-in-the-church-script', get_stylesheet_directory_uri() . '/js/hope-serve-scripts.js');
		wp_localize_script( 'hcc-serve-in-the-church-script', 'ajaxVars', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' )));
		//enqueue the parent styles
	}

	wp_enqueue_style('hcc-child-styles', get_stylesheet_directory_uri() . '/style.css');


}