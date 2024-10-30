<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( __( 'Dear %s,', 'birthday' ), esc_html( $customer_name ) ); ?></p>
<p><?php _e( 'We hope you have a wonderful birthday and a fantastic year ahead!', 'birthday' ); ?></p>
<p><?php _e( 'To celebrate your special day, we have a special offer just for you. Use coupon code "HAPPY_BIRTHDAY"', 'birthday' ); ?></p>
<p><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php _e( 'Shop Now', 'birthday' ); ?></a></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
