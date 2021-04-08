<?php
class S2_Frontend extends S2_Core {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'init', array( $this, 'frontend_hooks' ) );
	}

	public function frontend_hooks() {
		// load strings later on frontend for polylang plugin compatibility
		add_action( 'wp', array( &$this, 'load_strings' ) );

		if ( isset( $_REQUEST['s2'] ) ) {
			// someone is confirming a request
			add_filter( 'request', array( &$this, 'query_filter' ) );
			add_filter( 'the_title', array( &$this, 'title_filter' ) );
			add_filter( 'the_content', array( &$this, 'confirm' ) );
		}

		// add the frontend filters
		add_shortcode( 'subscribe2', array( &$this, 'shortcode' ) );
		add_filter( 'the_content', array( &$this, 'filter' ), 10 );

		// add frontend actions for comment subscribers
		if ( 'no' !== $this->subscribe2_options['comment_subs'] ) {
			add_filter( 'comment_form_submit_field', array( &$this, 's2_comment_meta_form' ) );
			add_action( 'comment_post', array( &$this, 's2_comment_meta' ), 1, 2 );
		}

		// add actions for other plugins
		if ( '1' === $this->subscribe2_options['show_meta'] ) {
			add_action( 'wp_meta', array( &$this, 'add_minimeta' ), 0 );
		}

		// add action for adding javascript IP updating code
		if ( '1' === $this->subscribe2_options['js_ip_updater'] ) {
			add_action( 'wp_enqueue_scripts', array( &$this, 'js_ip_script' ), 10 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'js_ip_library_script' ), 20 );
			add_filter( 'script_loader_tag', array( &$this, 'tag_replace_ampersand' ) );
		}

		// Display Subscription form on frontend rather than link to Profile
		if ( '1' === $this->subscribe2_options['frontend_form'] ) {
			require_once S2PATH . 'classes/class-s2-frontend-form.php';
			global $s2_frontend_form;
			$s2_frontend_form = new S2_Frontend_Form();
		}

		// Instantiate ReCaptcha class if enabled
		if ( 'off' !== $this->subscribe2_options['recaptcha'] ) {
			require_once S2PATH . 'classes/class-s2-captcha.php';
			global $s2_captcha;
			$s2_captcha = new S2_Captcha( $this->subscribe2_options['recaptcha'] );
		}
	}

	/**
	 * Load all our strings
	 */
	public function load_strings() {
		// Translators: Link to login page
		$this->please_log_in = '<p class="s2_message">' . sprintf( __( 'To manage your subscription options please <a href="%1$s">login</a>.', 'subscribe2-for-cp' ), get_option( 'siteurl' ) . '/wp-login.php' ) . '</p>';

		$profile = (string) apply_filters( 's2_profile_link', get_option( 'siteurl' ) . '/wp-admin/admin.php?page=s2' );
		// Translators: Link to Profile page
		$this->profile = '<p class="s2_message">' . sprintf( __( 'You may manage your subscription options from your <a href="%1$s">profile</a>.', 'subscribe2-for-cp' ), $profile ) . '</p>';
		if ( true === $this->s2_mu ) {
			global $blog_id;
			$user_ID = get_current_user_id();
			if ( ! is_user_member_of_blog( $user_ID, $blog_id ) ) {
				// if we are on multisite and the user is not a member of this blog change the link
				$mu_profile = apply_filters( 's2_mu_profile_link', get_option( 'siteurl' ) . '/wp-admin/?s2mu_subscribe=' . $blog_id );
				// Translators: Link to Profile page
				$this->profile = '<p class="s2_message">' . sprintf( __( '<a href="%1$s">Subscribe</a> to email notifications when this blog posts new content.', 'subscribe2-for-cp' ), $mu_profile ) . '</p>';
			}
		}

		$this->confirmation_sent = '<p class="s2_message">' . __( 'A confirmation message is on its way!', 'subscribe2-for-cp' ) . '</p>';

		$this->already_subscribed = '<p class="s2_error">' . __( 'That email address is already subscribed.', 'subscribe2-for-cp' ) . '</p>';

		$this->not_subscribed = '<p class="s2_error">' . __( 'That email address is not subscribed.', 'subscribe2-for-cp' ) . '</p>';

		$this->not_an_email = '<p class="s2_error">' . __( 'Sorry, but that does not look like an email address to me.', 'subscribe2-for-cp' ) . '</p>';

		$this->barred_domain = '<p class="s2_error">' . __( 'Sorry, email addresses at that domain are currently barred due to spam, please use an alternative email address.', 'subscribe2-for-cp' ) . '</p>';

		$this->error = '<p class="s2_error">' . __( 'Sorry, there seems to be an error on the server. Please try again later.', 'subscribe2-for-cp' ) . '</p>';

		// confirmation messages
		$this->no_such_email = '<p class="s2_error">' . __( 'No such email address is registered.', 'subscribe2-for-cp' ) . '</p>';

		$this->added = '<p class="s2_message">' . __( 'You have successfully subscribed!', 'subscribe2-for-cp' ) . '</p>';

		$this->deleted = '<p class="s2_message">' . __( 'You have successfully unsubscribed.', 'subscribe2-for-cp' ) . '</p>';

		$this->subscribe = __( 'subscribe', 'subscribe2-for-cp' ); //ACTION replacement in subscribing confirmation email

		$this->unsubscribe = __( 'unsubscribe', 'subscribe2-for-cp' ); //ACTION replacement in unsubscribing in confirmation email
	}

	/* ===== template and filter functions ===== */
	/**
	 * Display our form; also handles (un)subscribe requests
	 */
	public function shortcode( $atts ) {
		$args = shortcode_atts(
			array(
				'hide'       => '',
				'id'         => '',
				'nojs'       => 'false',
				'noantispam' => 'false',
				'link'       => '',
				'size'       => 20,
				'wrap'       => 'true',
				'widget'     => 'false',
			),
			$atts
		);

		// if link is true return a link to the page with the ajax class
		if ( '1' === $this->subscribe2_options['ajax'] && '' !== $args['link'] && ! is_user_logged_in() ) {
			$id = '';
			foreach ( $args as $arg_name => $arg_value ) {
				if ( ! empty( $arg_value ) && 'link' !== $arg_name && 'id' !== $arg_name ) {
					if ( 'nojs' === $arg_name ) {
						$arg_value = 'true';
					}
					( '' === $id ) ? $id .= $arg_name . '-' . $arg_value : $id .= ':' . $arg_name . '-' . $arg_value;
				}
			}
			$this->s2form = '<a href="#" class="s2popup" id="' . esc_attr( $id ) . '">' . esc_html( $args['link'] ) . '</a>' . "\r\n";
			return $this->s2form;
		}

		// Apply filters to button text
		$unsubscribe_button_value = (string) apply_filters( 's2_unsubscribe_button', __( 'Unsubscribe', 'subscribe2-for-cp' ) );
		$subscribe_button_value   = (string) apply_filters( 's2_subscribe_button', __( 'Subscribe', 'subscribe2-for-cp' ) );

		// if a button is hidden, show only other
		$hide = strtolower( $args['hide'] );
		if ( 'subscribe' === $hide ) {
			$this->input_form_action = '<input type="submit" name="unsubscribe" value="' . esc_attr( $unsubscribe_button_value ) . '" />';
		} elseif ( 'unsubscribe' === $hide ) {
			$this->input_form_action = '<input type="submit" name="subscribe" value="' . esc_attr( $subscribe_button_value ) . '" />';
		} else {
			// both form input actions
			$this->input_form_action = '<input type="submit" name="subscribe" value="' . esc_attr( $subscribe_button_value ) . '" />&nbsp;<input type="submit" name="unsubscribe" value="' . esc_attr( $unsubscribe_button_value ) . '" />';
		}

		// if ID is provided, get permalink
		$action = '';
		if ( is_numeric( $args['id'] ) ) {
			$action = ' action="' . get_permalink( $args['id'] ) . '"';
		} elseif ( 'home' === $args['id'] ) {
			$action = ' action="' . get_site_url() . '"';
		} elseif ( 'self' === $args['id'] ) {
			// Correct for Static front page redirect behaviour
			if ( 'page' === get_option( 'show_on_front' ) && is_front_page() ) {
				$post   = get_post( get_option( 'page_on_front' ) );
				$action = ' action="' . get_option( 'home' ) . '/' . $post->post_name . '/"';
			} else {
				$action = '';
			}
		} elseif ( $this->subscribe2_options['s2page'] > 0 ) {
			$action = ' action="' . get_permalink( $this->subscribe2_options['s2page'] ) . '"';
		}

		// allow remote setting of email in form
		if ( isset( $_REQUEST['email'] ) ) {
			$email = $this->sanitize_email( $_REQUEST['email'] );
		}
		if ( isset( $_REQUEST['email'] ) && false !== $this->validate_email( $email ) ) {
			$value = $email;
		} elseif ( 'true' === strtolower( $args['nojs'] ) ) {
			$value = '';
		} else {
			$value = __( 'Enter email address...', 'subscribe2-for-cp' );
		}

		// if wrap is true add paragraph html tags
		$wrap_text = '';
		if ( 'true' === strtolower( $args['wrap'] ) ) {
			$wrap_text = '</p><p>';
		}

		// deploy some anti-spam measures
		$antispam_text = '';
		if ( 'true' !== strtolower( $args['noantispam'] ) ) {
			$antispam_text  = '<span style="display:none !important">';
			$antispam_text .= '<label for="firstname">Leave This Blank:</label><input type="text" id="firstname" name="firstname" />';
			$antispam_text .= '<label for="lastname">Leave This Blank Too:</label><input type="text" id="lastname" name="lastname" />';
			$antispam_text .= '<label for="uri">Do Not Change This:</label><input type="text" id="uri" name="uri" value="http://" />';
			$antispam_text .= '</span>';
		}

		// get remote IP address
		$remote_ip = $this->get_remote_ip();

		// form name
		if ( 'true' === $args['widget'] ) {
			$form_name = 's2formwidget';
		} else {
			$form_name = 's2form';
		}

		// build default form
		if ( 'true' === strtolower( $args['nojs'] ) ) {
			$this->form = '<form name="' . $form_name . '" method="post"' . $action . '><input type="hidden" name="ip" value="' . esc_attr( $remote_ip ) . '" />' . $antispam_text . '<p><label for="s2email">' . __( 'Your email:', 'subscribe2-for-cp' ) . '</label><br><input type="email" name="email" id="s2email" value="' . $value . '" size="' . $args['size'] . '" />' . $wrap_text . $this->input_form_action . '</p></form>';
		} else {
			$this->form = '<form name="' . $form_name . '" method="post"' . $action . '><input type="hidden" name="ip" value="' . esc_attr( $remote_ip ) . '" />' . $antispam_text . '<p><label for="s2email">' . __( 'Your email:', 'subscribe2-for-cp' ) . '</label><br><input type="email" name="email" id="s2email" value="' . $value . '" size="' . $args['size'] . '" onfocus="if (this.value === \'' . $value . '\') {this.value = \'\';}" onblur="if (this.value === \'\') {this.value = \'' . $value . '\';}" />' . $wrap_text . $this->input_form_action . '</p></form>' . "\r\n";
		}
		$this->s2form = apply_filters( 's2_form', $this->form, $args );

		global $user_ID;
		if ( 0 !== $user_ID ) {
			return $this->profile;
		}

		if ( isset( $_POST['subscribe'] ) || isset( $_POST['unsubscribe'] ) ) {
			// anti spam sign up measure
			if ( ( isset( $_POST['firstname'] ) && '' !== $_POST['firstname'] ) || ( isset( $_POST['lastname'] ) && '' !== $_POST['lastname'] ) || ( isset( $_POST['uri'] ) && 'http://' !== $_POST['uri'] ) ) {
				// looks like some invisible-to-user fields were changed; falsely report success
				return $this->confirmation_sent;
			}
			$validation = (bool) apply_filters( 's2_form_submission', true );
			if ( true !== $validation ) {
				return (string) apply_filters( 's2_form_failed_validation', $this->s2form );
			}
			global $wpdb;
			$this->email = $this->sanitize_email( $_POST['email'] );
			if ( false === $this->validate_email( $this->email ) ) {
				$this->s2form = $this->s2form . $this->not_an_email;
			} elseif ( $this->is_barred( $this->email ) ) {
				$this->s2form = $this->s2form . $this->barred_domain;
			} else {
				$this->ip = $_POST['ip'];
				if ( is_int( $this->lockout ) && $this->lockout > 0 ) {
					$date = gmdate( 'H:i:s.u', $this->lockout );
					$ips  = $wpdb->get_col( $wpdb->prepare( "SELECT ip FROM $wpdb->subscribe2 WHERE date = CURDATE() AND time > SUBTIME(CURTIME(), %s)", $date ) );
					if ( in_array( $this->ip, $ips, true ) ) {
						return __( 'Slow down, you move too fast.', 'subscribe2-for-cp' );
					}
				}
				// does the supplied email belong to a registered user?
				$check = $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM $wpdb->users WHERE user_email = %s", $this->email ) );
				if ( null !== $check ) {
					// this is a registered email
					$this->s2form = $this->please_log_in;
				} else {
					// this is not a registered email
					// what should we do?
					if ( isset( $_POST['subscribe'] ) ) {
						// someone is trying to subscribe
						// lets see if they've tried to subscribe previously
						if ( '1' !== $this->is_public( $this->email ) ) {
							// the user is unknown or inactive
							$this->add( $this->email );
							$status = $this->send_confirm( 'add' );
							// set a variable to denote that we've already run, and shouldn't run again
							$this->filtered = 1;
							if ( $status ) {
								$this->s2form = $this->confirmation_sent;
							} else {
								$this->s2form = $this->error;
							}
						} else {
							// they're already subscribed
							$this->s2form = $this->already_subscribed;
						}
						$this->action = 'subscribe';
					} elseif ( isset( $_POST['unsubscribe'] ) ) {
						// is this email a subscriber?
						if ( false === $this->is_public( $this->email ) ) {
							$this->s2form = $this->s2form . $this->not_subscribed;
						} else {
							$status = $this->send_confirm( 'del' );
							// set a variable to denote that we've already run, and shouldn't run again
							$this->filtered = 1;
							if ( $status ) {
								$this->s2form = $this->confirmation_sent;
							} else {
								$this->s2form = $this->error;
							}
						}
						$this->action = 'unsubscribe';
					}
				}
			}
		}
		return $this->s2form;
	}

	/**
	 * Display form when deprecated <!--subscribe2--> is used
	 */
	public function filter( $content = '' ) {
		if ( '' === $content || ! strstr( $content, '<!--subscribe2-->' ) ) {
			return $content;
		}

		return preg_replace( '/(<p>)?(\n)*<!--subscribe2-->(\n)*(<\/p>)?/', do_shortcode( '[subscribe2]' ), $content );
	}

	/**
	 * Overrides the default query when handling a (un)subscription confirmation
	 * This is basically a trick: if the s2 variable is in the query string, just grab the first
	 * static page and override it's contents later with title_filter()
	 */
	public function query_filter() {
		// don't interfere if we've already done our thing
		if ( 1 === $this->filtered ) {
			return;
		}

		global $wpdb;

		// brute force Simple Facebook Connect to bypass compatiblity issues
		$priority = has_filter( 'wp_head', 'sfc_base_meta' );
		if ( false !== $priority ) {
			remove_action( 'wp_head', 'sfc_base_meta', $priority );
		}

		if ( 0 !== $this->subscribe2_options['s2page'] ) {
			return array(
				'page_id' => $this->subscribe2_options['s2page'],
			);
		} else {
			$id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status='publish' LIMIT 1" );
			if ( $id ) {
				return array(
					'page_id' => $id,
				);
			} else {
				return array(
					'showposts' => 1,
				);
			}
		}
	}

	/**
	 * Overrides the page title
	 */
	public function title_filter( $title ) {
		if ( in_the_loop() ) {
			$code   = $_GET['s2'];
			$action = intval( substr( $code, 0, 1 ) );
			if ( 1 === $action ) {
				return __( 'Subscription Confirmation', 'subscribe2-for-cp' );
			} else {
				return __( 'Unsubscription Confirmation', 'subscribe2-for-cp' );
			}
		} else {
			return $title;
		}
	}

	/**
	 * Confirm request from the link emailed to the user and email the admin
	 */
	public function confirm( $content = '' ) {
		if ( 1 === $this->filtered && '' !== $this->message ) {
			return $this->message;
		} elseif ( 1 === $this->filtered ) {
			return $content;
		}

		$code   = $_GET['s2'];
		$action = substr( $code, 0, 1 );
		$hash   = substr( $code, 1, 32 );
		$id     = intval( substr( $code, 33 ) );
		if ( $id ) {
			$this->email = $this->sanitize_email( $this->get_email( $id ) );
			if ( ! $this->email || wp_hash( $this->email ) !== $hash ) {
				return $this->no_such_email;
			}
		} else {
			return $this->no_such_email;
		}

		if ( '1' === $action ) {
			// make this subscription active
			$this->message = apply_filters( 's2_subscribe_confirmed', $this->added );
			if ( '1' !== $this->is_public( $this->email ) ) {
				$this->ip = esc_html( $this->get_remote_ip() );
				$this->toggle( $this->email );
				if ( 'subs' === $this->subscribe2_options['admin_email'] || 'both' === $this->subscribe2_options['admin_email'] ) {
					$this->admin_email( 'subscribe' );
				}
			}
			$this->filtered = 1;
		} elseif ( '0' === $action ) {
			// remove this public subscriber
			$this->message = apply_filters( 's2_unsubscribe_confirmed', $this->deleted );
			if ( '0' !== $this->is_public( $this->email ) ) {
				$this->delete( $this->email );
				if ( 'unsubs' === $this->subscribe2_options['admin_email'] || 'both' === $this->subscribe2_options['admin_email'] ) {
					$this->admin_email( 'unsubscribe' );
				}
			}
			$this->filtered = 1;
		}

		if ( '' !== $this->message ) {
			return $this->message;
		}
	}

	/**
	 * Prepare and send emails to admins on new subscriptions and unsubsriptions
	 */
	public function admin_email( $action ) {
		if ( ! in_array( $action, array( 'subscribe', 'unsubscribe' ), true ) ) {
			return false;
		}

		( '' === get_option( 'blogname' ) ) ? $subject = '' : $subject = '[' . stripslashes( html_entity_decode( get_option( 'blogname' ), ENT_QUOTES ) ) . '] ';
		if ( 'subscribe' === $action ) {
			$subject .= __( 'New Subscription', 'subscribe2-for-cp' );
			$message  = $this->email . ' ' . __( 'subscribed to email notifications!', 'subscribe2-for-cp' );
		} elseif ( 'unsubscribe' === $action ) {
			$subject .= __( 'New Unsubscription', 'subscribe2-for-cp' );
			$message  = $this->email . ' ' . __( 'unsubscribed from email notifications!', 'subscribe2-for-cp' );
		}

		$subject = html_entity_decode( $subject, ENT_QUOTES );
		$role    = array(
			'fields' => array(
				'user_email',
			),
			'role'   => 'administrator',
		);

		$wp_user_query = get_users( $role );
		$recipients    = array();

		foreach ( $wp_user_query as $user ) {
			$recipients[] = $user->user_email;
		}

		$recipients = apply_filters( 's2_admin_email', $recipients, $action );
		$headers    = $this->headers();
		// send individual emails so we don't reveal admin emails to each other
		foreach ( $recipients as $recipient ) {
			wp_mail( $recipient, $subject, $message, $headers );
		}
	}

	/**
	 * Add hook for Minimeta Widget plugin
	 */
	public function add_minimeta() {
		if ( 0 !== $this->subscribe2_options['s2page'] ) {
			echo '<li><a href="' . esc_url( get_permalink( $this->subscribe2_options['s2page'] ) ) . '">' . esc_html__( '[Un]Subscribe to Posts', 'subscribe2-for-cp' ) . '</a></li>' . "\r\n";
		}
	}

	/**
	 * Check email is not from a barred domain
	 */
	public function is_barred( $email = '' ) {
		if ( '' === $email ) {
			return false;
		}

		list( , $domain ) = explode( '@', $email, 2 );

		$domain = '@' . $domain;

		foreach ( preg_split( '/[\s,]+/', $this->subscribe2_options['barred'] ) as $barred_domain ) {
			if ( false !== strpos( $barred_domain, '!' ) ) {
				$url   = explode( '.', str_replace( '!', '', $barred_domain ) );
				$count = count( $url );
				// make sure our exploded domain has at least 2 components e.g. yahoo.*
				if ( $count < 2 ) {
					continue;
				}
				for ( $i = 0; $i < $count; $i++ ) {
					if ( '*' === $url[ $i ] ) {
						unset( $url[ $i ] );
					}
				}

				$new_barred_domain = '@' . strtolower( trim( implode( '.', $url ) ) );

				if ( false !== strpos( $barred_domain, '*' ) ) {
					$new_barred_subdomain = '.' . strtolower( trim( implode( '.', $url ) ) );
					if ( false !== stripos( $domain, $new_barred_domain ) || false !== stripos( $domain, $new_barred_subdomain ) ) {
						return false;
					}
				} else {
					if ( false !== stripos( $domain, $new_barred_domain ) ) {
						return false;
					}
				}
			}

			if ( false === strpos( $barred_domain, '!' ) && false !== strpos( $barred_domain, '*' ) ) {
				// wildcard and explictly allowed checking
				$url   = explode( '.', str_replace( '!', '', $barred_domain ) );
				$count = count( $url );
				// make sure our exploded domain has at least 2 components e.g. yahoo.*
				if ( $count < 2 ) {
					continue;
				}
				for ( $i = 0; $i < $count; $i++ ) {
					if ( '*' === $url[ $i ] ) {
						unset( $url[ $i ] );
					}
				}

				$new_barred_domain    = '@' . strtolower( trim( implode( '.', $url ) ) );
				$new_barred_subdomain = '.' . strtolower( trim( implode( '.', $url ) ) );

				if ( false !== stripos( $domain, $new_barred_domain ) || false !== stripos( $domain, $new_barred_subdomain ) ) {
					return true;
				}
			} else {
				// direct domain string comparison
				$barred_domain = '@' . $barred_domain;
				if ( strtolower( $domain ) === strtolower( trim( $barred_domain ) ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Collect and return the IP address of the remote client machine
	 */
	public function get_remote_ip() {
		$remote_ip = false;

		// In order of preference, with the best ones for this purpose first
		$address_headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $address_headers as $header ) {
			if ( array_key_exists( $header, $_SERVER ) ) {
				// HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				// addresses. The first one is the original client. It can't be
				// trusted for authenticity, but we don't need to for this purpose.
				$address_chain = explode( ',', $_SERVER[ $header ] );
				$remote_ip     = trim( $address_chain[0] );
				break;
			}
		}

		return $remote_ip;
	}

	/**
	 * Enqueue javascript ip updater code
	 */
	public function js_ip_script() {
		wp_register_script( 's2_ip_updater', S2URL . 'include/s2-ip-updater' . $this->script_debug . '.js', array(), '1.1', true );
		wp_enqueue_script( 's2_ip_updater' );
	}

	/**
	 * Add ip updater library to footer
	 */
	public function js_ip_library_script() {
		$args = array(
			'format'   => 'jsonp',
			'callback' => 'getip',
		);
		wp_enqueue_script( 's2_ip_library', add_query_arg( $args, 'https://api.ipify.org' ), array(), S2VERSION, true );
	}

	/**
	 * Reformat escaped link to IPify library
	 */
	public function tag_replace_ampersand( $tag ) {
		if ( strstr( $tag, 'ipify' ) !== false ) {
			$tag = str_replace( '&#038;', '&', $tag );
		}

		return $tag;
	}

	/* ===== define some variables ===== */
	public $profile = '';
}
