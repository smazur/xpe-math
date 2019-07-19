<?php
namespace smazur\xpe\math;

use smazur\xpe\SyntaxParser;

class MathParser extends SyntaxParser {
	public $context;

	public function __construct() {
		$this->context = new MathContext();
		$this->init_parse_rules();
	}

	protected function init_parse_rules() {
		$reduce_binary_expr = function( $m ) {
			$list_len = count( $m  );

			if( $list_len == 1 ) {
				return $m[0];
			}

			$node = new BinaryNode( $m[1]->content, $m[0], $m[2] );

			for( $i = 3, $n = count( $m ); $i < $n; $i+= 2 ) {
				$node = new BinaryNode( $m[$i]->content, $node, $m[$i+1] );
			}

			return $node;
		};

		$reduce_pow_expr = function( $m ) {
			$list_len = count( $m  );

			if( $list_len == 1 ) {
				return $m[0];
			}

			$node = null;

			for( $i = count($m) - 1; $i > 0; $i -= 2 ) {
				if( is_null( $node ) ) {
					$node = new BinaryNode( $m[$i-1]->content, $m[$i-2], $m[$i] );
				} else {
					$node = new BinaryNode( $m[$i-1]->content, $m[$i-2], $node );
				}
			}

			return $node;
		};

		$return0 = function( $m ) { return $m[0]; };
		$return1 = function( $m ) { return $m[1]; };

		// TOKENS
		$this->add_token( 'tok-number', MathTokenizer::TOK_NUMBER )->onmatch(function( $m ) {
			$node = new NumberNode( $m->value );
			$node->set_meta( 'token', $m );
			return $node;
		});

		$this->add_token( 'tok-var', MathTokenizer::TOK_IDENTIFIER )->onmatch(function( $m ) {
			$node = new VarNode( $m->value );
			$node->set_meta( 'token', $m );
			return $node;
		});

		$this->add_token( 'tok-name', MathTokenizer::TOK_IDENTIFIER );
		$this->add_token( 'tok-sum', MathTokenizer::TOK_OPERATOR, [ '+', '-' ] );
		$this->add_token( 'tok-unar-operator', MathTokenizer::TOK_OPERATOR, [ '+', '-' ] );
		$this->add_token( 'tok-mul', MathTokenizer::TOK_OPERATOR, [ '*', '/', '%' ] );
		$this->add_token( 'tok-pow', MathTokenizer::TOK_OPERATOR, '^' );
		$this->add_token( 'tok-open-par', MathTokenizer::TOK_OPEN_PARENTHESIS );
		$this->add_token( 'tok-close-par', MathTokenizer::TOK_CLOSE_PARENTHESIS );
		$this->add_token( 'tok-comma', MathTokenizer::TOK_COMMA );
		$this->add_token( 'tok-semicolon', MathTokenizer::TOK_SEMICOLON );
		$this->add_token( 'tok-assign', MathTokenizer::TOK_ASSIGN );
		$this->add_token( 'tok-open-curl', MathTokenizer::TOK_OPEN_CURL );
		$this->add_token( 'tok-close-curl', MathTokenizer::TOK_CLOSE_CURL );
		$this->add_token( 'tok-const', MathTokenizer::TOK_IDENTIFIER, 'const' );
		$this->add_token( 'tok-func', MathTokenizer::TOK_IDENTIFIER, 'func' );

		// SYNTAX RULES
		$this->add_rule( 'term', 'tok-number' );
		$this->add_rule( 'term', 'call-expr' );
		$this->add_rule( 'term', 'tok-var' );
		$this->add_rule( 'term', 'par-expr' );

		$this->add_list_rule( 'call-args', 'sum-expr', 'tok-comma' )->opts([
			'match_empty' => true,
			'separator_return' => false,
		]);

		$this->add_rule( 'call-expr', [ 'tok-name', 'tok-open-par', 'call-args', 'tok-close-par' ] )->onmatch(function( $m ) {
			$node = new FuncCallNode( $m[0]->value, $m[2] );
			$node->set_meta( 'token', $m[0] );
			return $node;
		})->onerror( 2, 3, function( $m, $t ) {
			$tok = $t->check_token();

			switch( sizeof( $m ) ) {
			case 2:
				throw new SyntaxError( 'Expecting function arguments or \')\'.', $tok->line, $tok->line_offset );
			default:
				$this->maybe_throw_eof( $tok );
				throw new SyntaxError( 'Expecting \')\'.', $tok->line, $tok->line_offset );
			}
		});

		$this->add_rule( 'par-expr', [ 'tok-open-par', 'sum-expr', 'tok-close-par' ] )
			->onmatch( $return1 )
			->onerror(function( $m, $t ) {
				$tok = $t->check_token();
				$this->maybe_throw_eof( $tok );

				$line   = $tok ? $tok->line : 0;
				$offset = $tok ? $tok->line_offset + $tok->len : 0;

				switch( sizeof( $m ) ) {
					case 1:
						throw new SyntaxError( 'Expecting expression.', $line, $offset );
					break;
					default:
						throw new SyntaxError( 'Expecting \')\'.', $line, $offset );
				}
			});

		$this->add_rule( 'unar-expr', [ 'tok-unar-operator', 'term' ] )->onmatch( function( $m ) {
			return new UnaryNode( $m[0]->content, $m[1] );
		});

		$this->add_rule( 'unar-expr', 'term' );

		$this->add_list_rule( 'sum-expr', 'mul-expr', 'tok-sum' )->onmatch( $reduce_binary_expr );
		$this->add_list_rule( 'mul-expr', 'pow-expr', 'tok-mul' )->onmatch( $reduce_binary_expr );
		$this->add_list_rule( 'pow-expr', 'unar-expr', 'tok-pow' )->onmatch( $reduce_pow_expr );

		$this->add_rule( 'assign-statement', [ 'tok-name', 'tok-assign', 'sum-expr' ] )->onmatch( function( $m ) {
			return new AssignNode( $m[0]->content, $m[2] );
		})->onerror( function( $m, $t ) {

		});

		$this->add_rule( 'const-statement', [ 'tok-const', 'tok-name', 'tok-assign', 'sum-expr' ] )->onmatch( function( $m ) {
			$const_name = $m[1]->content;
			$const_expr = $m[3];

			$this->context->constants[$const_name] = false;

			return new ConstNode( $const_name, $const_expr );
		})->onerror( function( $m, $t ) {
			$tok = $t->check_token();

			$line   = $tok->line;
			$offset = $tok->line_offset;

			switch( sizeof( $m ) ) {
			case 1:
				throw new SyntaxError( 'Expecting constant name.', $line, $offset );
			case 2:
			case 3:
				throw new SyntaxError( 'Expecting constant assignment expression.', $line, $offset );
			default:
				$this->maybe_throw_eof( $tok );
				throw new SyntaxError( 'Expecting constant assignment expression.', $line, $offset );
			}
		});

		$this->add_rule( 'func-statement', [
			'tok-func',
			'tok-name',
			'tok-open-par', 
			'func-arg-list', 
			'tok-close-par',
			'tok-open-curl',
			'func-statement-list',
			'tok-close-curl',	
		])->onmatch( function( $m ) {
			$func_name = $m[1]->content;
			$func_args = Utils::pluck_key( $m[3], 'content' );
			$func_body = new ExprListNode();
			$func_body->add_child( $m[6] );

			$func_node = new FuncNode( $func_name, $func_args, $func_body );
			$func_node->create( $this->context );

			return $func_node;
		})->onerror( function( $m, $t ) {
			$tok = $t->check_token();

			$line   = $tok->line;
			$offset = $tok->line_offset;

			// Throw exception depending on number of matched rule elements.
			switch( sizeof( $m ) ) {
			case 1:
				throw new SyntaxError( 'Expecting function name.', $line, $offset );
			case 2:
				throw new SyntaxError( 'Expecting \'(\'.', $line, $offset );
			case 3:
			case 4:
				throw new SyntaxError( 'Expecting \')\'.', $line, $offset );
			case 5:
				throw new SyntaxError( 'Expecting \'{\'.', $line, $offset );
			case 6:
				throw new SyntaxError( 'Expecting function body.', $line, $offset );
			case 7:
				throw new SyntaxError( 'Unexpected end of function.', $line, $offset );
			default:
				$this->maybe_throw_eof( $tok );
				throw new SyntaxError( 'Expecting function definition.', $line, $offset );
			}
		});
		

		$this->add_list_rule( 'func-arg-list', 'tok-name', 'tok-comma' )->opts([
			'match_empty' => true,
			'separator_return' => false
		]);

		$this->add_list_rule( 'func-statement-list', 'func-statement-list-item', 'tok-semicolon' )->opts([
			'separator_return' => false,
		]);

		$this->add_rule( 'func-statement-list-item', 'const-statement' );
		$this->add_rule( 'func-statement-list-item', 'assign-statement' );
		$this->add_rule( 'func-statement-list-item', 'sum-expr' );

		$this->add_rule( 'statement', 'const-statement' );
		$this->add_rule( 'statement', 'func-statement' );
		$this->add_rule( 'statement', 'assign-statement' );
		$this->add_rule( 'statement', 'sum-expr' );

		$this->add_list_rule( 'statement-list', 'statement', 'tok-semicolon' )->opts([
			'separator_return' => false
		])->onmatch( function( $m ) {
			$node = new ExprListNode();
			$node->add_child( $m );
			return $node;
		});
	}

	public function evaluate( $expr ) {
		$tokenizer = new MathTokenizer( $expr );

		$parse_start = microtime( true );
		$statement_list = $this->parse( 'statement-list', $tokenizer );
		$parse_end = microtime( true );

		$next_token = $tokenizer->check_token();

		if( $next_token->code !== MathTokenizer::TOK_EOF ) {
			throw new SyntaxError( sprintf( 'Expecting \';\' got \'%s\'.', $next_token->content ), $next_token->line, $next_token->line_offset );
		}

		$eval_start = microtime( true );
		$res_value = $statement_list->get_value( $this->context );
		$eval_end = microtime( true );
		echo "Parse: ", $parse_end - $parse_start, "\n";
		echo "Eval: ", $eval_end - $eval_start, "\n";

		return $res_value;
	}

	public function maybe_throw_eof( $tok ) {
		if( $tok->code === MathTokenizer::TOK_EOF ) {
			throw new SyntaxError( 'Unexpected end of file.', $tok->line, $tok->line_offset );
		}
	}
}
