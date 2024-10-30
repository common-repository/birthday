<?php
/**
 * Plugin Name: Birthday
 * Description: Enhance your customer experience with WooCommerce Birthday and start offering personalized birthday promotions!
 * Version: 1.3.0
 * Requires Plugins: woocommerce
 * Author: Mini Plugins
 * Author URI: https://miniplugins.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || die();

if ( ! function_exists( 'bir_fs' ) ) {
    // Create a helper function for easy SDK access.
    function bir_fs() {
        global $bir_fs;

        if ( ! isset( $bir_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $bir_fs = fs_dynamic_init( array(
                'id'                  => '16315',
                'slug'                => 'birthday',
                'type'                => 'plugin',
                'public_key'          => 'pk_5f037db08dff9b416c82dd0163ad0',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'plugins.php',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $bir_fs;
    }

    // Init Freemius.
    bir_fs();
    // Signal that SDK was initiated.
    do_action( 'bir_fs_loaded' );
}

/**
 * Plugin constants.
 *
 * @since 1.0.0
 */
define( 'BIRTHDAY_PLUGIN_FILE', __FILE__ );
define( 'BIRTHDAY_PLUGIN_PATH', __DIR__ );
define( 'BIRTHDAY_VERSION', '1.3.0' );

require_once __DIR__ . '/src/Plugin.php';

/**
 * Return the main instance of Plugin Class.
 *
 * @since  1.0.0
 *
 * @return Plugin.
 */
function birthday() {
	$instance = \Birthday\Plugin::get_instance();

	$instance->init();

	return $instance;
}

birthday();