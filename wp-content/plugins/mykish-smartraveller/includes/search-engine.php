<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_prepare_search_index($dataset) {
    $index = [];
    foreach ((array) $dataset as $item) {
        $regions_text = '';
        if (!empty($item['regions']) && is_array($item['regions'])) {
            $regions_text = implode(' ', array_map('wp_strip_all_tags', $item['regions']));
        }

        $index[] = [
            'country' => $item['country'],
            'slug' => $item['slug'],
            'advisory_level' => $item['advisory_level'],
            'advisory_text' => $item['advisory_text'],
            'summary' => wp_trim_words(wp_strip_all_tags($item['summary']), 40),
            'regions_text' => $regions_text,
            'queries' => 'is ' . $item['country'] . ' safe travel ' . $item['country'] . ' safe',
            'url' => home_url('/smartraveller/' . $item['slug']),
        ];
    }
    return $index;
}
