<?php

namespace Breakdance\Variables;

use function Breakdance\Data\get_global_option;
use function Breakdance\Data\GlobalRevisions\add_new_revision;
use function Breakdance\Data\GlobalRevisions\load_revisions_list;
use function Breakdance\Data\set_global_option;

use const Breakdance\Data\GlobalRevisions\BREAKDANCE_REVISION_TYPE_VARIABLES;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_save_variables',
        '\Breakdance\Variables\saveVariables',
        'edit',
        false,
        ['args' => ['variables' => FILTER_UNSAFE_RAW]]
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_load_variables',
        '\Breakdance\Variables\getVariablesDataForBuilder',
        'edit'
    );
});

/**
 * @param string $data
 * @return void
 */
function saveVariables($data)
{
    // Naive way to replace empty objects with nulls, to avoid PHP from converting them to arrays
    $data = str_replace("{}", "null", $data);

    /** @var array{variables: OxygenVariable[], collections: string[]} $variablesData */
    $variablesData = json_decode($data, true);
    $variables = $variablesData['variables'];

    /** @var false|string $currentVariables */
    $currentVariables = get_global_option('variables_json_string');

    set_global_option('variables_collections_json_string', $variablesData['collections']);

    if ($variables !== $currentVariables) {
        $currentRevisions = load_revisions_list(BREAKDANCE_REVISION_TYPE_VARIABLES);
        if (!sizeof($currentRevisions) && $currentVariables) {
            add_new_revision($currentVariables, BREAKDANCE_REVISION_TYPE_VARIABLES);
        }

        set_global_option('variables_json_string', $variables);

        add_new_revision($variables, BREAKDANCE_REVISION_TYPE_VARIABLES);

        \Breakdance\Render\generateCacheForGlobalSettings();
    }
}
