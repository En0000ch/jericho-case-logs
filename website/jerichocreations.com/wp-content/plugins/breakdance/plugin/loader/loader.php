<?php

use function Breakdance\Admin\get_env;
use function Breakdance\BreakdanceOxygen\Strings\__bdox;


$mode = (string) ($_GET['mode'] ?? 'builder');

require_once __DIR__ . "/loader-utils.php";

$ajaxurl = admin_url('admin-ajax.php');
$envtype = get_env();

if ($envtype !== 'local') {
    $manifest = getProductionManifest(__DIR__ . '/../../builder/dist', plugin_dir_url(__BREAKDANCE_PLUGIN_FILE__) . 'builder/dist');
}

$window_dot_breakdance_object_data = new stdClass();
$window_dot_breakdance_object_data->ajaxurl = $ajaxurl;
$window_dot_breakdance_object_data->ajaxnonce = \Breakdance\AJAX\get_nonce_for_ajax_requests();
$window_dot_breakdance_object_data->subscriptionMode = \Breakdance\Subscription\getSubscriptionMode();
$window_dot_breakdance_object_data->builderMode = BREAKDANCE_MODE;
$window_dot_breakdance_object_data->bdoxTranslations = \Breakdance\BreakdanceOxygen\Strings\getBdoxTranslationsForBuilder();
$window_dot_breakdance_object_data->restUrl = get_rest_url('', 'breakdance/v1');
$window_dot_breakdance_object_data->restNonce = wp_create_nonce('wp_rest');

if (isset($_GET['onboarding']) && $_GET['onboarding'] === 'true') {
    $window_dot_breakdance_object_data->isOnboarding = true;
}

if (isset($_GET['singularity']) && $_GET['singularity'] === 'step1') {
    $window_dot_breakdance_object_data->singularity = 'step1';
} else if (isset($_GET['singularity']) && $_GET['singularity'] === 'step2') {
    $window_dot_breakdance_object_data->singularity = 'step2';
}

if ($mode === 'builder') {
    if (!isset($_GET['id'])) {
        die('error');
    }
    $id = $_GET['id'];

    $window_dot_breakdance_object_data->initialDocumentToLoadId = (int) $id;
    $window_dot_breakdance_object_data->initialMode = 'builder';
} elseif ($mode === 'browse') {
    $window_dot_breakdance_object_data->initialMode = 'browse';

    $returnUrl = filter_input(INPUT_GET, 'returnUrl', FILTER_VALIDATE_URL);
    if ($returnUrl) {
        $window_dot_breakdance_object_data->returnUrl = $returnUrl;
    }

    $browseModeOpenUrl = filter_input(INPUT_GET, 'browseModeOpenUrl', FILTER_VALIDATE_URL);
    if ($browseModeOpenUrl) {
        $window_dot_breakdance_object_data->browseModeOpenUrl = $browseModeOpenUrl;
    }
}
?>
<!DOCTYPE HTML>
<html lang="en" class="is-<?php echo BREAKDANCE_MODE; ?>">

<head>
    <title><?php echo __bdox('plugin_name'); ?></title>

    <?php if (BREAKDANCE_MODE === 'oxygen') { ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <?php } ?>

    <script>
        window.Breakdance = <?= json_encode($window_dot_breakdance_object_data); ?>;
    </script>

    <?php
    if ($envtype === 'local') {
        echo getDevelopmentHeadLinks('app');
    } else {
        echo getProductionHeadLinks($manifest, 'app');
    }
    ?>
</head>

<body>
    <div id="app"></div>
    <?php

    if ($envtype === 'local') {
        echo getDevelopmentFooterScripts('app');
    } else {
        echo getProductionFooterScripts($manifest, 'app');
    }
    ?>
    <?php do_action('unofficial_i_am_kevin_geary_master_of_all_things_css_and_html'); ?>


</body>

</html>