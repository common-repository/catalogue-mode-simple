<?php
/**
 * Plugin Name: Woocommerce Catalog Mode Simple
 * Description: Turn WooCommerce into a catalogue mode by disabling the add to cart button and checkout process.
 * Version: 3.0
 * Author: Sribharath
 * Author URI: https://webdzyners.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: Catalogue Mode Simple
 
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add admin options
 */
function wdcms_add_catalogue_mode_options() {
	add_options_page( 'Catalogue Mode Options', 'Catalogue Mode', 'manage_options', 'catalogue_mode_options', 'wdcms_catalogue_mode_options_page' );
}
add_action( 'admin_menu', 'wdcms_add_catalogue_mode_options' );

/**
 * Create options page
 */
function wdcms_catalogue_mode_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$catalogue_mode_enabled = get_option( 'catalogue_mode_enabled' );

	if ( isset( $_POST['catalogue_mode_enabled'] ) ) {
		$catalogue_mode_enabled = intval( $_POST['catalogue_mode_enabled'] );
		update_option( 'catalogue_mode_enabled', $catalogue_mode_enabled );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post">
			<label>
				<input type="checkbox" name="catalogue_mode_enabled" value="1" <?php checked( $catalogue_mode_enabled, 1 ); ?>>
				<?php esc_html_e( 'Enable catalogue mode', 'woocommerce-catalogue-mode' ); ?>
			</label>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Disable add to cart button
 */
function wdcms_disable_add_to_cart_button() {
	if ( is_product() || is_product_category() || is_shop() ) {
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}
}
add_action( 'wp', 'wdcms_disable_add_to_cart_button' );

/**
 * Remove cart and checkout links from header and mini-cart
 */
function wdcms_remove_cart_checkout_links( $links ) {
	if ( intval( get_option( 'catalogue_mode_enabled' ) ) === 1 ) {
		unset( $links['cart'] );
		unset( $links['checkout'] );
	}
	return $links;
}
add_filter( 'woocommerce_header_cart_link', 'wdcms_remove_cart_checkout_links' );
add_filter( 'woocommerce_add_to_cart_fragments', 'wdcms_remove_cart_checkout_links' );

/**
 * Disable checkout process
 */
function wdcms_disable_checkout_process() {
	if ( intval( get_option( 'catalogue_mode_enabled' ) ) === 1 && ! is_admin() && ! is_user_logged_in() && ( is_cart() || is_checkout() ) ) {
		wp_redirect( esc_url_raw( wc_get_page_permalink( 'shop' ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'wdcms_disable_checkout_process' );