<?php
/**
 * Plugin Name: Valkyrie
 * Plugin URI: https://github.com/pixelwatt/valkyrie
 * Description: This plugin integrates with CMB2 roadway segments and CMB2 to create backed GeoJSON and JSON data for use with multiple frontends.
 * Version: 0.9.0
 * Author: Rob Clark
 * Author URI: https://robclark.io
 */

 // Include utility class

require_once('class-valkyrie-utility.php');


// Required plugins

require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'valkyrie_register_required_plugins' );

function valkyrie_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(
		array(
			'name'      => 'CMB2',
			'slug'      => 'cmb2',
			'required'  => true,
		),
		array(
			'name'      => 'CMB2 Roadway Segments',
			'slug'      => 'cmb2-roadway-segments',
			'source'    => 'https://github.com/pixelwatt/cmb2-roadway-segments/archive/master.zip',
			'required'  => true,
		),
		array(
			'name'      => 'CMB2 Field Type: Ajax Search',
			'slug'      => 'cmb2-field-ajax-search',
			'source'    => 'https://github.com/rubengc/cmb2-field-ajax-search/archive/refs/heads/master.zip',
			'required'  => true,
		),
	);

	
	$config = array(
		'id'           => 'valkyrie',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	);

	tgmpa( $plugins, $config );
}


//======================================================================
// CUSTOM POST TYPES
//======================================================================

function valkyrie_resource_init() {
    $labels = array(
        'name'               => __( 'Resources', 'valkyrie' ),
        'singular_name'      => __( 'Resource', 'valkyrie' ),
        'menu_name'          => __( 'Resources', 'valkyrie' ),
        'name_admin_bar'     => __( 'Resource', 'valkyrie' ),
        'add_new'            => __( 'Add New', 'valkyrie' ),
        'add_new_item'       => __( 'Add New Resource', 'valkyrie' ),
        'new_item'           => __( 'New Resource', 'valkyrie' ),
        'edit_item'          => __( 'Edit Resource', 'valkyrie' ),
        'view_item'          => __( 'View Resource', 'valkyrie' ),
        'all_items'          => __( 'All Resources', 'valkyrie' ),
        'search_items'       => __( 'Search Resources', 'valkyrie' ),
        'parent_item_colon'  => __( 'Parent Resources:', 'valkyrie' ),
        'not_found'          => __( 'No resources found.', 'valkyrie' ),
        'not_found_in_trash' => __( 'No resources found in Trash.', 'valkyrie' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'resource' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail' )
    );

    register_post_type( 'valkyrie_resource', $args );
}
add_action( 'init', 'valkyrie_resource_init' );


//======================================================================
// CUSTOM TAXONOMIES
//======================================================================

function valkyrie_resourcetype_init() {
    $labels = array(
        'name'                       => _x( 'Resource Types', 'taxonomy general name', 'valkyrie' ),
        'singular_name'              => _x( 'Resource Type', 'taxonomy singular name', 'valkyrie' ),
        'search_items'               => __( 'Search Resource Types', 'valkyrie' ),
        'popular_items'              => __( 'Popular Resource Types', 'valkyrie' ),
        'all_items'                  => __( 'All Resource Types', 'valkyrie' ),
        'parent_item'                => __( 'Parent Resource Type', 'valkyrie' ),
        'parent_item_colon'          => __( 'Parent Resource Type:', 'valkyrie' ),
        'edit_item'                  => __( 'Edit Resource Type', 'valkyrie' ),
        'update_item'                => __( 'Update Resource Type', 'valkyrie' ),
        'add_new_item'               => __( 'Add New Resource Type', 'valkyrie' ),
        'new_item_name'              => __( 'New Resource Type Name', 'valkyrie' ),
        'separate_items_with_commas' => __( 'Separate resource types with commas', 'valkyrie' ),
        'add_or_remove_items'        => __( 'Add or remove resource types', 'valkyrie' ),
        'choose_from_most_used'      => __( 'Choose from the most used resource types', 'valkyrie' ),
        'not_found'                  => __( 'No resource types found.', 'valkyrie' ),
        'menu_name'                  => __( 'Resource Types', 'valkyrie' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
    );

    register_taxonomy( 'valkyrie_resoucetype', apply_filters( 'valkyrie_resource_post_type', array( 'valkyrie_resource' ) ), $args );
}
add_action( 'init', 'valkyrie_resourcetype_init' );


function valkyrie_feature_init() {
    $labels = array(
        'name'                       => _x( 'Features', 'taxonomy general name', 'valkyrie' ),
        'singular_name'              => _x( 'Feature', 'taxonomy singular name', 'valkyrie' ),
        'search_items'               => __( 'Search Features', 'valkyrie' ),
        'popular_items'              => __( 'Popular Features', 'valkyrie' ),
        'all_items'                  => __( 'All Features', 'valkyrie' ),
        'parent_item'                => __( 'Parent Feature', 'valkyrie' ),
        'parent_item_colon'          => __( 'Parent Feature:', 'valkyrie' ),
        'edit_item'                  => __( 'Edit Feature', 'valkyrie' ),
        'update_item'                => __( 'Update Feature', 'valkyrie' ),
        'add_new_item'               => __( 'Add New Feature', 'valkyrie' ),
        'new_item_name'              => __( 'New Feature Name', 'valkyrie' ),
        'separate_items_with_commas' => __( 'Separate Features with commas', 'valkyrie' ),
        'add_or_remove_items'        => __( 'Add or remove features', 'valkyrie' ),
        'choose_from_most_used'      => __( 'Choose from the most used features', 'valkyrie' ),
        'not_found'                  => __( 'No features found.', 'valkyrie' ),
        'menu_name'                  => __( 'Features', 'valkyrie' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
    );

    register_taxonomy( 'valkyrie_feature', apply_filters( 'valkyrie_resource_post_type', array( 'valkyrie_resource' ) ), $args );
}
add_action( 'init', 'valkyrie_feature_init' );


// Add custom metabox

add_action( 'cmb2_admin_init', 'valkyrie_register_resource_metabox' );

function valkyrie_register_resource_metabox() {
	$opts = new_cmb2_box(
		apply_filters(
			'valkyrie_metabox_resource_args',
			array(
				'id'            => '_valkyrie_metabox_resource',
				'title'         => esc_html__( 'Entity Data', 'valkyrie' ),
				'object_types'  => apply_filters( 'valkyrie_resource_post_type', array( 'valkyrie_resource' ) ),
			),
		),
	);

	$opts->add_field(
		array(
			'name'     => __( '24/7 Assistance', 'athena' ),
			'desc'     => __( 'Check this box if some or all of this resource\'s services are always available.', 'athena' ),
			'id'   => apply_filters( 'valkyrie_resource_meta_prefix', '_valkyrie_resource_' ) . '247',
			'type'     => 'checkbox',
		)
	);

	$opts->add_field( array(
		'name'          => __( '24/7 Coverage', 'cmb2' ),
		'desc'          => __( 'Please select the specific services that are always available.<br><strong>Important: Terms specified above must also be assigned to this resource in the "Resource Type" metabox in the right sidebar. Otherwise, their selection here will have no effect.</strong>', 'cmb2' ),
		'id'            => apply_filters( 'valkyrie_resource_meta_prefix', '_valkyrie_resource_' ) . 'terms',
		'type'          => 'term_ajax_search',
		'multiple-item' => true,
		'limit'      	=> -1,
		'query_args'	=> array(
			'taxonomy'			=> 'valkyrie_resoucetype',
			'hide_empty'		=> false
		)
	) );

	// Data Entry Metabox
	$group_field_data = $opts->add_field(
		array(
			'id'          => apply_filters( 'valkyrie_resource_meta_prefix', '_valkyrie_resource_' ) . 'datafields',
			'type'        => 'group',
			'description' => __( 'Configure data fields for this resource below.', 'valkyrie' ),
			'options'     => array(
				'group_title'       => __( 'Data Field {#}', 'valkyrie' ), // since version 1.1.4, {#} gets replaced by row number
				'add_button'        => __( 'Add Another Data Field', 'valkyrie' ),
				'remove_button'     => __( 'Remove Data Field', 'valkyrie' ),
				'sortable'          => true,
				'closed'         => false, // true to have the groups closed by default
				// 'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'valkyrie' ), // Performs confirmation before removing group.
			),
		)
	);

	$opts->add_group_field(
		$group_field_data,
		array(
			'name' => 'Data Type',
			'id'   => 'type',
			'type' => 'select',
			'default' => 'spacer',
			'desc' => __( 'What type of data will this field hold?', 'valkyrie' ),
			'options' => array(
				'spacer' => esc_attr__( 'Spacer', 'valkyrie' ),
				'headline' => esc_attr__( 'Headline', 'valkyrie' ),
				'wysiwyg' => esc_attr__( 'Rich Content', 'valkyrie' ),
				'daterange' => esc_attr__( 'Date/Time Range', 'valkyrie' ),
				'address' => esc_attr__( 'Address', 'valkyrie' ),
				'poc' => esc_attr__( 'Point of Contact', 'valkyrie' ),
			),
		)
	);
	

}