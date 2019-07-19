<?php
namespace smazur\xpe\math;

class BinaryNode extends ASTNode {
	protected $operator;

	public function __construct( $operator, $l, $r, $parent = null ) {
		parent::__construct( $parent );
		$this->operator = $operator;
		$this->add_child( $l );
		$this->add_child( $r );
	}

	public function get_value( $context ) {
		$lv = $this->children[0]->get_value( $context );
		$rv = $this->children[1]->get_value( $context );

		switch( $this->operator ) {
		case '+':
			return $lv + $rv;	
		break;
		case '-':
			return $lv - $rv;	
		break;
		case '*':
			return $lv * $rv;	
		break;
		case '/':
			return $lv / $rv;	
		break;
		case '%':
			return $lv % $rv;
		break;
		case '^':
			return pow( $lv, $rv );
		break;
		}

		throw new Exception( sprintf( 'Unknown binary operator "%s"', $this->operator ) );
	}
}
