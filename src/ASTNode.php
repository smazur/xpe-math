<?php
namespace smazur\xpe\math;

abstract class ASTNode {
	protected $parent;
	protected $children;
	protected $meta;

	public function __construct( $parent = null ) {
		$this->parent = $parent;
		$this->children = [];
	}

	public function add_child( $node ) {
		if( is_array( $node ) ) {
			foreach( $node as $n ) {
				$n->parent = $this;
				$this->children[] = $n;
			}
		} else {
			$node->parent = $this;
			$this->children[] = $node;
		}
	}

	public function prepend_child( $node ) {
		$node->parent = $this;
		array_unshift( $this->children, $node );
	}

	public function remove_child( $node ) {
		$key = array_search( $node, $node );
		if( false === $key ) {
			return false;
		}
		$node->parent = null;
		unset( $this->children[$key] );

		return true;
	}

	public function set_meta( $key, $value ) {
		$this->meta[$key] = $value;
	}

	public function get_meta( $key ) {
		return $this->meta[$key] ?? null;
	}

	abstract public function get_value( $context );
}
