<?php
namespace smazur\xpe\math;

class MathContext {
	public $parent;
	public $parent_scope;

	public $functions;
	public $constants;
	public $variables;

	public function __construct( $parent = null, $parent_scope = null ) {
		$this->parent = $parent;
		$this->parent_scope = $parent_scope;

		$this->functions = array();
		$this->constants = array();
		$this->variables = array();
	}

	public function is_const( $name ) {
		if( isset( $this->constants[$name] ) ) {
			return true;
		}

		if( $this->parent && !empty( $this->parent_scope['constants'] ) ) {
			return $this->parent->is_const( $name );
		}

		return false;
	}

	public function get_function( $name ) {
		if( isset( $this->functions[$name] ) ) {
			return $this->functions[$name];
		}

		if( $this->parent && !empty( $this->parent_scope['functions'] ) ) {
			return $this->parent->get_function( $name );
		}

		throw new \Exception( sprintf( 'Function "%s" is undefined.', $name ) );
	}

	public function get_var( $name ) {
		if( isset( $this->variables[$name] ) ) {
			return $this->variables[$name];
		}

		if( $this->parent && !empty( $this->parent_scope['variables'] ) ) {
			return $this->parent->get_var( $name );
		}

		throw new \Exception( sprintf( 'Name "%s" is undefined.', $name ) );
	}

	public function get_const( $name ) {
		if( isset( $this->constants[$name] ) ) {
			return $this->constants[$name];
		}

		if( $this->parent && !empty( $this->parent_scope['constants'] ) ) {
			return $this->parent->get_const( $name );
		}

		throw new \Exception( sprintf( 'Name "%s" is undefined.', $name ) );
	}
}
