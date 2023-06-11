<?php
/**
 * List Table class
 */
class S2_List_Table extends WP_List_Table {
	private $date_format = '';
	private $time_format = '';

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'subscriber',
				'plural'   => 'subscribers',
				'ajax'     => false,
			)
		);
		$this->date_format = get_option( 'date_format' );
		$this->time_format = get_option( 'time_format' );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
			case 'date':
				return $item[ $column_name ];
		}
	}

	public function column_email( $item ) {
		global $current_tab;
		if ( 'registered' === $current_tab ) {
			$url     = wp_nonce_url( sprintf( '?page=s2&amp;id=%d', rawurlencode( $item['id'] ) ), '_s2_edit_registered' );
			$actions = array(
				'edit' => sprintf( '<a href="%s">%s</a>', $url, __( 'Edit', 'subscribe2-for-cp' ) ),
			);
			return sprintf( '%1$s %2$s', $item['email'], $this->row_actions( $actions ) );
		} else {
			if ( '0' === s2cp()->is_public( $item['email'] ) ) {
				return sprintf( '<span style="color:#FF0000"><abbr title="%2$s">%1$s</abbr></span>', $item['email'], $item['ip'] );
			} else {
				return sprintf( '<abbr title="%2$s">%1$s</abbr>', $item['email'], $item['ip'] );
			}
		}
	}

	public function column_date( $item ) {
		global $current_tab;
		if ( 'registered' === $current_tab ) {
			$timestamp = strtotime( $item['date'] );
			return sprintf( '<abbr title="%2$s">%1$s</abbr>', date_i18n( $this->date_format, $timestamp ), date_i18n( $this->time_format, $timestamp ) );
		} else {
			$timestamp = strtotime( $item['date'] . ' ' . $item['time'] );
			return sprintf( '<abbr title="%2$s">%1$s</abbr>', date_i18n( $this->date_format, $timestamp ), date_i18n( $this->time_format, $timestamp ) );
		}
	}

	public function column_cb( $item ) {
		$column = '<label><span class="screen-reader-text">' . __( 'Checkbox for:', 'subscribe2-for-cp' ) . ' %1$s</span><input type="checkbox" name="%2$s[]" value="%3$s"></label>';
		return sprintf( $column, $item['email'], $this->_args['singular'], $item['email'] );
	}

	public function get_columns() {
		$columns = array(
			'cb'    => '<input type="checkbox">',
			'email' => _x( 'Email', 'column name', 'subscribe2-for-cp' ),
			'date'  => _x( 'Date', 'column name', 'subscribe2-for-cp' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'email' => array( 'email', true ),
			'date'  => array( 'date', false ),
		);
		return $sortable_columns;
	}

	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		// phpcs:ignore WordPress.Security.NonceVerification
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['_wpnonce'] ) && false === wp_verify_nonce( $_GET['_wpnonce'], 's2_subscriber_tab' ) ) {
			die( '<p>' . esc_html__( 'Security error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
		}

		if ( isset( $_REQUEST['what'] ) ) {
			$current_url = add_query_arg(
				array(
					'what' => $_REQUEST['what'],
				),
				$current_url
			);
		}

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb']     = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All', 'subscribe2-for-cp' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox">';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden, true ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby === $orderby ) {
					$order   = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order   = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'";
			}

			echo wp_kses_post( "<$tag $scope $id $class>$column_display_name</$tag>" );
		}
	}

	public function get_bulk_actions() {
		global $current_tab;
		if ( 'registered' === $current_tab ) {
			if ( is_multisite() ) {
				return array();
			} else {
				return array(
					'delete' => __( 'Delete', 'subscribe2-for-cp' ),
				);
			}
		} else {
			$actions = array(
				'delete' => __( 'Delete', 'subscribe2-for-cp' ),
				'toggle' => __( 'Toggle', 'subscribe2-for-cp' ),
			);
			return $actions;
		}
	}

	public function process_bulk_action() {
		global $current_user, $subscribers;

		if ( isset( $_GET['_wpnonce'] ) && false === wp_verify_nonce( $_GET['_wpnonce'], 's2_subscriber_tab' ) ) {
			die( '<p>' . esc_html__( 'Security error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
		}

		if ( in_array( $this->current_action(), array( 'delete', 'toggle' ), true ) ) {
			if ( ! isset( $_REQUEST['subscriber'] ) ) {
				echo '<div id="message" class="error"><p><strong>' . esc_html__( 'No users were selected.', 'subscribe2-for-cp' ) . '</strong></p></div>';
				return;
			}
		}

		if ( 'delete' === $this->current_action() ) {
			$message = array();
			foreach ( $_REQUEST['subscriber'] as $address ) {
				$address = trim( stripslashes( $address ) );
				if ( false !== s2cp()->is_public( $address ) ) {
					s2cp()->delete( $address );
					$key = array_search( $address, $subscribers, true );
					unset( $subscribers[ $key ] );
					$message['public_deleted'] = __( 'Address(es) deleted!', 'subscribe2-for-cp' );
				} else {
					$user = get_user_by( 'email', $address );
					if ( ! current_user_can( 'delete_user', $user->ID ) || $user->ID === $current_user->ID ) {
						$message['reg_delete_error'] = __( 'Delete failed! You cannot delete some or all of these users.', 'subscribe2-for-cp' );
						continue;
					} else {
						$message['reg_deleted'] = __( 'Registered user(s) deleted! Any posts made by these users were assigned to you.', 'subscribe2-for-cp' );
						foreach ( $subscribers as $key => $data ) {
							if ( in_array( $address, $data, true ) ) {
								unset( $subscribers[ $key ] );
							}
						}
						wp_delete_user( $user->ID, $current_user->ID );
					}
				}
			}
			$final_message = implode( '<br><br>', array_filter( $message ) );
			echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $final_message ) . '</strong></p></div>';
		}
		if ( 'toggle' === $this->current_action() ) {
			s2cp()->ip = $current_user->user_login;
			foreach ( $_REQUEST['subscriber'] as $address ) {
				$address = trim( stripslashes( $address ) );
				s2cp()->toggle( $address );
				if ( 'confirmed' === $_POST['what'] || 'unconfirmed' === $_POST['what'] ) {
					$key = array_search( $address, $subscribers, true );
					unset( $subscribers[ $key ] );
				}
			}
			echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'Status changed!', 'subscribe2-for-cp' ) . '</strong></p></div>';
		}
	}

	public function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = intval( $this->_pagination_args['total_items'] );
		$total_pages     = intval( $this->_pagination_args['total_pages'] );
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		// Translators: Pagination
		$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items, 'subscribe2-for-cp' ), number_format_i18n( $total_items ) ) . '</span>';

		$current = intval( $this->get_pagenum() );

		if ( isset( $_POST['_wpnonce'] ) && false !== wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
			if ( isset( $_POST['what'] ) ) {
				if ( isset( $_POST['paged'] ) ) {
					$current = intval( $_POST['paged'] );
				} else {
					$current = 1;
				}
			}
		}

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		if ( isset( $_REQUEST['what'] ) ) {
			$current_url = add_query_arg(
				array(
					'what' => $_REQUEST['what'],
				),
				$current_url
			);
		}

		if ( isset( $_POST['s'] ) ) {
			$current_url = add_query_arg(
				array(
					's' => $_POST['s'],
				),
				$current_url
			);
		}

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span>';

		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;

		if ( 1 === $current ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( 2 === $current ) {
			$disable_first = true;
		}
		if ( $current === $total_pages ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $current === $total_pages - 1 ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page', 'subscribe2-for-cp' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				__( 'Previous page', 'subscribe2-for-cp' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page', 'subscribe2-for-cp' ) . '</span><span id="table-paging" class="paging-input">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page', 'subscribe2-for-cp' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		// Translators: Pagination
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging', 'subscribe2-for-cp' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				__( 'Next page', 'subscribe2-for-cp' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page', 'subscribe2-for-cp' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function prepare_items() {
		global $subscribers, $current_tab;

		$user          = get_current_user_id();
		$screen        = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page      = get_user_meta( $user, $screen_option, true );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = array();
		if ( 'public' === $current_tab ) {
			foreach ( (array) $subscribers as $email ) {
				$data[] = array(
					'email' => $email,
					'date'  => s2cp()->signup_date( $email ),
					'time'  => s2cp()->signup_time( $email ),
					'ip'    => s2cp()->signup_ip( $email ),
				);
			}
		} else {
			foreach ( (array) $subscribers as $subscriber ) {
				$data[] = array(
					'email' => $subscriber['user_email'],
					'id'    => $subscriber['ID'],
					'date'  => get_userdata( $subscriber['ID'] )->user_registered,
				);
			}
		}

		function usort_reorder( $a, $b ) {
			$orderby = 'email';
			$order   = 'asc';

			if ( isset( $_GET['_s2_order_nonce'] ) && false !== wp_verify_nonce( $_GET['_s2_order_nonce'], 's2_subscriber_order' ) ) {
				$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : $orderby;
				$order   = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : $order;
			}

			$result = strcasecmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : -$result;
		}

		usort( $data, 'usort_reorder' );

		$current_page = (int) $this->get_pagenum();

		if ( isset( $_POST['_wpnonce'] ) && false !== wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
			if ( isset( $_POST['what'] ) ) {
				if ( isset( $_POST['paged'] ) ) {
					$current_page = intval( $_POST['paged'] );
				} else {
					$current_page = 1;
				}
			}
		}

		$total_items = count( $data );
		$data        = array_slice( $data, ( $current_page - 1 ) * $per_page, $per_page );
		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}
}
