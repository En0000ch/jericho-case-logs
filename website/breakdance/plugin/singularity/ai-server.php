<?php

namespace Breakdance\Singularity;

/**
 * @return array
 */
function getAiServerData()
{
    return [
        'url' => defined('BREAKDANCE_AI_SERVER_URL') ? rtrim((string) \BREAKDANCE_AI_SERVER_URL, '/') : 'https://lastditch-general-ai-service-production.up.railway.app',
        'key' => defined('BREAKDANCE_AI_SERVER_KEY') ? \BREAKDANCE_AI_SERVER_KEY : 'licenseKey',
    ];
}
