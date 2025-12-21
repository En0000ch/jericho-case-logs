<?php
/**
 * @var array $propertiesData
 */

if (!function_exists('yoast_breadcrumb') && !function_exists('rank_math_the_breadcrumbs') && !function_exists('seopress_display_breadcrumbs')) {
    echo "<p>Please install Yoast, RankMath, or SEOPress to use breadcrumbs.</p>";
} else {
    $integration = $propertiesData["content"]["breadcrumbs"]["integration"] ?? null;
    if ($integration) {
        if ($integration == 'yoast' && function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb();
        }

        if ($integration == 'rankmath' && function_exists('rank_math_the_breadcrumbs')) {
            rank_math_the_breadcrumbs();
        }

        if ($integration == 'seopress' && function_exists('seopress_display_breadcrumbs')) {
            seopress_display_breadcrumbs();
        }
    } else {
        echo '<p>Please select a breadcrumbs integration.</p>';
    }
}
