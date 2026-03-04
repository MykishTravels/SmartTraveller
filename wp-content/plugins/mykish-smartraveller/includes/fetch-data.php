<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_dataset_url() {
    return 'https://www.smartraveller.gov.au/destinations-export';
}

function mykish_smartraveller_backup_file_path() {
    $upload_dir = wp_upload_dir();
    return trailingslashit($upload_dir['basedir']) . 'smartraveller-backup.json';
}

function mykish_smartraveller_fetch_remote_dataset() {
    $response = wp_remote_get(mykish_smartraveller_dataset_url(), [
        'timeout' => 20,
        'headers' => ['Accept' => 'application/json'],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status = wp_remote_retrieve_response_code($response);
    if ($status < 200 || $status >= 300) {
        return new WP_Error('smartraveller_bad_status', 'Unexpected response status from Smartraveller API.', ['status' => $status]);
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        return new WP_Error('smartraveller_empty_body', 'Smartraveller API returned empty response body.');
    }

    $decoded = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('smartraveller_json_invalid', 'Invalid JSON received from Smartraveller API.');
    }

    return $decoded;
}

function mykish_smartraveller_load_backup_dataset() {
    $path = mykish_smartraveller_backup_file_path();
    if (!file_exists($path)) {
        return new WP_Error('smartraveller_backup_missing', 'Backup dataset file not found.');
    }

    $contents = file_get_contents($path);
    if ($contents === false || $contents === '') {
        return new WP_Error('smartraveller_backup_read_failed', 'Unable to read backup dataset file.');
    }

    $decoded = json_decode($contents, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('smartraveller_backup_json_invalid', 'Backup dataset JSON is invalid.');
    }

    return $decoded;
}

function mykish_smartraveller_write_backup_dataset($data) {
    $json = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!$json) {
        return false;
    }

    $path = mykish_smartraveller_backup_file_path();
    wp_mkdir_p(dirname($path));
    return file_put_contents($path, $json) !== false;
}
