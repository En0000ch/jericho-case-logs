<?php

if (BREAKDANCE_MODE === 'oxygen') {
    add_filter(
        'breakdance_disable_default_api_keys',
        function () {
            return true;
        }
    );
}
