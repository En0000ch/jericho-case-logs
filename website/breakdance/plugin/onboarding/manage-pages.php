<?php

namespace Breakdance\Onboarding;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_onboarding_trash_pages',
        'Breakdance\Onboarding\trashOnboardingPagesByPageId',
        'edit',
        true,
        [
            'args' => [
                'pageIdsToTrash' => [
                    'filter' => FILTER_UNSAFE_RAW,
                    'flags' => FILTER_REQUIRE_ARRAY,
                ],

            ],
        ]
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_get_design_set_barebones_data_from_remote',
        'Breakdance\Onboarding\getDesignSetBarebonesDataFromRemote',
        'none',
        true,
        [
            'args' => [
                'url' => FILTER_VALIDATE_URL
            ],
        ]
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_onboarding_request_structured_output',
        'Breakdance\Onboarding\requestStructuredOutput',
        'none',
        false,
        [
            'args' => [
                'messages' => FILTER_UNSAFE_RAW,
                'json_schema' => FILTER_UNSAFE_RAW
            ]
        ]
    );
});

/**
 * @param int[] $pageIdsToTrash
 * @return array
 */
function trashOnboardingPagesByPageId($pageIdsToTrash)
{
    $failedToDeleteSomething = false;

    foreach ($pageIdsToTrash as $pageIdToTrash) {
        $trashed = wp_trash_post($pageIdToTrash);

        if (!$trashed) {
            $failedToDeleteSomething = true;
        }
    }

    if ($failedToDeleteSomething) {
        return ['error' => "Failed to delete all pages."];
    }

    return ['success' => "Deleted all pages successfully."];
}

/**
 *
 * @param string $url The URL to fetch the design set data from.
 * @return array An array containing either 'data' with the design set data or 'error' with an error message.
 * @psalm-suppress MixedInferredReturnType
 */
function getDesignSetBarebonesDataFromRemote($url)
{
    /**
     * @psalm-suppress MixedOperand
     */
    $cache_key = 'breakdance_design_set_barebones_data_2_' . $url;

    /**
     * @psalm-suppress MixedAssignment
     */
    $cache = get_transient($cache_key);

    /**
     * @psalm-suppress MixedReturnStatement
     */
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


    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress UndefinedConstant
     */
    set_transient($cache_key, (array) $data, 25 * HOUR_IN_SECONDS);

    return ['data' => $data];
}


/**
 * Requests structured output from the Breakdance AI service.
 *
 * @param array $messages The messages to send to the AI service.
 * @param string|null $json_schema Optional JSON schema for structured output.
 * @return array The response data or an error message.
 * @psalm-suppress MixedInferredReturnType
 */
function requestStructuredOutput($messages, $json_schema = null)
{

    $url = 'https://breakdance.com/wp-json/breakdance/v1/onboarding/ai/request-structured-output';

    $response = wp_remote_post($url, [
        'body' => json_encode([
            'messages' => $messages,
            'json_schema' => $json_schema
        ]),
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) {
        /** @var \WP_Error $response */
        return ['error' => $response->get_error_message()];
    }

    if (wp_remote_retrieve_response_code($response) !== 200) {
        return ['error' => 'Unable to retrieve website'];
    }

    $body = wp_remote_retrieve_body($response);

    /**
     * @psalm-suppress MixedAssignment
     */
    $data = json_decode($body, true);

    /**
     * @psalm-suppress MixedReturnStatement
     */
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Unable to decode data from website'];
    }

    /**
     * @psalm-suppress MixedReturnStatement
     */
    return $data;
}
