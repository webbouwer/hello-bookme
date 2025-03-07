<?php 
/*
Plugin Name: Hello Book Me
Plugin URI: https://webbouwer.org
Description: Calendar Booking for Wordpress plugin based on ics source 
*/

if ( !defined( 'ABSPATH' ) ) exit;

require_once(plugin_dir_path(__FILE__) . 'config/config.php');
require_once(plugin_dir_path(__FILE__) . 'assets/calendar.php');
require_once(plugin_dir_path(__FILE__) . 'assets/language.php');
require_once(plugin_dir_path(__FILE__) . 'assets/shortcodes.php');
require_once(plugin_dir_path(__FILE__) . 'assets/send_email.php');

function plugconstruct() {
    if(!is_admin()){
        return new HelloBookMeClass(); 
    }
}
add_action( 'init', 'plugconstruct' );

function hello_bookme_enqueue_scripts() {
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.12.4.min.js', array(), '1.12.4', true);
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js', array('jquery'), '1.12.1', true);
    wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js', array('jquery'), '5.10.1', true);
    wp_enqueue_style('fullcalendarcss', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css', array(), '5.10.1'); 
    wp_enqueue_style('fullcalendarcustom', plugin_dir_url(__FILE__) . 'assets/booking.css'); 
    wp_enqueue_script('hello-bookme', plugin_dir_url(__FILE__) . 'assets/hello-bookme.js', array('jquery', 'fullcalendar'), null, true);
    wp_localize_script('hello-bookme', 'helloBookMeAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}

class HelloBookMeClass {
    function __construct() {
        add_action('wp_enqueue_scripts', 'hello_bookme_enqueue_scripts');
        add_shortcode('hello_bookme', 'hello_bookme_shortcode');
        add_action('wp_ajax_nopriv_send_booking_email', 'send_booking_email');
        add_action('wp_ajax_send_booking_email', 'send_booking_email');
    }
}

?>