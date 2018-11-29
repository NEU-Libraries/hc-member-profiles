<?php
/**
 * HC Member Profiles field types
 *
 * @package Hc_Member_Profiles
 */

/**
 * Personal Interests xprofile field type.
 */
class BP_XProfile_Field_Type_Personal_Interests extends BP_XProfile_Field_Type {

	/**
	 * Name for field type.
	 *
	 * @var string The name of this field type.
	 */
	public $name = 'Personal Interests';

	/**
	 * The name of the category that this field type should be grouped with. Used on the [Users > Profile Fields] screen in wp-admin.
	 *
	 * @var string
	 */
	public $category = 'HC';

	/**
	 * If allowed to store null/empty values.
	 *
	 * @var bool If this is set, allow BP to store null/empty values for this field type.
	 */
	public $accepts_null_value = true;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Change UP member's interests field display name.
		$displayed_user = bp_get_displayed_user();
		if ( $displayed_user ) {
			$memberships = bp_get_member_type( $displayed_user->id, false );
			if ( is_array( $memberships ) && in_array( 'up', $memberships ) ) {
				HC_Member_Profiles_Component::$display_names[ HC_Member_Profiles_Component::INTERESTS ] = 'Personal Interests';
			}
		}
	}

	/**
	 * Allow field types to modify the appearance of their values.
	 *
	 * By default, this is a pass-through method that does nothing. Only
	 * override in your own field type if you need to provide custom
	 * filtering for output values.
	 *
	 * @uses DOMDocument
	 *
	 * @param mixed      $field_value Field value.
	 * @param string|int $field_id    ID of the field.
	 * @return mixed
	 */
	public static function display_filter( $field_value, $field_id = '' ) {
		$tax       = get_taxonomy( 'mla_academic_interests' );
		$interests = wpmn_get_object_terms(
			bp_displayed_user_id(), 'mla_academic_interests', array(
				'fields' => 'names',
			)
		);
		$interest_ids = get_user_meta( bp_displayed_user_id(), 'personal_interests' );
		$interests = array();
		foreach ( $interest_ids as $interest_id ) {
			$term = get_term( $interest_id );
			$interests[] = $term->name;
		}

		$html      = '<ul>';
		foreach ( $interests as $term_name ) {
			$search_url = esc_url(
				sprintf(
					'/?%s',
					http_build_query(
						[
							's'         => $term_name,
							'post_type' => [ 'user' ],
						]
					)
				)
			);
			$html      .= '<li><a href="' . esc_url( $search_url ) . '" rel="nofollow">';
			$html      .= $term_name;
			$html      .= '</a></li>';
		}
		$html .= '</ul>';
		return $html;
	}

	/**
	 * Output the edit field HTML for this field type.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @uses DOMDocument
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 * @return void
	 */
	public function edit_field_html( array $raw_properties = [] ) {
		global $mla_academic_interests;

		$user = wp_get_current_user();

		printf( '<label>%s</label>', $this->name );

		$interests_list = array();

		$interest_terms = wpmn_get_terms(
			'mla_academic_interests',
			array(
				'orderby' => 'name',
				'fields' => 'all',
				'hide_empty' => 0,
			)
		);
		foreach ( $interest_terms as $term ) {
			$interests_list[ $term->name ] = $term->name;
		}

		natcasesort( $interests_list );

		$html = '<select name="personal-interests[]" class="js-basic-multiple-tags interests" multiple="multiple" data-placeholder="Enter interests.">';

		$interests = get_user_meta( $user->ID, 'personal_interests' );
		$input_interest_list = array();
		foreach ( (array) $interests as $interest ) {
			$term = get_term( $interest );
			$input_interest_list[ $term->name ] = $term->name;
		}

				// Only add selected interests, load the rest over ajax.
		foreach ( $interests_list as $interest_key => $interest_value ) {
			if ( in_array( $interest_key, $input_interest_list ) ) {
				$html .= sprintf(
					'			<option class="level-1" selected="selected" value="%1$s">%2$s</option>' . "\n",
					$interest_key,
					$interest_value
				);
			}
		}

		$html .= '</select>';
		echo $html;
	}

	/**
	 * Output HTML for this field type on the wp-admin Profile Fields screen.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 * @return void
	 */
	public function admin_field_html( array $raw_properties = [] ) {
		echo "This field lists the user's personal interests.";
	}

}
