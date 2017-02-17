<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * An implementation of a Folder which displays a list on entries using the status list table.
 *
 * @since 1.0
 */
class Gravity_Flow_Folder_List extends Gravity_Flow_Folder {

	/**
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function icon( $size = 1, $echo = true ) {
		$icon = sprintf( '<i class="gravityflowfolders-folder fa fa-folder-o fa-%dx"></i>', $size );
		if ( $echo ) {
			echo $icon;
		}

		return $icon;
	}

	/**
	 * Renders the folder.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return bool|WP_Error
	 */
	public function render( $args = array() ) {

		require_once( gravity_flow()->get_base_path() . '/includes/pages/class-status.php' );

		$defaults = array(
			'action_url'           => admin_url( 'admin.php?page=gravityflow-folders&folder=' . $this->get_id() ),
			'base_url'             => admin_url( 'admin.php?page=gravityflow-folders&folder=' . $this->get_id() ),
			'detail_base_url'      => admin_url( 'admin.php?page=gravityflow-inbox&view=entry&folder=' . $this->get_id() ),
			'filter_hidden_fields' => array(),
			'constraint_filters'   => array(),
			'display_all'          => true,
		);

		$args = array_merge( $defaults, $args );

		if ( ! isset( $args['constraint_filters']['field_filters'] ) ) {
			$args['constraint_filters']['field_filters'] = array();

		}

		$args['constraint_filters']['field_filters'][] = array(
			'key'      => $this->get_meta_key(),
			'value'    => 0,
			'operator' => '>',
		);

		if ( empty( $args['filter_hidden_fields'] ) ) {
			$args['filter_hidden_fields'] = array(
				'page'   => 'gravityflow-folders',
				'folder' => $this->get_id(),
			);
		}


		Gravity_Flow_Status::render( $args );

		return true;
	}
}
