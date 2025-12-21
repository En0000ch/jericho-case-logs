<?php

namespace Breakdance\MaintenanceMode;

if (BREAKDANCE_MODE === 'breakdance') {
    require_once __DIR__ . "/maintenance-mode.php";
    require_once __DIR__ . "/maintenance-mode-tab.php";
}
