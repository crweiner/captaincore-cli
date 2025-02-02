<?php

// Replaces dashes in keys with underscores
foreach($args as $index => $arg) {
	$split = strpos($arg, "=");
	if ( $split ) {
		$key = str_replace('-', '_', substr( $arg , 0, $split ) );
		$value = substr( $arg , $split, strlen( $arg ) );

		// Removes unnessary bash quotes
		$value = trim( $value,'"' ); 				// Remove last quote 
		$value = str_replace( '="', '=', $value );  // Remove quote right after equals

		$args[$index] = $key.$value;
	} else {
		$args[$index] = str_replace('-', '_', $arg);
	}

}

// Converts --arguments into $arguments
parse_str( implode( '&', $args ) );

$arguments = array(
	'author'    	 => $captain_id,
	'post_type'      => 'captcore_website',
	'posts_per_page' => '-1',
	'fields'         => 'ids',
	'meta_or_title'  => $search,
	'meta_query'     => array(
		'relation' => 'and',
		array(
			'key'     => 'status', // name of custom field
			'value'   => 'active', // matches exaclty "123", not just 123. This prevents a match for "1234"
			'compare' => '=',
		),
		array(
			'key'     => 'site', // name of custom field
			'value'   => '',
			'compare' => '!=',
		),
	),
);

if( $search_field ) {
	$arguments['meta_query'][] = 	array(
		'relation' => 'OR',
			array(
				'key'     => $search_field, // name of custom field
				'value'   => $search,
				'compare' => 'like',
			),
		);
} else {
	$arguments['meta_query'][] = array(
		'relation' => 'OR',
		array(
			'key'     => 'address', // name of custom field
			'value'   => $search,
			'compare' => 'like',
		),
		array(
			'key'     => 'site', // name of custom field
			'value'   => $search,
			'compare' => 'like',
		),
	);
}

$websites = get_posts( $arguments );

$results = array();

foreach ( $websites as $website_id ) {

	if ($field && $field == "domain") {
		$site = get_the_title( $website_id );
	} elseif ($field) {
		$site = get_post_meta( $website_id, $field, true );
	} else {
		$site = get_post_meta( $website_id, 'site', true );
	}
	$results[] = $site;

}

echo implode( ' ', $results );
