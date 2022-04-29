<?php
if ( ! function_exists( 'add_action' ) ) {
	exit();
}

global $current_user;

// was anything POSTed?
if ( isset( $_POST['s2_admin'] ) && 'mail' === $_POST['s2_admin'] ) {
	if ( false === wp_verify_nonce( $_REQUEST['_wpnonce'], 'subscribe2-write_subscribers' . S2VERSION ) ) {
		die( '<p>' . esc_html__( 'Security error! Your request cannot be completed.', 'subscribe2-for-cp' ) . '</p>' );
	}

	$subject = html_entity_decode( stripslashes( wp_kses( s2cp()->substitute( $_POST['subject'] ), '' ) ), ENT_QUOTES );
	$body    = wpautop( s2cp()->substitute( stripslashes( $_POST['content'] ) ), true );
	if ( '' !== $current_user->display_name || '' !== $current_user->user_email ) {
		s2cp()->myname  = html_entity_decode( $current_user->display_name, ENT_QUOTES );
		s2cp()->myemail = $current_user->user_email;
	}
	if ( isset( $_POST['send'] ) ) {
		if ( 'confirmed' === $_POST['what'] ) {
			$recipients = s2cp()->get_public();
		} elseif ( 'unconfirmed' === $_POST['what'] ) {
			$recipients = s2cp()->get_public( 0 );
		} elseif ( 'public' === $_POST['what'] ) {
			$confirmed   = s2cp()->get_public();
			$unconfirmed = s2cp()->get_public( 0 );
			$recipients  = array_merge( (array) $confirmed, (array) $unconfirmed );
		} elseif ( is_numeric( $_POST['what'] ) ) {
			$category   = intval( $_POST['what'] );
			$recipients = s2cp()->get_registered( "cats=$category" );
		} elseif ( 'all_users' === $_POST['what'] ) {
			$recipients = s2cp()->get_all_registered();
		} elseif ( 'all' === $_POST['what'] ) {
			$confirmed   = s2cp()->get_public();
			$unconfirmed = s2cp()->get_public( 0 );
			$registered  = s2cp()->get_all_registered();
			$recipients  = array_merge( (array) $confirmed, (array) $unconfirmed, (array) $registered );
		} else {
			$recipients = s2cp()->get_registered();
		}
	} elseif ( isset( $_POST['preview'] ) ) {
		global $user_email;
		$recipients[] = $user_email;
	}

	$uploads = array();
	if ( ! empty( $_FILES ) ) {
		foreach ( $_FILES['file']['name'] as $key => $value ) {
			if ( 0 === $_FILES['file']['error'][ $key ] ) {
				$file = array(
					'name'     => $_FILES['file']['name'][ $key ],
					'type'     => $_FILES['file']['type'][ $key ],
					'tmp_name' => $_FILES['file']['tmp_name'][ $key ],
					'error'    => $_FILES['file']['error'][ $key ],
					'size'     => $_FILES['file']['size'][ $key ],
				);

				$uploads[] = wp_handle_upload(
					$file,
					array(
						'test_form' => false,
					)
				);
			}
		}
	}
	$attachments = array();
	if ( ! empty( $uploads ) ) {
		foreach ( $uploads as $upload ) {
			if ( ! isset( $upload['error'] ) ) {
				$attachments[] = $upload['file'];
			} else {
				$upload_error = $upload['error'];
			}
		}
	}

	// perform some error checking
	if ( empty( $body ) ) {
		$success       = false;
		$error_message = __( 'Your email was empty', 'subscribe2-for-cp' );
	} elseif ( isset( $upload_error ) ) {
		$success       = false;
		$error_message = $upload_error;
	} else {
		$success       = s2cp()->mail( $recipients, $subject, $body, 'html', $attachments );
		$error_message = __( 'Check your settings and check with your hosting provider', 'subscribe2-for-cp' );
	}

	// report user message
	if ( $success ) {
		if ( isset( $_POST['preview'] ) ) {
			$message = '<p class="s2_message">' . __( 'Preview message sent!', 'subscribe2-for-cp' ) . '</p>';
		} elseif ( isset( $_POST['send'] ) ) {
			$message = '<p class="s2_message">' . __( 'Message sent!', 'subscribe2-for-cp' ) . '</p>';
		}
	} else {
		global $phpmailer;
		$message = '<p class="s2_error">' . __( 'Message failed!', 'subscribe2-for-cp' ) . '</p>' . $error_message . $phpmailer->ErrorInfo;
	}
	echo '<div id="message" class="updated"><strong><p>' . wp_kses_post( $message ) . '</p></strong></div>' . "\r\n";
}

// show our form
echo '<div class="wrap">';
echo '<h1>' . esc_html__( 'Send an email to subscribers', 'subscribe2-for-cp' ) . '</h1>' . "\r\n";
echo '<form method="post" enctype="multipart/form-data">' . "\r\n";

wp_nonce_field( 'subscribe2-write_subscribers' . S2VERSION );

if ( isset( $_POST['subject'] ) ) {
	$subject = stripslashes( esc_html( $_POST['subject'] ) );
} else {
	$subject = __( 'A message from', 'subscribe2-for-cp' ) . ' ' . html_entity_decode( get_option( 'blogname' ), ENT_QUOTES );
}
if ( ! isset( $_POST['content'] ) ) {
	$body = '';
}
echo '<p><label>' . esc_html__( 'Subject', 'subscribe2-for-cp' ) . ': <input type="text" size="69" name="subject" value="' . esc_attr( $subject ) . '" /></label> <br><br>';
echo '<label><span class="screen-reader-text">' . esc_html__( 'Email body', 'subscribe2-for-cp' ) . '</span><textarea rows="12" cols="75" name="content">' . esc_textarea( $body ) . '</textarea></label>';
echo "<br><div id=\"upload_files\"><input type=\"file\" name=\"file[]\"></div>\r\n";
echo '<input type="button" class="button-secondary" name="addmore" value="' . esc_attr( __( 'Add More Files', 'subscribe2-for-cp' ) ) . "\" onClick=\"add_file_upload();\" />\r\n";
echo "<br><br>\r\n";
echo esc_html__( 'Recipients:', 'subscribe2-for-cp' ) . ' ';
s2cp()->display_subscriber_dropdown( apply_filters( 's2_subscriber_dropdown_default', 'registered' ), false );
echo '<input type="hidden" name="s2_admin" value="mail" />';
echo '<p class="submit"><input type="submit" class="button-secondary" name="preview" value="' . esc_attr( __( 'Preview', 'subscribe2-for-cp' ) ) . '" />&nbsp;<input type="submit" class="button-primary" name="send" value="' . esc_attr( __( 'Send', 'subscribe2-for-cp' ) ) . '" /></p>';
echo '</form></div>' . "\r\n";
echo '<div style="clear: both;"><p>&nbsp;</p></div>';
?>
<script>
//<![CDATA[
function add_file_upload() {
	var div = document.getElementById( 'upload_files' );
	var field = div.getElementsByTagName( 'input' )[0];
	div.appendChild( document.createElement( 'br' ) );
	div.appendChild( field.cloneNode( false ) );
}
//]]>
</script>
<?php
require ABSPATH . 'wp-admin/admin-footer.php';
// just to be sure
die;
