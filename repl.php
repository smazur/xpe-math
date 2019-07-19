<?php

require_once __DIR__ . '/vendor/autoload.php';

use smazur\xpe\math\MathParser;

$parser = new MathParser();
$parser->context->constants = [
	'pi' => M_PI,
	'inf' => INF,
	'nan' => NAN,
];

$parser->context->functions = array(
	'sin' => 'sin',
	'cos' => 'cos',
	'round' => 'round',
	'ceil' => 'ceil',
	'floor' => 'floor',
	'clamp' => function( $x, $min, $max ) {
		return min( $max, max( $x, $min ) );
	},
);

$history_file = '.repl_history';

readline_read_history( $history_file );

while( true ) {
	try {
		$expr = readline( '> ' );

		if( false === $expr ) {
			break;
		}

		$expr = trim( $expr );

		if( empty( $expr ) ) {
			continue;
		}

		readline_add_history( $expr );

		echo $parser->evaluate( $expr ), "\n";
	} catch( Exception $e ) {
		echo 'ERROR: ', $e->getMessage(), "\n";
	}

	readline_write_history( $history_file );
}
