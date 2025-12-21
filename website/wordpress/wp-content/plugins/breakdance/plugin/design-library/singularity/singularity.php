<?php

// @psalm-ignore-file

namespace Breakdance\DesignLibrary;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_get_design_set_singularity_data_from_remote',
        'Breakdance\DesignLibrary\getDesignSetSingularityDataFromRemote',
        'none',
        true,
        [
            'args' => [
                'url' => FILTER_VALIDATE_URL
            ],
        ]
    );
});

/**
 * @param string $url
 */
function getDesignSetSingularityDataFromRemote($url)
{

    $cache_key = 'breakdance_design_set_singularity_data_2_' . $url;

    $cache = get_transient($cache_key);

    if ($cache !== false) {
        return $cache;
    }

    $request = \Breakdance\remotePostToWpAjax($url, 'breakdance_get_design_set_singularity_data');

    if (is_wp_error($request)) {
        /** @var \WP_Error $request */
        return ['error' => $request->get_error_message()];
    }

    if (is_array($request) && (!isset($request['response']['code']) || $request['response']['code'] !== 200)) {
        return ['error' => 'Unable to retrieve website'];
    }

    $body = wp_remote_retrieve_body($request);

    /** @var mixed */
    $data = json_decode($body);

    if (!is_object($data)) {
        return ['error' => 'Unable to decode data from website'];
    }

    set_transient($cache_key, (array) $data, 25 * HOUR_IN_SECONDS);

    return ['data' => $data];
}
