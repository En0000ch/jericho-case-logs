<?php

namespace Breakdance\Themeless;

use function Breakdance\MaintenanceMode\optionsGetter;
use function Breakdance\Render\render;
use function Breakdance\Themeless\ThemeDisabler\is_theme_disabled;
use function Breakdance\Themeless\ThemeDisabler\is_zero_theme_enabled;

$page = optionsGetter('page');
$mode = optionsGetter('mode');
if ($mode === 'maintenance') {
    if (isset($_SERVER["SERVER_PROTOCOL"])) {
        header($_SERVER["SERVER_PROTOCOL"] . ' 503 Service Temporarily Unavailable');
    }
}

$isThemelessOrZeroTheme = is_theme_disabled() || is_zero_theme_enabled();

remove_theme_support('title-tag');
?>
<!doctype html>
<html <?php language_attributes();?>>
<head>
    <meta charset="<?php bloginfo('charset');?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($isThemelessOrZeroTheme) : ?>
        <?php echo getNormalizeDotCssLinkTag(); ?>
    <?php endif ?>
    <?php wp_head(); ?>
    <title>
        <?php echo $mode === 'coming-soon' ? 'Coming Soon' : 'Maintenance'; ?>
    </title>
</head>
<?php

$breakdance_or_oxygen_body_class = (string) BREAKDANCE_MODE;

?>
<body <?php body_class($isThemelessOrZeroTheme ? [$breakdance_or_oxygen_body_class] : []);?>>
<?php wp_body_open();?>
    <?php if ($page !== 'none') : ?>
        <?php echo render($page); ?>
    <?php else : ?>
        <p>Down for maintenance.</p>
    <?php endif; ?>
<?php wp_footer();?>
</body>
</html>
