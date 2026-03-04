<?php
if (!defined('ABSPATH')) {
    exit;
}

function mykish_smartraveller_count_by_level($dataset) {
    $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    foreach ((array) $dataset as $item) {
        $level = (int) $item['advisory_level'];
        if (isset($counts[$level])) {
            $counts[$level]++;
        }
    }
    return $counts;
}

function mykish_smartraveller_render_summary_cards($dataset) {
    $counts = mykish_smartraveller_count_by_level($dataset);
    ?>
    <section class="mykish-summary-grid" aria-label="Global advisory snapshot">
        <button class="mykish-card level-4" data-filter-level="4">
            <span class="label">Level 4 – Do Not Travel</span>
            <strong><?php echo esc_html($counts[4]); ?></strong>
        </button>
        <button class="mykish-card level-3" data-filter-level="3">
            <span class="label">Level 3 – Reconsider Travel</span>
            <strong><?php echo esc_html($counts[3]); ?></strong>
        </button>
        <button class="mykish-card level-2" data-filter-level="2">
            <span class="label">Level 2 – Exercise High Degree of Caution</span>
            <strong><?php echo esc_html($counts[2]); ?></strong>
        </button>
        <button class="mykish-card level-1" data-filter-level="1">
            <span class="label">Level 1 – Exercise Normal Safety Precautions</span>
            <strong><?php echo esc_html($counts[1]); ?></strong>
        </button>
    </section>
    <?php
}
