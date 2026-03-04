<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'mykish_smartraveller_enqueue_assets');
function mykish_smartraveller_enqueue_assets() {
    wp_register_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
    wp_register_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
    wp_register_script('fusejs', 'https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js', [], '7.0.0', true);

    wp_register_style('mykish-smartraveller', MYKISH_SMARTRAVELLER_URL . 'assets/css/smartraveller.css', [], MYKISH_SMARTRAVELLER_VERSION);
    wp_register_script('mykish-smartraveller-search', MYKISH_SMARTRAVELLER_URL . 'assets/js/search.js', ['fusejs'], MYKISH_SMARTRAVELLER_VERSION, true);
    wp_register_script('mykish-smartraveller-map', MYKISH_SMARTRAVELLER_URL . 'assets/js/map.js', ['leaflet'], MYKISH_SMARTRAVELLER_VERSION, true);
}

function mykish_smartraveller_render_disclaimer() {
    ?>
    <div class="mykish-disclaimer">
        <p>Travel advice sourced from the Australian Government Smartraveller website.</p>
        <p>This page is updated daily at 9 AM AEST.</p>
        <p>For official information visit: <a href="https://www.smartraveller.gov.au/" target="_blank" rel="noopener">https://www.smartraveller.gov.au/</a></p>
        <p>The information on this page is provided for general guidance only. Always refer to the official website for the most current travel advice.</p>
    </div>
    <?php
}

add_shortcode('smartraveller_search', 'mykish_smartraveller_shortcode');
function mykish_smartraveller_shortcode() {
    $dataset = mykish_smartraveller_get_dataset();
    $search_index = mykish_smartraveller_prepare_search_index($dataset);

    wp_enqueue_style('leaflet');
    wp_enqueue_style('mykish-smartraveller');
    wp_enqueue_script('leaflet');
    wp_enqueue_script('fusejs');
    wp_enqueue_script('mykish-smartraveller-search');
    wp_enqueue_script('mykish-smartraveller-map');

    wp_localize_script('mykish-smartraveller-search', 'MykishSmartravellerData', [
        'dataset' => $search_index,
        'levelLabels' => mykish_smartraveller_advisory_level_map(),
    ]);

    ob_start();
    ?>
    <section class="mykish-wrapper mykish-dashboard" id="mykish-smartraveller-root">
        <header class="mykish-header">
            <h1>Australian Government Travel Advice</h1>
            <p>Search any destination to see the latest Smartraveller travel advice.</p>
        </header>

        <div class="mykish-search-panel">
            <input type="search" id="mykish-search-input" placeholder="Search country or region (e.g. Is Japan safe?)" autocomplete="off" />
            <div id="mykish-search-results" class="mykish-search-results"></div>
        </div>

        <?php mykish_smartraveller_render_summary_cards($dataset); ?>
        <?php mykish_smartraveller_render_trending_alerts(); ?>

        <section class="mykish-map-section">
            <h3>Interactive Advisory Map</h3>
            <div id="mykish-advisory-map" data-countries="<?php echo esc_attr(wp_json_encode($search_index)); ?>"></div>
        </section>

        <section class="mykish-level-4-list">
            <h3>Do Not Travel</h3>
            <div class="mykish-cards">
                <?php foreach ($dataset as $item) : ?>
                    <?php if ((int) $item['advisory_level'] !== 4) { continue; } ?>
                    <article class="mykish-country-card" data-level="4">
                        <h4><?php echo esc_html($item['country']); ?></h4>
                        <span class="mykish-badge level-4"><?php echo esc_html($item['advisory_text']); ?></span>
                        <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($item['summary']), 25)); ?></p>
                        <a href="<?php echo esc_url(home_url('/smartraveller/' . $item['slug'])); ?>">View country page</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <?php mykish_smartraveller_render_disclaimer(); ?>
    </section>
    <?php

    return ob_get_clean();
}
