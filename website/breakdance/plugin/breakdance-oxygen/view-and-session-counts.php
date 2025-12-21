<?php

namespace Breakdance\BreakdanceOxygen;

if (BREAKDANCE_MODE === 'oxygen') {
    add_filter(
        'breakdance_disable_track_view_and_session_counts',
        function () {
            return true;
        }
    );
}
