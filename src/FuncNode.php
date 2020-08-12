<?php
namespace smazur\xpe\math;

class FuncNode extends ASTNode {
	protected $name;
	protected $args;

	public function __construct( $name, $args, $body, $parent = null ) {
		parent::__construct( $parent );

		$this->name = $name;
		$this->args = $args;
		$this->add_child( $body );
	}

	public function get_value( $context ) {
		return null;
	}

	public function create( $context ) {
		$func = function() use( $context ) {
			$func_context = new MathContext( $context, [
				'functions' => true,
				'constants' => true,
			]);

			$args = func_get_args();

			if( count( $this->args ) !== count( $args ) ) {
				throw new \Exception( sprintf('Function "%s" requires %d arguments got %d', $this->name, count( $this->args ), count( $args ) ) );
			}

			foreach( $this->args as $argi => $argname ) {
				$func_context->variables[$argname] = $args[$argi];
			}

			return $this->children[0]->get_value( $func_context );
		};

		$context->functions[ $this->name ] = $func;

		return $func;
	}
}
