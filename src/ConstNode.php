<?php
namespace smazur\xpe\math;

class ConstNode extends ASTNode {
	protected $name;

	public function __construct( $name, $n, $parent = null ) {
		parent::__construct( $parent );
		$this->name = $name;
		$this->add_child( $n );
	}

	public function get_value( $context ) {
		$context->constants[$this->name] = $this->children[0]->get_value( $context );
	}
}
