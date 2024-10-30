<?php echo $email->get_heading(); // Output the email heading. ?>

<?php _e( 'Dear', 'birthday' ); ?> <?php echo $customer_name; // Output the customer's first name. ?>,

<?php _e( 'We hope you have a wonderful birthday and a fantastic year ahead!', 'birthday' ); // Birthday wishes. ?>

<?php _e( 'To celebrate your special day, we have a special offer just for you. Use coupon code "HAPPY_BIRTHDAY"', 'birthday' ); ?>

<?php echo wc_get_page_permalink( 'shop' ); // Output the link to the shop page. ?>

<?php _e( 'Best regards,', 'birthday' ); ?>
<?php echo get_bloginfo( 'name' ); // Output the site name. ?>
