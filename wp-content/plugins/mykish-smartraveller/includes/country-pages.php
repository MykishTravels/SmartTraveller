<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'mykish_smartraveller_country_rewrite');
function mykish_smartraveller_country_rewrite() {
    add_rewrite_tag('%mykish_country%', '([^&]+)');
    add_rewrite_rule('^smartraveller/([a-z0-9-]+)/?$', 'index.php?mykish_country=$matches[1]', 'top');
}

add_filter('query_vars', 'mykish_smartraveller_country_query_vars');
function mykish_smartraveller_country_query_vars($vars) {
    $vars[] = 'mykish_country';
    return $vars;
}

add_action('template_redirect', 'mykish_smartraveller_render_country_page');
function mykish_smartraveller_render_country_page() {
    $slug = get_query_var('mykish_country');
    if (!$slug || str_starts_with($slug, 'is-it-safe-to-travel-to-')) {
        return;
    }

    $dataset = mykish_smartraveller_get_dataset();
    $country = null;
    foreach ($dataset as $item) {
        if ($item['slug'] === $slug) {
            $country = $item;
            break;
        }
    }

    if (!$country) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        return;
    }

    status_header(200);
    nocache_headers();
    get_header();
    ?>
    <main class="mykish-wrapper">
        <article class="mykish-country-page">
            <h1><?php echo esc_html($country['country']); ?> Travel Advice</h1>
            <span class="mykish-badge level-<?php echo esc_attr($country['advisory_level']); ?>"><?php echo esc_html($country['advisory_text']); ?></span>
            <p><?php echo esc_html($country['summary']); ?></p>

            <?php if (!empty($country['regions'])) : ?>
                <section>
                    <h3>Regional Warnings</h3>
                    <ul>
                        <?php foreach ($country['regions'] as $region) : ?>
                            <li><?php echo esc_html(wp_strip_all_tags((string) $region)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <p><a href="<?php echo esc_url($country['source_url']); ?>" target="_blank" rel="noopener">Official Smartraveller advice</a></p>

            <section class="mykish-cta">
                <h3><?php echo esc_html(sprintf('Planning a trip to %s?', $country['country'])); ?></h3>
                <p>Our travel specialists at Mykish Travels create bespoke journeys tailored to you.</p>
                <a class="mykish-btn" href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a>
            </section>

            <?php mykish_smartraveller_render_disclaimer(); ?>
        </article>
    </main>
    <?php
    get_footer();
    exit;
}
