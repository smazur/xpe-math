<?php
namespace smazur\xpe\math;

class AssignNode extends ASTNode {
	protected $name;

	public function __construct( $name, $n, $parent = null ) {
		parent::__construct( $parent );
		$this->name = $name;
		$this->add_child( $n );
	}

	public function get_value( $context ) {
		if( $context->is_const( $this->name ) ) {
			throw new \Exception( sprintf( '"%s" is constant.', $this->name ) );
		}

		$value = $this->children[0]->get_value( $context );
		$context->variables[$this->name] = $value;

		return $value;
	}
}
