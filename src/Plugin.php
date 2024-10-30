<?php

namespace Birthday;

defined( 'ABSPATH' ) || exit;
// Exit if accessed directly.

/**
 * Plugin Class.
 *
 * @since 1.0.0
 */
final class Plugin {


	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_notices', array( $this, 'rate' ) );
		add_action( 'init', array( $this, 'schedule' ) );
		add_action( 'plugins_loaded ', array( $this, 'load_woocommerce_email_class' ), 1000 );
		add_action( 'birthday_for_woocommerce_email', array( $this, 'process_send' ) );
		add_action( 'birthday_for_woocommerce_email_single', array( $this, 'send' ) );
		add_filter( 'woocommerce_email_classes', array( $this, 'add_birthday_email_class' ) );
		add_action( 'woocommerce_billing_fields', array( $this, 'add_birthday_field_to_checkout' ) );
		add_filter( 'woocommerce_customer_meta_fields', array( $this, 'add_birthday_field_to_customer_billing_address' ) );
		add_action( 'woocommerce_edit_account_form', array( $this, 'add_birthday_field_to_account_page' ) );
		add_action( 'woocommerce_save_account_details', array( $this, 'save_birthday_field_in_account_page' ) );
		add_action( 'woocommerce_save_account_details_errors', array( $this, 'validate_birthday_field' ), 10, 1 );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/birthday/birthday-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/birthday-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'birthday' );

		load_textdomain( 'birthday', WP_LANG_DIR . '/birthday/birthday-' . $locale . '.mo' );
		load_plugin_textdomain( 'birthday', false, plugin_basename( dirname( BIRTHDAY_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Rate the Birthday For WooCommerce Plugin!
	 *
	 * @since 1.3.0
	 */
	public function rate() {

		if ( ! empty( $_GET['section'] ) && 'wc_birthday_email' === $_GET['section'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$message = sprintf( /* translators: %1s - The Birthday For WooCommerce Plugin, %2s - Mini Plugins  */ __( 'Please rate the %1$1s plugin to help us spread the word.  ~ Sanjeev Aryal, the %2$2s developer.', 'birthday' ), '<a href="https://wordpress.org/support/plugin/birthday/reviews/#new-post">Birthday For WooCommerce</a>', '<a href="https://miniplugins.com">Mini Plugins</a>' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-info', $message ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Schedule Sending Email.
	 */
	public function schedule() {
		if ( false === as_next_scheduled_action( 'birthday_for_woocommerce_email' ) ) {
			as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'birthday_for_woocommerce_email', array(), 'birthday' );
		}
	}

	/**
	 * Send the Email to the customer on their birthday.
	 */
	public function process_send() {

		$today = gmdate( 'm-d' );

		$customer_ids = get_users(
			array(
				'fields'     => 'ID',
				'number'     => 1,
				'meta_query' => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'billing_birthday',
						'value'   => $today,
						'compare' => 'LIKE',
					),
				),
			)
		);

		$i = 0;
		if ( ! empty( $customer_ids ) ) {
			foreach ( $customer_ids as $customer_id ) {

				++$i;
				as_schedule_single_action( time() + $i * MINUTE_IN_SECONDS, 'birthday_for_woocommerce_email_single', array( 'user_id' => $customer_id ), 'birthday_for_woocommerce' );
			}
		}
	}

	/**
	 * Send single email by async action.
	 *
	 * @param int $user_id User ID.
	 */
	public function send( $user_id ) {
		do_action( 'woocommerce_customer_birthday', $user_id );
	}

	/**
	 * Add Birthday Email Class.
	 *
	 * @param array $email_classes Other Email Classes.
	 */
	public function add_birthday_email_class( $email_classes ) {
		require_once BIRTHDAY_PLUGIN_PATH . '/src/class-wc-birthday-email.php';
		$email_classes['WC_Birthday_Email'] = new \WC_Email_Customer_Birthday();

		return $email_classes;
	}

	/**
	 * Add a birthday field in the billing section of checkout.
	 *
	 * @param array $fields Existing fields in checkout.
	 *
	 * @since 1.0.0
	 */
	public function add_birthday_field_to_checkout( $fields ) {

		$fields['billing_birthday'] = array(
			'type'         => 'date',
			'label'        => apply_filters( 'birthday_for_woocommerce_checkout_label', __( 'Birthday', 'birthday' ) ),
			'description'  => apply_filters( 'birthday_for_woocommerce_checkout_description', __( 'Enter your birthday in case you\'d like to receive cool gifts, discounts etc. from us in your birthday', 'birthday' ) ),
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'birthday',
			'required'     => false,
			'options'      => array(
				'' => __( 'Select your birthday', 'birthday' ),
			),
		);

		return $fields;
	}

	/**
	 * Add birthday field to customer billing address section in user profile page.
	 *
	 * @param array $fields Existing customer meta fields.
	 *
	 * @return array Modified customer meta fields.
	 */
	public function add_birthday_field_to_customer_billing_address( $fields ) {

		$fields['billing']['fields']['billing_birthday'] = array(
			'label'       => __( 'Birthday', 'birthday' ),
			'type'        => 'date',
			'placeholder' => 'YYYY-MM-DD',
			'description' => apply_filters( 'birthday_for_woocommerce_user_profile_page_field_description', __( 'Enter your birthday in case you\'d like to receive cool gifts, discounts etc. from us in your birthday. Format: YYYY-MM-DD', 'birthday' ) ),
		);

		return $fields;
	}

	/**
	 * Add birthday field to my account page.
	 */
	public function add_birthday_field_to_account_page() {

		$user_id       = get_current_user_id();
		$user_birthday = get_user_meta( $user_id, 'billing_birthday', true );

		?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="account_birthday"><?php esc_html_e( 'Birthday', 'birthday' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="account_birthday" id="account_birthday" value="<?php echo esc_attr( $user_birthday ); ?>" />
			</p>
		<?php
	}

	/**
	 * Save birthday field in my account page.
	 *
	 * @param int $user_id User ID.
	 */
	public function save_birthday_field_in_account_page( $user_id ) {
		if ( isset( $_POST['account_birthday'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_user_meta( $user_id, 'billing_birthday', sanitize_text_field( wp_unslash( $_POST['account_birthday'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}
	/**
	 * Validate the birthday field.
	 *
	 * @param object $args Arguments.
	 */
	public function validate_birthday_field( $args ) {
		if ( empty( $_POST['account_birthday'] ) ) {
			$args->add( 'error', __( 'Please enter your birthday.', 'birthday' ) );
		}
	}
}
