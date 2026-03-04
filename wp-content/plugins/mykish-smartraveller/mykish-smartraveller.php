<?php
/**
 * Plugin Name: Mykish Smartraveller
 * Description: Travel advisory intelligence dashboard powered by Australian Government Smartraveller data.
 * Version: 1.0.0
 * Author: Mykish Travels
 * Text Domain: mykish-smartraveller
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MYKISH_SMARTRAVELLER_VERSION', '1.0.0');
define('MYKISH_SMARTRAVELLER_FILE', __FILE__);
define('MYKISH_SMARTRAVELLER_PATH', plugin_dir_path(__FILE__));
define('MYKISH_SMARTRAVELLER_URL', plugin_dir_url(__FILE__));
define('MYKISH_SMARTRAVELLER_TRANSIENT_KEY', 'mykish_smartraveller_normalized');
define('MYKISH_SMARTRAVELLER_CRON_HOOK', 'mykish_smartraveller_daily_refresh');

require_once MYKISH_SMARTRAVELLER_PATH . 'includes/normalize-data.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/fetch-data.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/cache-manager.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/alert-detector.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/search-engine.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/risk-summary-cards.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/trending-alerts.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/country-pages.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/seo-safety-pages.php';
require_once MYKISH_SMARTRAVELLER_PATH . 'includes/shortcode.php';

register_activation_hook(__FILE__, 'mykish_smartraveller_activate');
register_deactivation_hook(__FILE__, 'mykish_smartraveller_deactivate');

function mykish_smartraveller_activate() {
    mykish_smartraveller_schedule_daily_event();
    mykish_smartraveller_refresh_data(true);
    flush_rewrite_rules();
}

function mykish_smartraveller_deactivate() {
    $timestamp = wp_next_scheduled(MYKISH_SMARTRAVELLER_CRON_HOOK);
    if ($timestamp) {
        wp_unschedule_event($timestamp, MYKISH_SMARTRAVELLER_CRON_HOOK);
    }
    flush_rewrite_rules();
}

add_action('init', 'mykish_smartraveller_schedule_daily_event');
function mykish_smartraveller_schedule_daily_event() {
    if (wp_next_scheduled(MYKISH_SMARTRAVELLER_CRON_HOOK)) {
        return;
    }

    try {
        $now = new DateTime('now', wp_timezone());
        $scheduled = new DateTime('today 09:00:00', new DateTimeZone('Australia/Sydney'));
        $scheduled->setTimezone(wp_timezone());
        if ($scheduled <= $now) {
            $scheduled->modify('+1 day');
        }

        wp_schedule_event($scheduled->getTimestamp(), 'daily', MYKISH_SMARTRAVELLER_CRON_HOOK);
    } catch (Exception $e) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', MYKISH_SMARTRAVELLER_CRON_HOOK);
    }
}

add_action(MYKISH_SMARTRAVELLER_CRON_HOOK, 'mykish_smartraveller_refresh_data');

add_action('admin_post_mykish_smartraveller_refresh', 'mykish_smartraveller_manual_refresh');
function mykish_smartraveller_manual_refresh() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Permission denied.', 'mykish-smartraveller'));
    }
    check_admin_referer('mykish_smartraveller_refresh');
    mykish_smartraveller_refresh_data(true);
    wp_safe_redirect(admin_url('options-general.php?page=mykish-smartraveller&refreshed=1'));
    exit;
}

add_action('admin_menu', 'mykish_smartraveller_admin_menu');
function mykish_smartraveller_admin_menu() {
    add_options_page(
        __('Mykish Smartraveller', 'mykish-smartraveller'),
        __('Mykish Smartraveller', 'mykish-smartraveller'),
        'manage_options',
        'mykish-smartraveller',
        'mykish_smartraveller_admin_page'
    );
}

function mykish_smartraveller_admin_page() {
    $last_updated = get_option('mykish_smartraveller_last_refresh', 'Never');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Mykish Smartraveller', 'mykish-smartraveller'); ?></h1>
        <p><?php echo esc_html(sprintf(__('Last refresh: %s', 'mykish-smartraveller'), $last_updated)); ?></p>
        <?php if (isset($_GET['refreshed'])) : ?>
            <div class="notice notice-success"><p><?php esc_html_e('Dataset refreshed successfully.', 'mykish-smartraveller'); ?></p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('mykish_smartraveller_refresh'); ?>
            <input type="hidden" name="action" value="mykish_smartraveller_refresh" />
            <button type="submit" class="button button-primary"><?php esc_html_e('Refresh now', 'mykish-smartraveller'); ?></button>
        </form>
    </div>
    <?php
}
