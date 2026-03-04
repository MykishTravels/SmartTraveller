<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_render_trending_alerts() {
    $alerts = array_slice((array) get_option('mykish_smartraveller_alert_history', []), 0, 5);
    $labels = mykish_smartraveller_advisory_level_map();

    echo '<section class="mykish-trending-alerts"><h3>Trending Travel Alerts</h3>';
    if (empty($alerts)) {
        echo '<p>No recent advisory changes detected.</p></section>';
        return;
    }

    echo '<ul>';
    foreach ($alerts as $alert) {
        $country_url = home_url('/smartraveller/' . $alert['slug']);
        printf(
            '<li><a href="%1$s">%2$s</a>: %3$s → %4$s <span>%5$s</span></li>',
            esc_url($country_url),
            esc_html($alert['country']),
            esc_html($labels[(int) $alert['previous_level']] ?? 'Unknown'),
            esc_html($labels[(int) $alert['new_level']] ?? 'Unknown'),
            esc_html(mysql2date('M j, Y', $alert['date']))
        );
    }
    echo '</ul></section>';
}
