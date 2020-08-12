<?php
namespace smazur\xpe\math;

class UnaryNode extends ASTNode {
	protected $operator;

	public function __construct( $operator, $n, $parent = null ) {
		parent::__construct( $parent );
		$this->operator = $operator;
		$this->children[0] = $n;
	}

	public function get_value( $context ) {
		$v = $this->children[0]->get_value( $context );

		switch( $this->operator ) {
		case '+':
			return $v;	
		case '-':
			return -$v;	
		}

		throw new \Exception( sprintf( 'Unknown unary operator "%s"', $this->operator ) );
	}
}
