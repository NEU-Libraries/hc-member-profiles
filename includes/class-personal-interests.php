<?php
/**
 * Legacy class to support personal interests.
 *
 * Deprecated - planned to roll into field type class.
 *
 * @package Hc_Member_Profiles
 */

/**
 * Filters to control tax saving/loading.
 */
class Personal_Interests {

	/**
	 * Cookie name.
	 *
	 * @var string
	 */
	static $cookie_name = 'personal_interest_term_taxonomy_id';

	/**
	 * Querystring param name.
	 *
	 * @var string
	 */
	static $query_param = 'personal_interests';

	/**
	 * Save terms.
	 *
	 * @param int $user_id User.
	 */
	static function save_personal_interests( $user_id ) {
		$tax = get_taxonomy( 'mla_personal_interests' );
		$term_taxonomy_ids = array();

		// If array add any new keywords.
		if ( is_array( $_POST['personal-interests'] ) ) {
			foreach ( $_POST['personal-interests'] as $term_id ) {
				$term_key = wpmn_term_exists( $term_id, 'mla_academic_interests' );
				if ( empty( $term_key ) ) {
					$term_key = wpmn_insert_term( sanitize_text_field( $term_id ), 'mla_academic_interests' );
				}
				if ( ! is_wp_error( $term_key ) ) {
					$term_ids[] = intval( $term_key['term_id'] );
				} else {
					error_log( '*****MLA Academic Interests Error - bad tag*****' . var_export( $term_key, true ) );
				}
				if ( ! is_null( $term_key ) && ! is_wp_error( $term_key ) ) {
					$term_taxonomy_ids[] = is_array( $term_key ) ? $term_key['term_id'] : $term_key;
				}
			}
		}

		// Set user meta for theme query.
		delete_user_meta( $user_id, 'personal_interests' );
		foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
			add_user_meta( $user_id, 'personal_interests', $term_taxonomy_id, false );
		}
	}

	/**
	 * Set cookie.
	 */
	static function set_personal_interests_cookie_query() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$term_taxonomy_id = $_COOKIE[ self::$cookie_name ];
		} else {
			$interest = isset( $_REQUEST[ self::$query_param ] ) ? $_REQUEST[ self::$query_param ] : null;

			if ( ! empty( $interest ) ) {
				$term = wpmn_get_term_by( 'name', $interest, 'mla_academic_interests' );

				setcookie( self::$cookie_name, $term->term_taxonomy_id, null, '/' );
				$_COOKIE[ self::$cookie_name ] = $term->term_taxonomy_id;
			} else {
				setcookie( self::$cookie_name, null, null, '/' );
			}
		}
	}

	/**
	 * Injects markup to support filtering a search/list by personal interest in member directory
	 *
	 * @param string $template Template path.
	 */
	static function add_personal_interests_to_directory( $template ) {
		if ( in_array( 'members/members-loop.php', (array) $template ) && isset( $_COOKIE[ self::$cookie_name ] ) ) {
			$term_taxonomy_id = $_COOKIE[ self::$cookie_name ];

			if ( ! empty( $term_taxonomy_id ) ) {
				$term = wpmn_get_term_by( 'term_taxonomy_id', $term_taxonomy_id, 'mla_academic_interests' );
			}

			if ( $term ) {
				echo sprintf(
					'<div id="academic_interest">
						<h4>Academic Interest: %1$s <sup><a href="#" id="remove_academic_interest_filter">x</a></sup></h4>
					</div>
					<div id="message" class="academic_interest_removed" class="info notice" style="display:none">
						<p>"Academic Interest: %1$s" filter removed</p>
					</div>',
					$term->name
				);
			}
		}

		return $template;
	}

}
