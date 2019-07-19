<?php
namespace smazur\xpe\math;

class NumberNode extends ASTNode {
	protected $value;

	public function __construct( $value, $parent = null ) {
		parent::__construct( $parent );
		$this->value = $value;
	}

	public function get_value( $context ) {
		return $this->value;
	}
}
