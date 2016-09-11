<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Gravity_Flow_Folder_Checklist extends Gravity_Flow_Folder {

	/**
	 * @var array
	 */
	protected $entry_ids = null;

	/**
	 * @var bool
	 */
	protected $sequential;

	/**
	 * @var array
	 */
	protected $nodes = array();

	public function __construct( $config, WP_User $user = null ) {
		parent::__construct( $config, $user );
		$this->sequential = (bool) rgar( $config, 'sequential' );

		if ( isset( $config['nodes'] ) ) {
			$this->nodes = $config['nodes'];
		}
	}

	public function get_entries() {

		if ( isset( $this->entries ) ) {
			return $this->entries;
		}

		$search_criteria = array(
			'field_filters' => array(
				array(
					'key'      => 'created_by',
					'value'    => $this->user->ID,
					'operator' => '=',
				),
			),
		);

		$form_ids = array();

		foreach ( $this->nodes as $node ) {
			$form_ids[] = $node['form_id'];
		}

		$entries = GFAPI::get_entries( $form_ids, $search_criteria );

		$this->entries = $entries;
		return $this->entries;
	}

	public function get_entry_ids() {
		global $wpdb;

		if ( isset( $this->entry_ids ) ) {
			return $this->entry_ids;
		}

		$lead_table = GFFormsModel::get_lead_table_name();
		$clauses = array();

		foreach ( $this->nodes as $node ) {
			$form_id = $node['form_id'];
			$clause = $wpdb->prepare( "(SELECT group_concat(id ORDER BY id DESC SEPARATOR ',' ) from $lead_table WHERE form_id=%d AND created_by=%d AND status='active') as form_%d", $form_id, $this->user->ID, $form_id );
			$clauses[] = $clause;
		}

		$sql = 'SELECT ' . join( ', ', $clauses );

		$results = $wpdb->get_results( $sql );

		$ids = isset( $results[0] ) ? $results[0] : array();

		foreach ( $this->nodes as $node ) {
			$form_id                     = $node['form_id'];
			$key                         = 'form_' . $form_id;
			$this->entry_ids[ $form_id ] = isset( $ids->{$key} ) ? explode( ',', $ids->{$key} ) : null;
		}

		return $this->entry_ids;
	}

	public function get_entry_ids_for_form( $form_id ) {
		$entry_ids = $this->get_entry_ids();
		return $entry_ids[ $form_id ];
	}

	public function get_entries_for_form( $form_id ) {
		$entries_for_form = array();
		$entries = $this->get_entries();
		foreach ( $entries as $entry ) {
			if ( $entry['form_id'] == $form_id ) {
				$entries_for_form[] = $entry;
			}
		}
		return $entries_for_form;
	}

	public function render( $args = array() ) {
		$can_submit = true;

		$items = array();

		$previous_entry = null;

		foreach ( $this->nodes as $node ) {
			$form_id = $node['form_id'];
			$form      = GFAPI::get_form( $form_id );
			$entries = $this->get_entries_for_form( $form_id );

			if ( empty( $entries ) ) {
				if ( $can_submit ) {
					$url  = add_query_arg( array( 'id' => $form_id ) );

					$form_title = sprintf( '<a href="%s" style="text-decoration:none;">%s</a>',  esc_url( $url ), esc_html( $form['title'] ) );

					if ( $previous_entry ) {
						$steps = gravity_flow()->get_steps( $previous_entry['form_id'], $previous_entry );
						if ( ! empty( $steps ) && empty( $previous_entry[ $this->get_meta_key() ] ) ) {
							$form_title = sprintf( '<span class="gravityflowfolders-disabled">%s</span>', esc_html( $form['title'] ) );
						}
					}

					$item = sprintf( '%s (pending)', $form_title );
				} else {
					$item = sprintf( '<span class="gravityflowfolders-disabled">%s</span>', esc_html( $form['title'] ) );
				}
			} else {
				$url  = add_query_arg( array(
					'lid'  => $entries[0]['id'],
					'page' => 'gravityflow-inbox',
					'view' => 'entry',
				) );
				$item = sprintf( '<a href="%s" style="text-decoration:none;">%s <i class="fa fa-check" style="color:darkgreen;"></i></a>', esc_url_raw( $url ), esc_html( $form['title'] ) );
			}
			$items[] = sprintf( '<li>%s</li>', $item );

			if ( $this->sequential && empty( $entries ) ) {
				$can_submit = false;
			}

			if ( isset( $entries[0] ) ) {
				$previous_entry = $entries[0];
			}
		}

		$list = $this->sequential ? '<ol>%s</ol>' : '<ul>%s</ul>';

		printf( $list, join( "\n", $items ) );
	}
}
