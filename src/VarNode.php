<?php
namespace smazur\xpe\math;

class VarNode extends ASTNode {
	protected $name;

	public  function __construct( $name, $parent = null ) {
		parent::__construct( $parent );

		$this->name = $name;
	}

	public function get_value( $context ) {
		if( $context->is_const( $this->name ) ) {
			return $context->get_const( $this->name );
		}
		return $context->get_var( $this->name );
	}
}
