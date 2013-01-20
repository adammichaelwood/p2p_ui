<?php
/*
Plugin Name: P2P UI
Plugin URI: https://github.com/adammichaelwood/p2p_ui
Description: Helps with Posts 2 Posts
Version: 0.0.1
Author: Adam Wood
Author URI: https://github.com/adammichaelwood
License: In Progress
*/

function p2pui_setup_maker_post_types() {	
	register_post_type( 'p2pui_datatype',
		array(
			'labels' => array(
				'name' => 'Data Types',
				'singular_name' => 'Data Type'
			),
		'public' => true,
		'has_archive' => true,
		'menu_position' => 101,
		'supports' => array('title', 'editor', 'custom-fields', 'excerpt', 'comments'),
		'register_meta_box_cb' => 'p2pui_datatype_meta',
		'hierarchical' => true,
		'taxonomies' => array( 'category', 'post_tag' )
		)
	);
	register_post_type( 'p2pui_connect_type',
		array(
			'labels' => array(
				'name' => 'Connections',
				'singular_name' => 'Connection'
			),
		'public' => true,
		'has_archive' => true,
		'menu_position' => 102,
		'supports' => array('title', 'editor', 'custom-fields')
		)
	);
}
add_action( 'init', 'p2pui_setup_maker_post_types', 9 ); 

function p2pui_setup_metabox() {	
    add_meta_box( 
        'p2pui_metabox',
        'Connection Information',  
        'p2pui_connection_meta_content',	
        'p2pui_connect_type'	
    );
}
function p2pui_connection_meta_content ( $post ) {
	wp_nonce_field( plugins_url( __FILE__ ), 'p2pui_connect_type_nonce' );	
	?>
	<label for="p2pui_connection_name">Connection Name</label><br/>
	<input type="text" id="p2pui_connection_name" name="p2pui_connection_name" value="<?php echo get_post_meta($post->ID, 'p2pui_connection_name', true); ?>"/><br/>

	<label for="p2pui_from_post_type">From Post Type</label><br/>
	<select id="p2pui_from_post_type" name="p2pui_from_post_type">
		<?php $post_types = get_post_types();
		$current_from_value = get_post_meta($post->ID, 'p2pui_from_post_type', true);
		foreach ( $post_types as $post_type ) :
			if ( $post_type == $current_from_value ) { ?>
				<option value="<?php echo $post_type; ?>" selected="selected"><?php echo $post_type; ?></option>
			<?php } else { ?>
				<option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
			<?php } 
		endforeach; ?>
	</select><br/>
	
	<label for="p2pui_to_post_type">To Post Type</label><br/>
	<select id="p2pui_to_post_type" name="p2pui_to_post_type">
		<?php 
		$current_to_value = get_post_meta($post->ID, 'p2pui_to_post_type', true);
		foreach ( $post_types as $post_type ) :
			if ( $post_type == $current_to_value ) { ?>
				<option value="<?php echo $post_type; ?>" selected="selected"><?php echo $post_type; ?></option>
			<?php } else { ?>
				<option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
			<?php } 
		endforeach; ?>
	</select><br/>
<?php	}
add_action( 'add_meta_boxes', 'p2pui_setup_metabox' );	//run the meta-box adding functions at just the right moment

function p2pui_datatype_meta() {	
    add_meta_box( 
        'p2pui_datatype_metabox',
        'Data Type Information',  
        'p2pui_datatype_meta_content',
        'p2pui_datatype',
		'side',
		'high'
    );
}
function p2pui_datatype_meta_content ( $post ) {
	wp_nonce_field( plugins_url( __FILE__ ), 'p2pui_datatype_nonce' );	// WP-nonce is used for security.
	?>
	<p>
	<label for="p2pui_datatype_name_single">Datatype Name Single</label><br/>
	<input type="text" id="p2pui_datatype_name_single" name="p2pui_datatype_name_single" value="<?php echo get_post_meta($post->ID, 'p2pui_datatype_name_single', true); ?>"/><br/>
	</p>
	<p>
	<label for="p2pui_datatype_name_plural">Datatype Name Plural</label><br/>
	<input type="text" id="p2pui_datatype_name_plural" name="p2pui_datatype_name_plural" value="<?php echo get_post_meta($post->ID, 'p2pui_datatype_name_plural', true); ?>"/><br/>
	</p>
	<p>
	Hierarchical?<br/>
	<label><input type="radio" class="input-radio" name="p2pui_datatype_hierarchical" value="1" <?php p2pui_datatype_is_hierarchical($post, 1); ?>>Yes </label><br/>
	<label><input type="radio" class="input-radio" name="p2pui_datatype_hierarchical" value="0" <?php p2pui_datatype_is_hierarchical($post, 0); ?>> No</label><br/>
	</p>
<?php	}

function p2pui_datatype_is_hierarchical($post, $hierarchical) {
	if ( get_post_meta($post->ID, 'p2pui_datatype_hierarchical', true) == $hierarchical ) {
		print 'checked="checked"';
	} else {
		return;
	}
}

function p2pui_save_datatype_meta($post_id, $post) {
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
       return;
	if('p2pui_datatype' == $_POST['post_type']) {
		if(!current_user_can('edit_page', $post_id))
           return;
		}
	else if(!current_user_can('edit_post', $post_id))
		return;
	if(isset($_POST['p2pui_datatype_nonce']) && wp_verify_nonce($_POST['p2pui_datatype_nonce'], plugins_url(__FILE__)) && check_admin_referer(plugins_url(__FILE__), 'p2pui_datatype_nonce')) {
		update_post_meta($post_id, 'p2pui_datatype_name_single', $_POST['p2pui_datatype_name_single']);
		update_post_meta($post_id, 'p2pui_datatype_name_plural', $_POST['p2pui_datatype_name_plural']);
		update_post_meta($post_id, 'p2pui_datatype_hierarchical', $_POST['p2pui_datatype_hierarchical']);
    }
	return;
}

function p2pui_save_connection_meta($post_id, $post) {	
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
       return;
	if('p2pui_connect_type' == $_POST['post_type']) {
		if(!current_user_can('edit_page', $post_id))
           return;
		}
	else if(!current_user_can('edit_post', $post_id))
		return;
	if(isset($_POST['p2pui_connect_type_nonce']) && wp_verify_nonce($_POST['p2pui_connect_type_nonce'], plugins_url(__FILE__)) && check_admin_referer(plugins_url(__FILE__), 'p2pui_connect_type_nonce')) {
		update_post_meta($post_id, 'p2pui_connection_name', $_POST['p2pui_connection_name']);
		update_post_meta($post_id, 'p2pui_from_post_type', $_POST['p2pui_from_post_type']);
		update_post_meta($post_id, 'p2pui_to_post_type', $_POST['p2pui_to_post_type']);
    }
	return;
}
add_action('save_post', 'p2pui_save_connection_meta');	
add_action('save_post', 'p2pui_save_datatype_meta');

function p2pui_setup_connections() {	//registers connection-types from each connection-type post
	$get_post_args = array(
		'numberposts'     => -1, // Get all of them
		'post_type'       => 'p2pui_connect_type',
	);
	$connections_to_register = get_posts( $get_post_args );
	foreach ( $connections_to_register as $post ) :
		setup_postdata($post);	
		p2p_register_connection_type( array(		//function from the Posts-to-Posts plugin that actually registers the connection types
			'name' => get_post_meta($post->ID, 'p2pui_connection_name', true),
			'from' => get_post_meta($post->ID, 'p2pui_from_post_type', true),
			'to' => get_post_meta($post->ID, 'p2pui_to_post_type', true),
			'admin_box' => array(
				'show' => 'any',
				'context' => 'advanced'
			),
			'fields' => p2pui_get_connection_fields($post),	//see below
			'cardinality' => get_post_meta($post->ID, 'p2pui_cardinality', true),
			
			
			//The below commented lines are additional parameters I haven't gotten to yet, copied from the P2P documentation.
			//'cardinality' => string How many connection can each post have: 'one-to-many', 'many-to-one' or 'many-to-many'. Default: 'many-to-many'
			//'prevent_duplicates' => bool Whether to disallow duplicate connections between the same two posts. Default: true.
			//'self_connections' => bool Whether to allow a post to connect to itself. Default: false.
			//'sortable' => bool|string Whether to allow connections to be ordered via drag-and-drop. Can be 'from', 'to', 'any' or false. Default: false.
			//'title' => string|array The box's title. Default: 'Connected {$post_type}s'
			//'reciprocal' => bool For indeterminate connections: True means all connections are displayed in a single box. False means 'from' connections are shown in one box and 'to' connections are shown in another box. Default: false.
			//'admin_box' => bool|string|array Whether and where to show the admin connections box.
			//'can_create_post' => bool Whether to allow post creation via the connection box. Default: true.
		));
	endforeach;
}
add_action( 'wp_loaded', 'p2pui_setup_connections' );	//run the connections generator after WP loads

function p2pui_get_connection_fields($post) {		//sifts through the meta-data of the connection-type post to find the fields of data that should be included for recording connection meta-data 
		// These serve as Connection Attributes. Each attribute is recorded as several separate pieces of meta-data
		// on the post that creates the Connection-Type.
	for ($i = 0; get_post_meta( $post->ID, 'fieldkey'.$i, true ); $i++) {
		${'fieldkey'.$i} = get_post_meta( $post->ID, 'fieldkey'.$i, true );
		$fields[${'fieldkey'.$i}] = array(
			'title' => get_post_meta( $post->ID, 'fieldtitle'.$i, true ),
			'type' => get_post_meta( $post->ID, 'fieldtype'.$i, true ),		//Field type in UI: blank for simple text entry, dropdown, or checkbox
		);
		if ( get_post_meta( $post->ID, 'fieldvaluesource'.$i, true ) == 'taxonomy' ) {	// Allows user to define list for a dropdown to come from a WP taxonomy list (works like a foreign-key ref) or a pre-defined set of values (works like ENUM)
			${taxonomies.$i} = get_post_meta( $post->ID, 'fieldvaluetaxonomy'.$i, true );
			${get_terms_args.$i} = array (	
				'hide_empty' => false,
				'fields' => 'names',
			);
			$fields[${'fieldkey'.$i}]['values'] = get_terms( ${taxonomies.$i}, ${get_terms_args.$i} ); //get the list of taxonomic terms from the specified taxonomy group
		} elseif ( get_post_meta( $post->ID, 'fieldvalues'.$i, false ) ) {
			$fields[${'fieldkey'.$i}]['values'] = get_post_meta( $post->ID, 'fieldvalues'.$i, false );	//get the pre-determined values. will return an array of all values with the key of fieldvaluesX
			if ( get_post_meta( $post->ID, 'fielddefault'.$i, true ) ) {
				$fields[${'fieldkey'.$i}]['default'] = get_post_meta( $post->ID, 'fielddefault'.$i, true );	//get the default value for the feild, if there is one
			}
		}
	}
	return $fields;
}

function p2pui_setup_datatypes() {	//registers data-types from each data-type post
	$get_post_args = array(
		'numberposts'     => -1, // Get all of them
		'post_type'       => 'p2pui_datatype',
	);
	$datatypes_to_register = get_posts( $get_post_args );
	foreach ( $datatypes_to_register as $post ) :
		setup_postdata($post);	//transforms $post (which is a small array of a few values, as fetched by the get_posts function) into the full $post object
		$post_type_name = get_post_meta($post->ID, 'p2pui_datatype_name_single', true);
		$datatype_args = array(
			'public' 		=> true,
			'label'			=> get_the_title($post->ID),
			'hierarchical' 	=> true, //get_post_meta($post->ID, 'p2pui_datatype_hierarchical', true),
			'supports' 		=> array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'custom-fields',
				'comments',
				'revisions'
			),
			'has_archive' => true,
		);
		register_post_type( $post_type_name, $datatype_args );
	endforeach;
}
add_action('init', 'p2pui_setup_datatypes');
?>