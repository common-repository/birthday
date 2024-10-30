<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}

/**
 * WC_Email_Customer_Birthday class.
 *
 * Handles the sending of birthday emails to customers.
 */
class WC_Email_Customer_Birthday extends WC_Email {

	/**
	 * Constructor for the email class.
	 */
	public function __construct() {
		$this->id = 'birthday';
		// Unique ID for the email.
		$this->title = __( 'Birthday', 'birthday' );
		// Email title in the admin panel.
		$this->description = __( 'This email is sent to customers on their birthday.', 'birthday' );
		// Email description in the admin panel.

		// Email heading and subject line.
		$this->heading = __( 'Happy Birthday!', 'birthday' );
		$this->subject = __( 'Wishing you a Happy Birthday, {customer_name}!', 'birthday' );

		$this->recipient = '';
		// Recipient email address.
		$this->template_base = BIRTHDAY_PLUGIN_PATH . '/templates/';
		// Template base path.
		$this->template_html = 'birthday-email-template-html.php';
		// HTML template for the email.
		$this->template_plain = 'birthday-email-template-plain.php';
		// Plain text template for the email.

		// Call parent constructor to load any other defaults not explicitly defined here.
		parent::__construct();

		// Set recipient email address (in this case, the customer's email).
		$this->recipient = $this->get_recipient() ?: '';

		// Hook to trigger the email on the customer's birthday.
		add_action( 'woocommerce_customer_birthday', array( $this, 'trigger' ), 10, 1 );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $customer_id The customer ID.
	 */
	public function trigger( $customer_id ) {

		if ( ! $this->is_enabled() ) {
			return;
			// Exit if the email is not enabled.
		}

		$customer = get_user_by( 'ID', $customer_id );
		// Get the customer object by ID.
		$this->recipient = $customer->user_email;
		// Set the recipient email address.

		// Set placeholders to replace in the email content.
		$this->placeholders = array(
			'{customer_name}' => $customer->first_name,
		);

		// Send the email.
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get the HTML content for the email.
	 *
	 * @return string The HTML content.
	 */
	public function get_content_html() {

		ob_start();
		// Start output buffering.
		wc_get_template(
			$this->template_html,
			array(
				'email_heading' => $this->get_heading(),
				'customer_name' => $this->placeholders['{customer_name}'],
				'email'         => $this,
			// Pass the email object to the template.
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
		// Return the buffered content.
	}

	/**
	 * Get the plain text content for the email.
	 *
	 * @return string The plain text content.
	 */
	public function get_content_plain() {
		ob_start();
		// Start output buffering.
		wc_get_template(
			$this->template_plain,
			array(
				'email_heading' => $this->get_heading(),
				'customer_name' => $this->placeholders['{customer_name}'],
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
		// Return the buffered content.
	}
}
