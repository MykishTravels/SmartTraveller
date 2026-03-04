<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_refresh_data($force = false) {
    if (!$force) {
        $cached = get_transient(MYKISH_SMARTRAVELLER_TRANSIENT_KEY);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }
    }

    $previous = get_transient(MYKISH_SMARTRAVELLER_TRANSIENT_KEY);
    $raw = mykish_smartraveller_fetch_remote_dataset();

    if (is_wp_error($raw)) {
        $raw = mykish_smartraveller_load_backup_dataset();
        if (is_wp_error($raw)) {
            return [];
        }
    } else {
        mykish_smartraveller_write_backup_dataset($raw);
    }

    $normalized = mykish_smartraveller_normalize_dataset($raw);
    if (empty($normalized)) {
        return is_array($previous) ? $previous : [];
    }

    set_transient(MYKISH_SMARTRAVELLER_TRANSIENT_KEY, $normalized, DAY_IN_SECONDS);
    mykish_smartraveller_detect_alert_changes(is_array($previous) ? $previous : [], $normalized);
    update_option('mykish_smartraveller_last_refresh', current_time('mysql'), false);

    return $normalized;
}

function mykish_smartraveller_get_dataset() {
    $cached = get_transient(MYKISH_SMARTRAVELLER_TRANSIENT_KEY);
    if (is_array($cached) && !empty($cached)) {
        return $cached;
    }

    return mykish_smartraveller_refresh_data();
}
