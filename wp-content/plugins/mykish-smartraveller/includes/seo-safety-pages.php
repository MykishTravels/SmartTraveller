<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', 'mykish_smartraveller_render_seo_safety_page', 9);
function mykish_smartraveller_render_seo_safety_page() {
    $slug = get_query_var('mykish_country');
    $prefix = 'is-it-safe-to-travel-to-';
    if (!$slug || !str_starts_with($slug, $prefix)) {
        return;
    }

    $country_slug = substr($slug, strlen($prefix));
    $dataset = mykish_smartraveller_get_dataset();
    $country = null;
    foreach ($dataset as $item) {
        if ($item['slug'] === $country_slug) {
            $country = $item;
            break;
        }
    }

    if (!$country) {
        return;
    }

    $safe_answer = ((int) $country['advisory_level'] <= 2)
        ? 'Yes, with normal precautions and close monitoring of official advice.'
        : 'Exercise caution. Review official guidance before making travel plans.';

    status_header(200);
    get_header();
    ?>
    <main class="mykish-wrapper">
        <article class="mykish-country-page">
            <h1><?php echo esc_html(sprintf('Is it safe to travel to %s?', $country['country'])); ?></h1>
            <p class="mykish-direct-answer"><?php echo esc_html($safe_answer); ?></p>
            <span class="mykish-badge level-<?php echo esc_attr($country['advisory_level']); ?>"><?php echo esc_html($country['advisory_text']); ?></span>
            <p><?php echo esc_html($country['summary']); ?></p>

            <?php if (!empty($country['regions'])) : ?>
                <h3>Regional warnings</h3>
                <ul>
                    <?php foreach ($country['regions'] as $region) : ?>
                        <li><?php echo esc_html(wp_strip_all_tags((string) $region)); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p><a href="<?php echo esc_url(home_url('/smartraveller/' . $country['slug'])); ?>">View full <?php echo esc_html($country['country']); ?> advisory page</a></p>
            <?php mykish_smartraveller_render_disclaimer(); ?>
        </article>
    </main>
    <?php
    get_footer();
    exit;
}
