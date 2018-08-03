<?php
/**
 * Plugin Name: NEU Member Profiles
 * Plugin URI:  https://github.com/mlaa/hc-member-profiles
 * Description: Enhanced BuddyPress XProfile functionality for Humanities Commons. Forked from MLA Commons Profile
 * Author:      MLA, NEU, Tanner Moushey (iWitness Design)
 * Author URI:  https://github.com/NEU-Libraries
 * Text Domain: hc-member-profiles
 * Domain Path: /languages
 *
 * @package Hc_Member_Profiles
 */

/**
 * Bootstrap the component.
 */
function hcmp_init() {
	require_once trailingslashit( dirname( __FILE__ ) ) . 'includes/class-hc-member-profiles-component.php';
	buddypress()->hc_member_profiles = new HC_Member_Profiles_Component();
}
add_action( 'bp_init', 'hcmp_init' );
