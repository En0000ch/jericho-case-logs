<?php

// @psalm-ignore-file

namespace Breakdance\Singularity\Endpoints;


add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_website_intake_service',
        '\Breakdance\Singularity\Endpoints\websiteIntakeService',
        'edit',
        true,
        [
            'args' => [
                'url' => FILTER_UNSAFE_RAW
            ],
        ]
    );
});

/**
 * @param string $url
 * @return array{data: mixed[]}
 */
function websiteIntakeService($url)
{

    $endpoint = 'https://lastditch-existing-website-intake-service-production.up.railway.app/analyze';

    $data = json_encode(array('url' => $url));

    $args = array(
        'body' => $data,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'method' => 'POST',
        'timeout' => 120, // Increase timeout for analysis
    );

    $response = wp_remote_post($endpoint, $args);

    if (is_wp_error($response)) {
        return array('error' => $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $analysis = json_decode($body, true);

    return ['data' => $analysis];
}
