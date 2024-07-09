<?php
class S2_Counter_Widget extends WP_Widget {
	/**
	 * Declares the S2_Counter_widget class.
	 */
	public function __construct() {
		$widget_options = array(
			'classname'                   => 's2_counter',
			'description'                 => esc_html__( 'Subscriber Counter widget for Subscribe2', 'subscribe2-for-cp' ),
			'customize_selective_refresh' => true,
			'show_instance_in_rest'       => true,
		);

		$control_options = array(
			'width'  => 250,
			'height' => 500,
		);
		parent::__construct( 's2_counter', esc_html__( 'Subscribe2 Counter', 'subscribe2-for-cp' ), $widget_options, $control_options );
	}

	/**
	 * Displays the Widget
	 */
	public function widget( $args, $instance ) {
		$title      = empty( $instance['title'] ) ? esc_html__( 'Subscriber Count', 'subscribe2-for-cp' ) : $instance['title'];
		$s2w_bg     = empty( $instance['s2w_bg'] ) ? '#e3dacf' : $instance['s2w_bg'];
		$s2w_fg     = empty( $instance['s2w_fg'] ) ? '#345797' : $instance['s2w_fg'];
		$s2w_width  = empty( $instance['s2w_width'] ) ? 82 : $instance['s2w_width'];
		$s2w_height = empty( $instance['s2w_height'] ) ? 16 : $instance['s2w_height'];
		$s2w_font   = empty( $instance['s2w_font'] ) ? 11 : $instance['s2w_font'];

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		$registered = s2cp()->get_registered();
		$confirmed  = s2cp()->get_public();
		$count      = ( count( $registered ) + count( $confirmed ) );
		echo wp_kses_post( '<ul><div style="text-align:center; background-color:' . $s2w_bg . '; color:' . $s2w_fg . '; width:' . $s2w_width . 'px; height:' . $s2w_height . 'px; font:' . $s2w_font . 'pt Verdana, Arial, Helvetica, sans-serif; vertical-align:middle; padding:3px; border:1px solid #444;">' );
		echo esc_html( $count );
		echo '</div></ul>';
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Saves the widgets settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = wp_strip_all_tags( stripslashes( $new_instance['title'] ) );

		$background_color = wp_strip_all_tags( stripslashes( $new_instance['s2w_bg'] ) );
		if ( null !== $this->sanitize_color( $background_color ) ) {
			$instance['s2w_bg'] = $background_color;
		}
		$foreground_color = wp_strip_all_tags( stripslashes( $new_instance['s2w_fg'] ) );
		if ( null !== $this->sanitize_color( $foreground_color ) ) {
			$instance['s2w_fg'] = $foreground_color;
		}

		$instance['s2w_width']  = (int) wp_strip_all_tags( stripslashes( $new_instance['s2w_width'] ) );
		$instance['s2w_height'] = (int) wp_strip_all_tags( stripslashes( $new_instance['s2w_height'] ) );
		$instance['s2w_font']   = (int) wp_strip_all_tags( stripslashes( $new_instance['s2w_font'] ) );

		return $instance;
	}

	/**
	 * Creates the edit form for the widget.
	 */
	public function form( $instance ) {
		// set some defaults
		$options = get_option( 'widget_s2counter' );
		if ( false === $options ) {
			$defaults = array(
				'title'      => 'Subscriber Count',
				's2w_bg'     => '#e3dacf',
				's2w_fg'     => '#345797',
				's2w_width'  => 82,
				's2w_height' => 16,
				's2w_font'   => 11,
			);
		} else {
			$defaults = array(
				'title'      => $options['title'],
				's2w_bg'     => $options['s2w_bg'],
				's2w_fg'     => $options['s2w_fg'],
				's2w_width'  => $options['s2w_width'],
				's2w_height' => $options['s2w_height'],
				's2w_font'   => $options['s2w_font'],
			);
			delete_option( 'widget_s2counter' );
		}
		$instance = wp_parse_args( (array) $instance, $defaults );
		// Be sure you format your options to be valid HTML attributes.
		$s2w_title  = htmlspecialchars( $instance['title'], ENT_QUOTES );
		$s2w_bg     = htmlspecialchars( $instance['s2w_bg'], ENT_QUOTES );
		$s2w_fg     = htmlspecialchars( $instance['s2w_fg'], ENT_QUOTES );
		$s2w_width  = htmlspecialchars( $instance['s2w_width'], ENT_QUOTES );
		$s2w_height = htmlspecialchars( $instance['s2w_height'], ENT_QUOTES );
		$s2w_font   = htmlspecialchars( $instance['s2w_font'], ENT_QUOTES );
		echo '<div>' . "\r\n";
		echo '<fieldset><legend><label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">' . esc_html__( 'Widget Title', 'subscribe2-for-cp' ) . '</label></legend>' . "\r\n";
		echo '<input type="text" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" value="' . esc_attr( $s2w_title ) . '">' . "\r\n";
		echo '</fieldset>' . "\r\n";

		echo '<fieldset>' . "\r\n";
		echo '<legend>' . esc_html__( 'Color Scheme', 'subscribe2-for-cp' ) . '</legend>' . "\r\n";
		echo '<table style="border:0; padding:0; margin:0 0 12px 0; border-collapse:collapse;" align="center">' . "\r\n";
		echo '<tr><td><label for="' . esc_attr( $this->get_field_id( 's2w_bg' ) ) . '">' . esc_html__( 'Body', 'subscribe2-for-cp' ) . '</label></td>' . "\r\n";
		echo '<td><input type="text" name="' . esc_attr( $this->get_field_name( 's2w_bg' ) ) . '" id="' . esc_attr( $this->get_field_id( 's2w_bg' ) ) . '" maxlength="6" value="' . esc_attr( $s2w_bg ) . '" class="colorpickerField" style="width:60px;"></td></tr>' . "\r\n";
		echo '<tr><td><label for="' . esc_attr( $this->get_field_id( 's2w_fg' ) ) . '">' . esc_html__( 'Text', 'subscribe2-for-cp' ) . '</label></td>' . "\r\n";
		echo '<td><input type="text" name="' . esc_attr( $this->get_field_name( 's2w_fg' ) ) . '" id="' . esc_attr( $this->get_field_id( 's2w_fg' ) ) . '" maxlength="6" value="' . esc_attr( $s2w_fg ) . '" class="colorpickerField" style="width:60px;"></td></tr>' . "\r\n";
		echo '</table></fieldset>';

		echo '<fieldset>' . "\r\n";
		echo '<legend>' . esc_html__( 'Width, Height and Font Size', 'subscribe2-for-cp' ) . '</legend>' . "\r\n";
		echo '<table style="border:0; padding:0; margin:0 0 12px 0; border-collapse:collapse;" align="center">' . "\r\n";
		echo '<tr><td><label for="' . esc_attr( $this->get_field_id( 's2w_width' ) ) . '">' . esc_html__( 'Width', 'subscribe2-for-cp' ) . '</label></td>' . "\r\n";
		echo '<td><input type="text" name="' . esc_attr( $this->get_field_name( 's2w_width' ) ) . '" id="' . esc_attr( $this->get_field_id( 's2w_width' ) ) . '" value="' . esc_attr( $s2w_width ) . '"></td></tr>' . "\r\n";
		echo '<tr><td><label for="' . esc_attr( $this->get_field_id( 's2w_height' ) ) . '">' . esc_html__( 'Height', 'subscribe2-for-cp' ) . '</label></td>' . "\r\n";
		echo '<td><input type="text" name="' . esc_attr( $this->get_field_name( 's2w_height' ) ) . '" id="' . esc_attr( $this->get_field_id( 's2w_height' ) ) . '" value="' . esc_attr( $s2w_height ) . '"></td></tr>' . "\r\n";
		echo '<tr><td><label for="' . esc_attr( $this->get_field_id( 's2w_font' ) ) . '">' . esc_html__( 'Font', 'subscribe2-for-cp' ) . '</label></td>' . "\r\n";
		echo '<td><input type="text" name="' . esc_attr( $this->get_field_name( 's2w_font' ) ) . '" id="' . esc_attr( $this->get_field_id( 's2w_font' ) ) . '" value="' . esc_attr( $s2w_font ) . '"></td></tr>' . "\r\n";
		echo '</table></fieldset></div>' . "\r\n";
	}

	/**
	 * Sanitize hex color input
	 */
	private function sanitize_color( $color ) {
		if ( '' === $color || null === $color ) {
			return null;
		}

		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}
	}
}
