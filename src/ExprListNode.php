<?php
namespace smazur\xpe\math;

class ExprListNode extends ASTNode {
	public function get_value( $context ) {
		$last_value = null;

		foreach( $this->children as $node ) {
			$last_value = $node->get_value( $context );
		}

		return $last_value;
	}
}
