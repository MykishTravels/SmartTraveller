<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_advisory_level_map() {
    return [
        1 => 'Exercise Normal Safety Precautions',
        2 => 'Exercise High Degree of Caution',
        3 => 'Reconsider Your Need to Travel',
        4 => 'Do Not Travel',
    ];
}

function mykish_smartraveller_normalize_level($raw_level, $raw_text = '') {
    if (is_numeric($raw_level)) {
        $numeric = (int) $raw_level;
        if ($numeric >= 1 && $numeric <= 4) {
            return $numeric;
        }
    }

    $text = strtolower(trim((string) $raw_text));
    if (str_contains($text, 'do not travel')) {
        return 4;
    }
    if (str_contains($text, 'reconsider your need to travel')) {
        return 3;
    }
    if (str_contains($text, 'high degree of caution')) {
        return 2;
    }

    return 1;
}

function mykish_smartraveller_find_value($item, $keys, $default = '') {
    foreach ($keys as $key) {
        if (isset($item[$key]) && $item[$key] !== '') {
            return $item[$key];
        }
    }
    return $default;
}

function mykish_smartraveller_normalize_dataset($raw_data) {
    if (!is_array($raw_data)) {
        return [];
    }

    $items = [];
    if (isset($raw_data['destinations']) && is_array($raw_data['destinations'])) {
        $items = $raw_data['destinations'];
    } elseif (array_values($raw_data) === $raw_data) {
        $items = $raw_data;
    } else {
        foreach ($raw_data as $value) {
            if (is_array($value) && isset($value[0])) {
                $items = $value;
                break;
            }
        }
    }

    $normalized = [];
    $map = mykish_smartraveller_advisory_level_map();

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $country = trim((string) mykish_smartraveller_find_value($item, ['title', 'country', 'name', 'destination']));
        if ($country === '') {
            continue;
        }

        $level_raw = mykish_smartraveller_find_value($item, ['advice_level', 'advisory_level', 'level'], 1);
        $advisory_text_raw = trim((string) mykish_smartraveller_find_value($item, ['advice_level_text', 'advisory_text', 'advice'], ''));
        $level = mykish_smartraveller_normalize_level($level_raw, $advisory_text_raw);

        $summary = trim((string) mykish_smartraveller_find_value($item, ['summary', 'overall_advice', 'description', 'advice_summary'], ''));
        $regions = mykish_smartraveller_find_value($item, ['regional_advice', 'regions', 'regionalWarnings'], []);
        if (!is_array($regions)) {
            $regions = $regions ? [(string) $regions] : [];
        }

        $source_url = trim((string) mykish_smartraveller_find_value($item, ['url', 'link', 'source_url'], 'https://www.smartraveller.gov.au/'));
        $last_updated = trim((string) mykish_smartraveller_find_value($item, ['last_updated', 'updated', 'lastUpdatedDate'], current_time('mysql')));

        $normalized[] = [
            'country' => $country,
            'slug' => sanitize_title($country),
            'advisory_level' => $level,
            'advisory_text' => $map[$level],
            'summary' => $summary,
            'regions' => array_values($regions),
            'last_updated' => $last_updated,
            'source_url' => esc_url_raw($source_url ?: 'https://www.smartraveller.gov.au/'),
        ];
    }

    usort($normalized, static function ($a, $b) {
        return strcmp($a['country'], $b['country']);
    });

    return $normalized;
}
