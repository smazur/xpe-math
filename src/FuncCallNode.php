<?php
namespace smazur\xpe\math;

class FuncCallNode extends ASTNode {
	protected $name;

	public  function __construct( $name, $args, $parent = null ) {
		parent::__construct( $parent );

		$this->name = $name;
		foreach( $args as $arg_node ) {
			$this->add_child( $arg_node );
		}
	}

	public function get_value( $context ) {
		$function = $context->get_function( $this->name );

		$args = array();
		foreach( $this->children as $arg_node ) {
			$args[] = $arg_node->get_value( $context );
		}

		return call_user_func_array( $function, $args );
	}

}
