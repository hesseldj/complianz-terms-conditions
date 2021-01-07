<?php
defined( 'ABSPATH' ) or die( "you do not have acces to this page!" );



if ( ! function_exists( 'cmplz_tc_get_template' ) ) {
	/**
	 * Get a template based on filename, overridable in theme dir
	 * @param string $filename
	 * @param array $args
	 * @return string
	 */

	function cmplz_tc_get_template( $filename , $args = array() ) {

		$file       = apply_filters('cmplz_tc_template_file', trailingslashit( cmplz_tc_path ) . 'templates/' . $filename, $filename);

		$theme_file = trailingslashit( get_stylesheet_directory() )
		              . trailingslashit( basename( cmplz_tc_path ) )
		              . 'templates/' . $filename;

		if ( !file_exists( $file ) ) {
		    return false;
        }

		if ( file_exists( $theme_file ) ) {
			$file = $theme_file;
		}

		if ( strpos( $file, '.php' ) !== false ) {
			ob_start();
			require $file;
			$contents = ob_get_clean();
		} else {
			$contents = file_get_contents( $file );
		}

		if ( !empty($args) && is_array($args) ) {
			foreach($args as $fieldname => $value ) {
				$contents = str_replace( '{'.$fieldname.'}', $value, $contents );
			}
		}

		return $contents;
	}
}

if ( ! function_exists( 'cmplz_tc_get_value' ) ) {

	/**
	 * Get value for an a complianz option
	 * For usage very early in the execution order, use the $page option. This bypasses the class usage.
	 *
	 * @param string $fieldname
	 * @param bool|int $post_id
	 * @param bool|string $page
	 * @param bool $use_default
	 * @param bool $use_translate
	 *
	 * @return array|bool|mixed|string
	 */

	function cmplz_tc_get_value(
		$fieldname, $post_id = false, $page = false, $use_default = true, $use_translate = true
	) {
		if ( ! is_numeric( $post_id ) ) {
			$post_id = false;
		}

		if ( ! $page && ! isset( COMPLIANZ_TC::$config->fields[ $fieldname ] ) ) {
			return false;
		}

		//if  a post id is passed we retrieve the data from the post
		if ( ! $page ) {
			$page = COMPLIANZ_TC::$config->fields[ $fieldname ]['source'];
		}

		$fields = get_option( 'complianz_tc_options_' . $page );
		$default = ( $use_default && $page && isset( COMPLIANZ_TC::$config->fields[ $fieldname ]['default'] ) )
            ? COMPLIANZ_TC::$config->fields[ $fieldname ]['default'] : '';
        //@todo $default = apply_filters( 'cmplz_tc_default_value', $default, $fieldname );

		$value   = isset( $fields[ $fieldname ] ) ? $fields[ $fieldname ] : $default;


		/*
         * Translate output
         *
         * */
        if ($use_translate) {

            $type = isset(COMPLIANZ_TC::$config->fields[$fieldname]['type'])
                ? COMPLIANZ_TC::$config->fields[$fieldname]['type'] : false;
            if ($type === 'cookies' || $type === 'thirdparties'
                || $type === 'processors'
            ) {
                if (is_array($value)) {

                    //this is for example a cookie array, like ($item = cookie("name"=>"_ga")

                    foreach ($value as $item_key => $item) {
                        //contains the values of an item
                        foreach ($item as $key => $key_value) {
                            if (function_exists('pll__')) {
                                $value[$item_key][$key] = pll__($item_key . '_'
                                    . $fieldname
                                    . "_" . $key);
                            }
                            if (function_exists('icl_translate')) {
                                $value[$item_key][$key]
                                    = icl_translate('complianz',
                                    $item_key . '_' . $fieldname . "_" . $key,
                                    $key_value);
                            }

                            $value[$item_key][$key]
                                = apply_filters('wpml_translate_single_string',
                                $key_value, 'complianz',
                                $item_key . '_' . $fieldname . "_" . $key);
                        }
                    }
                }
            } else {
                if (isset(COMPLIANZ_TC::$config->fields[$fieldname]['translatable'])
                    && COMPLIANZ_TC::$config->fields[$fieldname]['translatable']
                ) {
                    if (function_exists('pll__')) {
                        $value = pll__($value);
                    }
                    if (function_exists('icl_translate')) {
                        $value = icl_translate('complianz', $fieldname, $value);
                    }

                    $value = apply_filters('wpml_translate_single_string', $value,
                        'complianz', $fieldname);
                }
            }

        }

		return $value;
	}
}


if ( ! function_exists( 'cmplz_tc_intro' ) ) {

	/**
	 * @param string $msg
	 *
	 * @return string|void
	 */

	function cmplz_tc_intro( $msg ) {
		if ( $msg == '' ) {
			return;
		}
		$html = "<div class='cmplz-panel cmplz-notification cmplz-intro'>{$msg}</div>";

		echo $html;

	}
}

if ( ! function_exists( 'cmplz_tc_notice' ) ) {
	/**
	 * @param string $msg
	 * @param string $type notice | warning | success
	 * @param bool   $remove_after_change
	 * @param bool   $echo
     * @param array  $condition $condition['question'] $condition['answer']
	 *
	 * @return string|void
	 */
	function cmplz_tc_notice( $msg, $type = 'notice', $remove_after_change = false, $echo = true, $condition = false) {
		if ( $msg == '' ) {
			return;
		}

		// Condition
        $condition_check = "";
        $condition_question = "";
        $condition_answer = "";
        $cmplz_hidden = "";
		if ($condition) {
		    $condition_check = "condition-check";
		    $condition_question = "data-condition-question='{$condition['question']}'";
		    $condition_answer = "data-condition-answer='{$condition['answer']}'";
		    $args['condition'] = array($condition['question'] => $condition['answer']);
            $cmplz_hidden = cmplz_tc_field::this()->condition_applies($args) ? "" : "cmplz-hidden";;
        }

        // Hide
		$remove_after_change_class = $remove_after_change ? "cmplz-remove-after-change" : "";

		$html = "<div class='cmplz-panel cmplz-notification cmplz-{$type} {$remove_after_change_class} {$cmplz_hidden} {$condition_check}' {$condition_question} {$condition_answer}'>{$msg}</div>";

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
}

if ( ! function_exists( 'cmplz_tc_notification' ) ) {
	/**
	 * @param string $msg
	 * @param string $type notice | warning | success
	 * @param bool   $remove_after_change
	 * @param bool   $echo
	 * @param bool|array  $condition $condition['question'] $condition['answer']
	 *
	 * @return string|void
	 */

	function cmplz_tc_notification( $msg, $type = 'notice', $remove_after_change = false, $echo = true, $condition = false) {
		if ( $msg == '' ) {
			return;
		}

		// Condition
		$condition_check = "";
		$condition_question = "";
		$condition_answer = "";
		$cmplz_hidden = "";
		if ($condition) {
			$condition_check = "condition-check";
			$condition_question = "data-condition-question='{$condition['question']}'";
			$condition_answer = "data-condition-answer='{$condition['answer']}'";
			$args['condition'] = array($condition['question'] => $condition['answer']);
			$cmplz_hidden = cmplz_tc_field::this()->condition_applies($args) ? "" : "cmplz-hidden";;
		}

		// Hide
		$remove_after_change_class = $remove_after_change ? "cmplz-remove-after-change" : "";

		$html = "<div class='cmplz-help-modal cmplz-notice cmplz-{$type} {$remove_after_change_class} {$cmplz_hidden} {$condition_check}' {$condition_question} {$condition_answer}>{$msg}</div>";

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
}

if ( ! function_exists( 'cmplz_tc_update_option' ) ) {
	/**
	 * Save a complianz option
	 * @param string $page
	 * @param string $fieldname
	 * @param mixed $value
	 */
	function cmplz_tc_update_option( $page, $fieldname, $value ) {
		$options               = get_option( 'complianz_tc_options_' . $page );
		$options[ $fieldname ] = $value;
		if ( ! empty( $options ) ) {
			update_option( 'complianz_tc_options_' . $page, $options );
		}
	}
}


if ( ! function_exists( 'cmplz_tc_localize_date' ) ) {

	function cmplz_tc_localize_date( $date ) {
		$month             = date( 'F', strtotime( $date ) ); //june
		$month_localized   = __( $month ); //juni
		$date              = str_replace( $month, $month_localized, $date );
		$weekday           = date( 'l', strtotime( $date ) ); //wednesday
		$weekday_localized = __( $weekday ); //woensdag
		$date              = str_replace( $weekday, $weekday_localized, $date );

		return $date;
	}
}

if (!function_exists('cmplz_tc_read_more')) {
	/**
	 * Create a generic read more text with link for help texts.
	 *
	 * @param string $url
	 * @param bool   $add_space
	 *
	 * @return string
	 */
	function cmplz_tc_read_more( $url, $add_space = true ) {
		$html
			= sprintf( __( "For more information on this subject, please read this %sarticle%s",
			'complianz-terms-conditions' ), '<a target="_blank" href="' . $url . '">',
			'</a>' );
		if ( $add_space ) {
			$html = '&nbsp;' . $html;
		}

		return $html;
	}
}

register_activation_hook( __FILE__, 'cmplz_tc_set_activation_time_stamp' );
if ( ! function_exists( 'cmplz_tc_set_activation_time_stamp' ) ) {
	function cmplz_tc_set_activation_time_stamp( $networkwide ) {
		update_option( 'cmplz_tc_activation_time', time() );
	}
}

if ( ! function_exists( 'cmplz_tc_allowed_html' ) ) {
	function cmplz_tc_allowed_html() {

		$allowed_tags = array(
			'a'          => array(
				'class'  => array(),
				'href'   => array(),
				'rel'    => array(),
				'title'  => array(),
				'target' => array(),
				'id' => array(),
			),
			'button'     => array(
				'id'  => array(),
				'class'  => array(),
				'href'   => array(),
				'rel'    => array(),
				'title'  => array(),
				'target' => array(),
			),
			'b'          => array(),
			'br'         => array(),
			'blockquote' => array(
				'cite' => array(),
			),
			'div'        => array(
				'class' => array(),
				'id'    => array(),
			),
			'h1'         => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'h5'         => array(),
			'h6'         => array(),
			'i'          => array(),
			'input'      => array(
				'type'        => array(),
				'class'       => array(),
				'id'          => array(),
				'required'    => array(),
				'value'       => array(),
				'placeholder' => array(),
				'data-category' => array(),
				'style' => array(
					'color' => array(),
				),			),
			'img'        => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'label'      => array(
				'for' => array(),
				'class' => array(),
				'style' => array(
					'visibility' => array(),
				),
			),
			'li'         => array(
				'class' => array(),
				'id'    => array(),
			),
			'ol'         => array(
				'class' => array(),
				'id'    => array(),
			),
			'p'          => array(
				'class' => array(),
				'id'    => array(),
			),
			'span'       => array(
				'class' => array(),
				'title' => array(),
				'style' => array(
					'color' => array(),
					'display' => array(),
				),
				'id'    => array(),
			),
			'strong'     => array(),
			'table'      => array(
				'class' => array(),
				'id'    => array(),
			),
			'tr'         => array(),
			'svg'         => array(
				'width' => array(),
				'height' => array(),
				'viewBox' => array(),
			),
			'polyline'    => array(
				'points' => array(),

			),
			'path'    => array(
				'd' => array(),

			),
			'style'      => array(),
			'td'         => array( 'colspan' => array(), 'scope' => array() ),
			'th'         => array( 'scope' => array() ),
			'ul'         => array(
				'class' => array(),
				'id'    => array(),
			),
		);

		return apply_filters( "cmplz_tc_allowed_html", $allowed_tags );
	}
}

/**
 * Check if this field is translatable
 *
 * @param $fieldname
 *
 * @return bool
 */

if ( ! function_exists( 'cmplz_tc_translate' ) ) {
	function cmplz_tc_translate( $value, $fieldname ) {
		if ( function_exists( 'pll__' ) ) {
			$value = pll__( $value );
		}

		if ( function_exists( 'icl_translate' ) ) {
			$value = icl_translate( 'complianz', $fieldname, $value );
		}

		$value = apply_filters( 'wpml_translate_single_string', $value, 'complianz', $fieldname );

		return $value;
	}
}

/**
 * Registrer a translation
 *
 * @param $fieldname
 *
 * @return bool
 */

if ( ! function_exists( 'cmplz_tc_register_translation' ) ) {

	function cmplz_tc_register_translation( $string, $fieldname ) {
		//polylang
		if ( function_exists( "pll_register_string" ) ) {
			pll_register_string( $fieldname, $string, 'complianz' );
		}

		//wpml
		if ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'complianz', $fieldname, $string );
		}

		do_action( 'wpml_register_single_string', 'complianz', $fieldname,
			$string );

	}
}

if ( ! function_exists( 'cmplz_tc_uses_gutenberg' ) ) {
	function cmplz_tc_uses_gutenberg() {

		if ( function_exists( 'has_block' )
		     && ! class_exists( 'Classic_Editor' )
		) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'cmplz_tc_user_can_manage' ) ) {
	function cmplz_tc_user_can_manage() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}
}


if ( ! function_exists( 'cmplz_tc_array_filter_multidimensional' ) ) {
	function cmplz_tc_array_filter_multidimensional(
		$array, $filter_key, $filter_value
	) {
		$new = array_filter( $array,
			function ( $var ) use ( $filter_value, $filter_key ) {
				return isset( $var[ $filter_key ] ) ? ( $var[ $filter_key ]
				                                        == $filter_value )
					: false;
			} );

		return $new;
	}
}