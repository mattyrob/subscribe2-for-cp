<?php
class S2_Forms {
	/**
	 * Functions to Display content of Your Subscriptions page and process any input
	 */
	public function init() {
		add_action( 's2_subscription_submit', array( &$this, 's2_your_subscription_submit' ) );
		add_action( 's2_subscription_form', array( &$this, 's2_your_subscription_form' ), 10, 2 );
	}

	/**
	 * Return appropriate user ID if user can edit other users subscriptions
	 */
	public function get_userid() {
		if ( isset( $_GET['_wpnonce'] ) && false === wp_verify_nonce( $_GET['_wpnonce'], '_s2_edit_registered' ) ) {
			die( '<p>' . esc_html__( 'Security error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
		}

		if ( isset( $_GET['id'] ) ) {
			if ( ! current_user_can( (string) apply_filters( 's2_capability', 'manage_options', 'manage' ) ) ) {
				die( '<p>' . esc_html__( 'Permission error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
			}
			if ( is_multisite() ) {
				if ( ! is_user_member_of_blog( $_GET['id'], get_current_blog_id() ) ) {
					die( '<p>' . esc_html__( 'Permission error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
				} else {
					$userid = (int) $_GET['id'];
				}
			} else {
				$userid = (int) $_GET['id'];
			}
		} else {
			global $user_ID;
			return $user_ID;
		}
		return $userid;
	}

	/**
	 * Display the form to allow Regsitered users to amend their subscription
	 */
	public function s2_your_subscription_form( $userid ) {
		if ( ! is_int( $userid ) ) {
			return false;
		}

		echo '<input type="hidden" name="s2_admin" value="user">';
		if ( 'never' === s2cp()->subscribe2_options['email_freq'] ) {
			echo esc_html__( 'Receive email as', 'subscribe2-for-cp' ) . ': &nbsp;&nbsp;';
			echo '<label><input type="radio" name="s2_format" value="html"' . checked( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_format' ), true ), 'html', false ) . '>';
			echo ' ' . esc_html__( 'HTML - Full', 'subscribe2-for-cp' ) . '</label>&nbsp;&nbsp;';
			echo '<label><input type="radio" name="s2_format" value="html_excerpt"' . checked( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_format' ), true ), 'html_excerpt', false ) . '>';
			echo ' ' . esc_html__( 'HTML - Excerpt', 'subscribe2-for-cp' ) . '</label>&nbsp;&nbsp;';
			echo '<label><input type="radio" name="s2_format" value="post"' . checked( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_format' ), true ), 'post', false ) . '>';
			echo ' ' . esc_html__( 'Plain Text - Full', 'subscribe2-for-cp' ) . '</label>&nbsp;&nbsp;';
			echo '<label><input type="radio" name="s2_format" value="excerpt"' . checked( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_format' ), true ), 'excerpt', false ) . '>';
			echo ' ' . esc_html__( 'Plain Text - Excerpt', 'subscribe2-for-cp' ) . '</label><br><br>' . "\r\n";

			if ( 'yes' === s2cp()->subscribe2_options['show_autosub'] ) {
				echo esc_html__( 'Automatically subscribe me to newly created categories', 'subscribe2-for-cp' ) . ': &nbsp;&nbsp;';
				echo '<label><input type="radio" name="new_category" value="yes"' . checked( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_autosub' ), true ), 'yes', false ) . '>';
				echo ' ' . esc_html__( 'Yes', 'subscribe2-for-cp' ) . '</label>&nbsp;&nbsp;';
				echo '<label><input type="radio" name="new_category" value="no"' . checked( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_autosub' ), true ), 'no', false ) . '>';
				echo ' ' . esc_html__( 'No', 'subscribe2-for-cp' ) . '</label>';
				echo '</p>';
			}

			if ( 'yes' === s2cp()->subscribe2_options['one_click_profile'] ) {
				// One-click subscribe and unsubscribe buttons
				echo '<h2>' . esc_html__( 'One Click Subscription / Unsubscription', 'subscribe2-for-cp' ) . "</h2>\r\n";
				echo '<p class="submit"><input type="submit" class="button-primary" name="subscribe" value="' . esc_html__( 'Subscribe to All', 'subscribe2-for-cp' ) . '">&nbsp;&nbsp;';
				echo '<input type="submit" class="button-primary" name="unsubscribe" value="' . esc_html__( 'Unsubscribe from All', 'subscribe2-for-cp' ) . '"></p>';
			}

			// subscribed categories
			if ( s2cp()->s2_mu ) {
				global $blog_id;
				$subscribed = get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true );
				// if we are subscribed to the current blog display an "unsubscribe" link
				if ( ! empty( $subscribed ) ) {
					$unsubscribe_link = esc_url( add_query_arg( 's2mu_unsubscribe', $blog_id ) );
					echo '<p><a href="' . esc_url( $unsubscribe_link ) . '" class="button">' . esc_html__( 'Unsubscribe me from this blog', 'subscribe2-for-cp' ) . '</a></p>';
				} else {
					// else we show a "subscribe" link
					$subscribe_link = esc_url( add_query_arg( 's2mu_subscribe', $blog_id ) );
					echo '<p><a href="' . esc_url( $subscribe_link ) . '" class="button">' . esc_html__( 'Subscribe to all categories', 'subscribe2-for-cp' ) . '</a></p>';
				}
				echo '<h2>' . esc_html__( 'Subscribed Categories on', 'subscribe2-for-cp' ) . ' ' . esc_html( get_option( 'blogname' ) ) . ' </h2>' . "\r\n";
			} else {
				echo '<h2>' . esc_html__( 'Subscribed Categories', 'subscribe2-for-cp' ) . '</h2>' . "\r\n";
			}
			( '' === s2cp()->subscribe2_options['compulsory'] ) ? $compulsory = array() : $compulsory = explode( ',', s2cp()->subscribe2_options['compulsory'] );
			$this->display_category_form( explode( ',', get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true ) ), s2cp()->subscribe2_options['reg_override'], $compulsory );
		} else {
			// we're doing daily digests, so just show
			// subscribe / unnsubscribe
			echo esc_html__( 'Receive periodic summaries of new posts?', 'subscribe2-for-cp' ) . ': &nbsp;&nbsp;';
			echo '<label>';
			echo '<input type="radio" name="category" value="digest"';
			if ( get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true ) ) {
				echo ' checked="checked"';
			}
			echo '> ' . esc_html__( 'Yes', 'subscribe2-for-cp' ) . '</label> <label><input type="radio" name="category" value="-1" ';
			if ( ! get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true ) ) {
				echo ' checked="checked"';
			}
			echo '> ' . esc_html__( 'No', 'subscribe2-for-cp' );
			echo '</label></p>';
		}

		if ( count( $this->get_authors() ) > 1 && 'never' === s2cp()->subscribe2_options['email_freq'] ) {
			echo '<div class="s2_admin" id="s2_authors">' . "\r\n";
			echo '<h2>' . esc_html__( 'Do not send notifications for post made by these authors', 'subscribe2-for-cp' ) . '</h2>' . "\r\n";
			$this->display_author_form( explode( ',', get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_authors' ), true ) ) );
			echo '</div>' . "\r\n";
		}

		// list of subscribed blogs on Multisite installs
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( s2cp()->s2_mu && ! isset( $_GET['email'] ) ) {
			global $s2class_multisite;
			$s2blog_id    = $blog_id;
			$current_user = wp_get_current_user();
			$blogs        = $s2class_multisite->get_mu_blog_list();

			$blogs_subscribed    = array();
			$blogs_notsubscribed = array();

			foreach ( $blogs as $blog ) {
				// switch to blog
				switch_to_blog( $blog['blog_id'] );

				// check that the Subscribe2 plugin is active on the current blog
				$current_plugins = get_option( 'active_plugins' );
				if ( ! is_array( $current_plugins ) ) {
					$current_plugins = (array) $current_plugins;
				}
				if ( ! in_array( S2DIR . 'subscribe2.php', $current_plugins, true ) ) {
					continue;
				}

				// check if we're subscribed to the blog
				$subscribed = get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true );

				$blogname = get_option( 'blogname' );
				if ( strlen( $blogname ) > 30 ) {
					$blog['blogname'] = wp_html_excerpt( $blogname, 30 ) . '..';
				} else {
					$blog['blogname'] = $blogname;
				}
				$blog['description']    = get_option( 'blogdescription' );
				$blog['blogurl']        = get_option( 'home' );
				$blog['subscribe_page'] = get_option( 'home' ) . '/wp-admin/admin.php?page=s2';

				$key = strtolower( $blog['blogname'] . '-' . $blog['blog_id'] );
				if ( ! empty( $subscribed ) ) {
					$blogs_subscribed[ $key ] = $blog;
				} else {
					$blogs_notsubscribed[ $key ] = $blog;
				}
				restore_current_blog();
			}

			echo '<div class="s2_admin" id="s2_mu_sites">' . "\r\n";
			if ( ! empty( $blogs_subscribed ) ) {
				ksort( $blogs_subscribed );
				echo '<h2>' . esc_html__( 'Subscribed Blogs', 'subscribe2-for-cp' ) . '</h2>' . "\r\n";
				echo '<ul class="s2_blogs">' . "\r\n";
				foreach ( $blogs_subscribed as $blog ) {
					echo '<li><span class="name"><a href="' . esc_url( $blog['blogurl'] ) . '" title="' . esc_attr( $blog['description'] ) . '">' . esc_html( $blog['blogname'] ) . '</a></span>' . "\r\n";
					if ( $s2blog_id === $blog['blog_id'] ) {
						echo '<span class="buttons">' . esc_html__( 'Viewing Settings Now', 'subscribe2-for-cp' ) . '</span>' . "\r\n";
					} else {
						echo '<span class="buttons">';
						if ( is_user_member_of_blog( $current_user->id, $blog['blog_id'] ) ) {
							echo '<a href="' . esc_url( $blog['subscribe_page'] ) . '">' . esc_html__( 'View Settings', 'subscribe2-for-cp' ) . '</a>' . "\r\n";
						}
						echo '<a href="' . esc_url( add_query_arg( 's2mu_unsubscribe', $blog['blog_id'] ) ) . '">' . esc_html__( 'Unsubscribe', 'subscribe2-for-cp' ) . '</a></span>' . "\r\n";
					}
					echo '<div class="additional_info">' . esc_html( $blog['description'] ) . '</div>' . "\r\n";
					echo '</li>';
				}
				echo '</ul>' . "\r\n";
			}

			if ( ! empty( $blogs_notsubscribed ) ) {
				ksort( $blogs_notsubscribed );
				echo '<h2>' . esc_html__( 'Subscribe to new blogs', 'subscribe2-for-cp' ) . "</h2>\r\n";
				echo '<ul class="s2_blogs">';
				foreach ( $blogs_notsubscribed as $blog ) {
					echo '<li><span class="name"><a href="' . esc_url( $blog['blogurl'] ) . '" title="' . esc_attr( $blog['description'] ) . '">' . esc_html( $blog['blogname'] ) . '</a></span>' . "\r\n";
					if ( $s2blog_id === $blog['blog_id'] ) {
						echo '<span class="buttons">' . esc_html__( 'Viewing Settings Now', 'subscribe2-for-cp' ) . '</span>' . "\r\n";
					} else {
						echo '<span class="buttons">';
						if ( is_user_member_of_blog( $current_user->id, $blog['blog_id'] ) ) {
							echo '<a href="' . esc_url( $blog['subscribe_page'] ) . '">' . esc_html__( 'View Settings', 'subscribe2-for-cp' ) . '</a>' . "\r\n";
						}
						echo '<a href="' . esc_url( add_query_arg( 's2mu_subscribe', $blog['blog_id'] ) ) . '">' . esc_html__( 'Subscribe', 'subscribe2-for-cp' ) . '</a></span>' . "\r\n";
					}
					echo '<div class="additional_info">' . esc_html( $blog['description'] ) . '</div>' . "\r\n";
					echo '</li>';
				}
				echo '</ul>' . "\r\n";
			}
			echo '</div>' . "\r\n";
		}
	}

	/**
	 * Process input from the form that allows Regsitered users to amend their subscription
	 */
	public function s2_your_subscription_submit() {
		$userid = $this->get_userid();

		if ( false === wp_verify_nonce( $_REQUEST['_wpnonce'], 'subscribe2-user_subscribers' . S2VERSION ) ) {
			die( '<p>' . esc_html__( 'Security error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
		}

		if ( isset( $_POST['submit'] ) ) {
			if ( isset( $_POST['s2_format'] ) ) {
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_format' ), $_POST['s2_format'] );
			} else {
				// value has not been set so use default
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_format' ), 'excerpt' );
			}
			if ( isset( $_POST['new_category'] ) ) {
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_autosub' ), $_POST['new_category'] );
			} else {
				// value has not been passed so use Settings defaults
				if ( 'yes' === s2cp()->subscribe2_options['show_autosub'] && 'yes' === s2cp()->subscribe2_options['autosub_def'] ) {
					update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_autosub' ), 'yes' );
				} else {
					update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_autosub' ), 'no' );
				}
			}

			$cats = ( isset( $_POST['category'] ) ) ? $_POST['category'] : '';

			if ( empty( $cats ) || '-1' === $cats ) {
				$oldcats = explode( ',', get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true ) );
				if ( $oldcats ) {
					foreach ( $oldcats as $cat ) {
						delete_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_cat' ) . $cat );
					}
				}
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), '' );
			} elseif ( 'digest' === $cats ) {
				$all_cats = s2cp()->all_cats( false, 'ID' );
				$catids   = '';
				foreach ( $all_cats as $cat ) {
					( '' === $catids ) ? $catids = "$cat->term_id" : $catids .= ",$cat->term_id";
					update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_cat' ) . $cat->term_id, $cat->term_id );
				}
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), $catids );
			} else {
				if ( ! is_array( $cats ) ) {
					$cats = (array) $_POST['category'];
				}
				sort( $cats );
				$old_cats = explode( ',', get_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), true ) );
				$remove   = array_diff( $old_cats, $cats );
				$new      = array_diff( $cats, $old_cats );
				if ( ! empty( $remove ) ) {
					// remove subscription to these cat IDs
					foreach ( $remove as $id ) {
						delete_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_cat' ) . $id );
					}
				}
				if ( ! empty( $new ) ) {
					// add subscription to these cat IDs
					foreach ( $new as $id ) {
						update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_cat' ) . $id, $id );
					}
				}
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_subscribed' ), implode( ',', $cats ) );
			}

			$authors = ( isset( $_POST['author'] ) ) ? $_POST['author'] : '';
			if ( is_array( $authors ) ) {
				$authors = implode( ',', $authors );
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_authors' ), $authors );
			} elseif ( empty( $authors ) ) {
				update_user_meta( $userid, s2cp()->get_usermeta_keyname( 's2_authors' ), '' );
			}
		} elseif ( isset( $_POST['subscribe'] ) ) {
			s2cp()->one_click_handler( $userid, 'subscribe' );
		} elseif ( isset( $_POST['unsubscribe'] ) ) {
			s2cp()->one_click_handler( $userid, 'unsubscribe' );
		}

		echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'Subscription preferences updated.', 'subscribe2-for-cp' ) . '</strong></p></div>' . "\r\n";
	}

	/**
	 * Display a table of categories with checkboxes
	 * Optionally pre-select those categories specified
	 */
	public function display_category_form( $selected = array(), $override = 1, $compulsory = array(), $name = 'category' ) {
		if ( 0 === $override ) {
			$all_cats = s2cp()->all_cats( true );
		} else {
			$all_cats = s2cp()->all_cats( false );
		}

		$col = 1;
		echo '<table style="width: 80%; border-collapse: separate; border-spacing: 2px;" class="editform">' . "\r\n";

		if ( count( $all_cats ) >= 2 ) {
			$colspan = 2;
		} else {
			$colspan = 1;
		}
		echo '<tr><td style="text-align: left;" colspan="' . esc_attr( $colspan ) . '">' . "\r\n";
		echo '<label><input type="checkbox" name="checkall" value="checkall_' . esc_attr( $name ) . '"> ' . esc_html__( 'Select / Unselect All', 'subscribe2-for-cp' ) . '</label>' . "\r\n";
		echo '</td></tr>' . "\r\n";
		foreach ( $all_cats as $cat ) {
			$cat_name = '';
			$parents  = array_reverse( get_ancestors( $cat->term_id, $cat->taxonomy ) );
			if ( $parents ) {
				foreach ( $parents as $parent ) {
					$parent    = get_term( $parent, $cat->taxonomy );
					$cat_name .= $parent->name . ' &raquo; ';
				}
			}
			$cat_name .= $cat->name;

			if ( 1 === $col ) {
				echo '<tr><td style="width: 50%"><label><input class="checkall_' . esc_attr( $name ) . '" type="checkbox" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $cat->term_id ) . '"';
				if ( in_array( (string) $cat->term_id, $selected, true ) || in_array( (string) $cat->term_id, $compulsory, true ) ) {
					echo ' checked="checked"';
				}
				if ( in_array( (string) $cat->term_id, $compulsory, true ) && 'category' === $name ) {
					echo ' DISABLED';
				}
				echo '> <abbr title="' . esc_attr( $cat->slug ) . '">' . esc_html( $cat_name ) . '</abbr></label></td>' . "\r\n";
				$col++;
			} else {
				echo '<td style="width: 50%"><label><input class="checkall_' . esc_attr( $name ) . '" type="checkbox" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $cat->term_id ) . '"';
				if ( in_array( (string) $cat->term_id, $selected, true ) || in_array( (string) $cat->term_id, $compulsory, true ) ) {
					echo ' checked="checked"';
				}
				if ( in_array( (string) $cat->term_id, $compulsory, true ) && 'category' === $name ) {
					echo ' DISABLED';
				}
				echo '> <abbr title="' . esc_attr( $cat->slug ) . '">' . esc_html( $cat_name ) . '</abbr></label></td></tr>' . "\r\n";
				$col = 1;
			}
		}
		if ( ! empty( $compulsory ) ) {
			foreach ( $compulsory as $cat ) {
				echo '<input type="hidden" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $cat ) . '">' . "\r\n";
			}
		}
		if ( 0 === count( $all_cats ) % 2 ) {
			echo '</table>' . "\r\n";
		} else {
			echo '</tr><table>' . "\r\n";
		}
	}

	/**
	 * Display a table of authors with checkboxes
	 * Optionally pre-select those authors specified
	 */
	public function display_author_form( $selected = array() ) {
		$all_authors = $this->get_authors();

		$half = ( count( $all_authors ) / 2 );
		$i    = 0;
		$j    = 0;
		echo '<table style="width: 100%; border-collapse: separate; border-spacing: 2px;" class="editform">' . "\r\n";
		if ( count( $all_authors ) >= 2 ) {
			$colspan = 2;
		} else {
			$colspan = 1;
		}
		echo '<tr><td style="text-align: left;" colspan="' . esc_attr( $colspan ) . '">' . "\r\n";
		echo '<label><input type="checkbox" name="checkall" value="checkall_author"> ' . esc_html__( 'Select / Unselect All', 'subscribe2-for-cp' ) . '</label>' . "\r\n";
		echo '</td></tr>' . "\r\n";
		echo '<tr style="vertical-align: top;"><td style="width: 50%; text-align: left;">' . "\r\n";
		foreach ( $all_authors as $author ) {
			if ( $i >= $half && 0 === $j ) {
				echo '</td><td style="width: 50%; text-align: left;">' . "\r\n";
				$j++;
			}
			if ( 0 === $j ) {
				echo '<label><input class="checkall_author" type="checkbox" name="author[]" value="' . esc_attr( $author->ID ) . '"';
				if ( in_array( $author->ID, $selected, true ) ) {
						echo ' checked="checked"';
				}
				echo '> ' . esc_html( $author->display_name ) . '</label>' . "\r\n";
			} else {
				echo '<label><input class="checkall_author" type="checkbox" name="author[]" value="' . esc_attr( $author->ID ) . '"';
				if ( in_array( $author->ID, $selected, true ) ) {
					echo ' checked="checked"';
				}
				echo '> ' . esc_html( $author->display_name ) . '</label>' . "\r\n";
			}
		}
		echo '</td></tr>' . "\r\n";
		echo '</table>' . "\r\n";
	}

	/**
	 * Collect an array of all author level users and above
	 */
	public function get_authors() {
		if ( '' === $this->all_authors ) {
			$role = array(
				'fields' => array( 'ID', 'display_name' ),
				'role'   => 'administrator',
			);

			$administrators = get_users( $role );

			$role = array(
				'fields' => array( 'ID', 'display_name' ),
				'role'   => 'editor',
			);

			$editors = get_users( $role );

			$role = array(
				'fields' => array( 'ID', 'display_name' ),
				'role'   => 'author',
			);

			$authors = get_users( $role );

			$this->all_authors = array_merge( $administrators, $editors, $authors );
		}
		return (array) apply_filters( 's2_authors', $this->all_authors );
	}

	/**
	 * Define some variables
	 */
	private $all_authors = '';
}
