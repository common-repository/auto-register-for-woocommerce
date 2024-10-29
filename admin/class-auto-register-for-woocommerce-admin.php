<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Auto_Register_Wc
 * @subpackage Auto_Register_Wc/admin
 * @author     palmoduledev <palmoduledev@gmail.com>
 */
class Auto_Register_Wc_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function palmodule_auto_register_customer($order_id, $posted_data, $order) {
        try {
            if (!is_user_logged_in()) {
                if (!empty($posted_data) && $order->get_total() > 0) {
                    $email = $order->get_billing_email();
                    if (!empty($email)) {
                        if (email_exists($email)) {
                            $customer_id = email_exists($email);
                        } else {
                            $username = sanitize_user(current(explode('@', $email)), true);
                            $append = 1;
                            $o_username = $username;
                            while (username_exists($username)) {
                                $username = $o_username . $append;
                                $append++;
                            }
                            $password = wp_generate_password();
                            WC()->session->set('before_wc_create_new_customer', true);
                            $new_customer = wc_create_new_customer($email, $username, $password);
                            if (is_wp_error($new_customer)) {
                                throw new Exception($new_customer->get_error_message());
                            } else {
                                $customer_id = absint($new_customer);
                                do_action('woocommerce_guest_customer_new_account_notification', $customer_id);
                            }
                        }
                        wc_set_customer_auth_cookie($customer_id);
                        WC()->session->set('reload_checkout', true);
                        WC()->cart->calculate_totals();
                        $first_name = $order->get_billing_first_name();
                        if ($customer_id && is_multisite() && is_user_logged_in() && !is_user_member_of_blog()) {
                            add_user_to_blog(get_current_blog_id(), $customer_id, 'customer');
                        }
                        if ($first_name && apply_filters('woocommerce_checkout_update_customer_data', true, WC()->customer)) {
                            $userdata = array(
                                'ID' => $customer_id,
                                'first_name' => $order->get_billing_first_name(),
                                'last_name' => $order->get_billing_last_name(),
                                'display_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
                            );
                            update_post_meta($order_id, '_customer_user', $customer_id);
                            wp_update_user(apply_filters('woocommerce_checkout_customer_userdata', $userdata, WC()->customer));
                            $customer = new WC_Customer($customer_id);
                            if (!empty($posted_data['billing_first_name'])) {
                                $customer->set_first_name($posted_data['billing_first_name']);
                            }
                            if (!empty($posted_data['billing_last_name'])) {
                                $customer->set_last_name($posted_data['billing_last_name']);
                            }
                            if (is_email($customer->get_display_name())) {
                                $customer->set_display_name($posted_data['billing_first_name'] . ' ' . $posted_data['billing_last_name']);
                            }
                            foreach ($posted_data as $key => $value) {
                                if (is_callable(array($customer, "set_{$key}"))) {
                                    $customer->{"set_{$key}"}($value);
                                } elseif (0 === stripos($key, 'billing_') || 0 === stripos($key, 'shipping_')) {
                                    $customer->update_meta_data($key, $value);
                                }
                            }
                            do_action('woocommerce_checkout_update_customer', $customer, $posted_data);
                            $customer->save();
                            wc_clear_notices();
                            wp_destroy_current_session();
                            wp_clear_auth_cookie();
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

}
