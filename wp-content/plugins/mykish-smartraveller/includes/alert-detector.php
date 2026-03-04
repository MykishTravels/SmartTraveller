<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_detect_alert_changes($old_data, $new_data) {
    $old_index = [];
    foreach ((array) $old_data as $item) {
        $old_index[$item['slug']] = (int) $item['advisory_level'];
    }

    $alerts = (array) get_option('mykish_smartraveller_alert_history', []);
    foreach ((array) $new_data as $item) {
        $slug = $item['slug'];
        $new_level = (int) $item['advisory_level'];
        if (!isset($old_index[$slug])) {
            continue;
        }

        $old_level = (int) $old_index[$slug];
        if ($old_level !== $new_level) {
            array_unshift($alerts, [
                'country' => $item['country'],
                'slug' => $slug,
                'previous_level' => $old_level,
                'new_level' => $new_level,
                'date' => current_time('mysql'),
            ]);
        }
    }

    $alerts = array_slice($alerts, 0, 50);
    update_option('mykish_smartraveller_alert_history', $alerts, false);
}
