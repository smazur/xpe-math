<?php
namespace smazur\xpe\math;

use smazur\xpe\Tokenizer;
use smazur\xpe\ReParser;

class MathTokenizer extends Tokenizer {
	const TOK_EOF = -1;
	const TOK_IDENTIFIER = 1;
	const TOK_NUMBER = 2;
	const TOK_OPERATOR = 3;
	const TOK_OPEN_PARENTHESIS = 4;
	const TOK_CLOSE_PARENTHESIS = 5;
	const TOK_ASSIGN = 6;
	const TOK_COMMA = 7;
	const TOK_SEMICOLON = 8;
	const TOK_WS = 9;
	const TOK_OPEN_CURL = 10;
	const TOK_CLOSE_CURL = 11;

	public function __construct( $expr ) {
		parent::__construct( $expr );

		$num_value = function( $match ) {
			return floatval( $match[0] );
		};

		$this->add_parser(
			new ReParser('#\s+#A', self::TOK_WS),
			new ReParser('#[a-zA-Z_][a-zA-Z0-9_]*#A', self::TOK_IDENTIFIER),
			new ReParser('#[0-9]+(\.[0-9]+)?#A', self::TOK_NUMBER, $num_value),
			new ReParser('#\.[0-9]+#A', self::TOK_NUMBER, $num_value),
			new ReParser('#[-+*/%^]#A', self::TOK_OPERATOR),
			new ReParser('#\(#A', self::TOK_OPEN_PARENTHESIS),
			new ReParser('#\)#A', self::TOK_CLOSE_PARENTHESIS),
			new ReParser('#=#A', self::TOK_ASSIGN),
			new ReParser('#,#A', self::TOK_COMMA),
			new ReParser('#;#A', self::TOK_SEMICOLON),
			new ReParser('#\{#A', self::TOK_OPEN_CURL),
			new ReParser('#\}#A', self::TOK_CLOSE_CURL)
		);

		$this->skip_token( self::TOK_WS );
		$this->token_eof = self::TOK_EOF;
	}
}
