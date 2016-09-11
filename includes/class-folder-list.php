<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Gravity_Flow_Folder_List extends Gravity_Flow_Folder {

	protected $entries = null;

	public function icon( $echo = true ) {
		$icon = '<span class="fa-stack fa-3x">
					<i class="fa fa-folder-o fa-stack-2x"></i>
					<i class="fa fa-list-alt fa-stack-1x" style="font-size:20px"></i>
				</span>';
		if ( $echo ) {
			echo $icon;
		}
		return $icon;
	}

	public function render( $args = array() ) {

		require_once( gravity_flow()->get_base_path() . '/includes/pages/class-status.php' );

		$defaults = array(
			'action_url'         => admin_url( 'admin.php?page=gravityflow-folders&folder=' . $this->get_id() ),
			'base_url' => admin_url( 'admin.php?page=gravityflow-folders&folder=' . $this->get_id() ),
			'detail_base_url'   => admin_url( 'admin.php?page=gravityflow-inbox&view=entry&folder=' . $this->get_id() ),
			'filter_hidden_fields' => array(),
			'constraint_filters' => array(),
			'display_all' => true,
		);

		$args = array_merge( $defaults, $args );

		if ( empty( $args['constraint_filters'] ) ) {
			$args['constraint_filters'] = array(
				'field_filters' => array(
					array(
						'key'      => $this->get_meta_key(),
						'value'    => 0,
						'operator' => '>',
					),
				),
			);
		}

		if ( empty( $args['filter_hidden_fields'] ) ) {
			$args['filter_hidden_fields'] = array(
				'page' => 'gravityflow-folders',
				'folder' => $this->get_id(),
			);
		}


		Gravity_Flow_Status::render( $args );
	}
}
