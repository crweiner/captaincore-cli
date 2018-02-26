<?php

// Converts arguments --staging --all --plugin=woocommerce --theme=anchorhost into $staging $all
parse_str( implode( '&', $args ) );

if ( isset( $all ) ) {
	echo 'all';
}

$arguments = array(
	'post_type'      => 'captcore_website',
	'posts_per_page' => '-1',
	'fields'         => 'ids',
	'meta_query'     => array(
		'relation' => 'AND',
		array(
			'key'     => 'status', // name of custom field
			'value'   => 'active', // matches exaclty "123", not just 123. This prevents a match for "1234"
			'compare' => '=',
		),
		array(
			'key'     => 'install', // name of custom field
			'value'   => '',
			'compare' => '!=',
		),
	),
);

if ( $plugin ) {
	$arguments['meta_query'][] = array(
		'key'     => 'plugins', // name of custom field
		'value'   => '"name":"' . $plugin . '"', // matches exaclty "123", not just 123. This prevents a match for "1234"
		'compare' => 'like',
	);
}

if ( $theme ) {
	$arguments['meta_query'][] = array(
		'key'     => 'themes', // name of custom field
		'value'   => '"name":"' . $theme . '"', // matches exaclty "123", not just 123. This prevents a match for "1234"
		'compare' => 'like',
	);
}

$websites = get_posts( $arguments );

$results = array();

foreach ( $websites as $website_id ) {

		$site = get_post_meta( $website_id, 'install', true );

	if ( $field ) {
		if ( $field == 'domain' ) {
			$site = get_the_title( $website_id );
		} else {
			$site = get_post_meta( $website_id, $field, true );
		}
	}

	if ( isset( $staging ) ) {
		$results[] = $site . '-staging';
	} elseif ( isset( $all ) ) {
		$results[] = $site;
		$results[] = $site . '-staging';
	} else {
		$results[] = $site;
	}
}

echo implode( ' ', $results );
