<?php

namespace Breakdance\Admin;

use function Breakdance\BreakdanceOxygen\Strings\__bdox;

if (BREAKDANCE_MODE === 'breakdance') {
    add_action('admin_menu', 'Breakdance\Admin\breakdance_admin_menu');
} else if (BREAKDANCE_MODE === 'oxygen') {
    add_action('admin_menu', 'Breakdance\Admin\oxygen_admin_menu');
}

function breakdance_admin_menu()
{
    $formCap = \Breakdance\Forms\Submission\getFormSubmissionCapability();

    if (\Breakdance\Permissions\hasPermission('full')) {
        add_menu_page('Breakdance', 'Breakdance', 'manage_options', 'breakdance', '', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4gPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2OCIgaGVpZ2h0PSI2OCIgdmlld0JveD0iMCAwIDQ5IDY4IiBmaWxsPSJub25lIj48cGF0aCBkPSJNMjcuODk5NCA2Ni4wMDFDMzEuNzA5OCA2Ni4wMDEgMzQuNzk4OCA2Mi45MTIgMzQuNzk4OCA1OS4xMDE2QzM0Ljc5ODggNTUuMjkxMSAzMS43MDk4IDUyLjIwMjEgMjcuODk5NCA1Mi4yMDIxQzI0LjA4OSA1Mi4yMDIxIDIxIDU1LjI5MTEgMjEgNTkuMTAxNkMyMSA2Mi45MTIgMjQuMDg5IDY2LjAwMSAyNy44OTk0IDY2LjAwMVoiIGZpbGw9ImJsYWNrIj48L3BhdGg+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik00LjgzNCA0OC4zNEMzLjU1MTg0IDQ4LjMzOTIgMi4zMjI0NSA0Ny44Mjk0IDEuNDE2MDIgNDYuOTIyNkMwLjUwOTU4MSA0Ni4wMTU4IDAuMDAwMjY0ODI3IDQ0Ljc4NjIgMCA0My41MDRDMC4wMDEwNTk5IDQyLjIyMjIgMC41MTA4NjQgNDAuOTkzMiAxLjQxNzQzIDQwLjA4N0MyLjMyNCAzOS4xODA4IDMuNTUzMTkgMzguNjcxNSA0LjgzNSAzOC42NzFIMTQuNTM1QzE1LjgxMTMgMzguNjYyIDE3LjAzMjQgMzguMTQ4OCAxNy45MzE5IDM3LjI0MzNDMTguODMxNCAzNi4zMzc4IDE5LjMzNjUgMzUuMTEzNCAxOS4zMzcgMzMuODM3VjQuODMzQzE5LjMzNzggMy41NTE0NSAxOS44NDcyIDIuMzIyNjIgMjAuNzUzNCAxLjQxNjQzQzIxLjY1OTYgMC41MTAyNCAyMi44ODg1IDAuMDAwNzk0NzU4IDI0LjE3IDBDMjUuNDUxNyAwLjAwMDUyOTgyOCAyNi42ODA4IDAuNTA5ODU4IDI3LjU4NzIgMS40MTYwOEMyOC40OTM2IDIuMzIyMyAyOS4wMDMyIDMuNTUxMjggMjkuMDA0IDQuODMzVjE4LjUwM0MyOS4wMDQ4IDE5Ljc4NDUgMzAuNDIwNCAyMS45MTk2IDMwLjQyMDQgMjEuOTE5NkMzMS4zMjY2IDIyLjgyNTggMzIuNTU1NSAyMy4zMzUyIDMzLjgzNyAyMy4zMzZDMzUuMTE4OSAyMy4zMzU1IDM2LjM0ODEgMjIuODI2IDM3LjI1NDYgMjEuOTE5NkMzOC4xNjEgMjEuMDEzMSAzOC42NzA1IDE5Ljc4MzkgMzguNjcxIDE4LjUwMlY0LjgzM0MzOC42NzE4IDMuNTUxMjggMzkuMTgxNCAyLjMyMjMgNDAuMDg3OCAxLjQxNjA4QzQwLjk5NDIgMC41MDk4NTggNDIuMjIzMyAwLjAwMDUyOTgyOCA0My41MDUgMEM0NC43ODY4IDAuMDAwNTI5NTY1IDQ2LjAxNiAwLjUwOTgyNSA0Ni45MjI2IDEuNDE2MDJDNDcuODI5MSAyLjMyMjIxIDQ4LjMzODkgMy41NTExOSA0OC4zNCA0LjgzM1Y2Mi44NDJDNDguMzM5MiA2NC4xMjQgNDcuODI5NSA2NS4zNTMyIDQ2LjkyMjkgNjYuMjU5NkM0Ni4wMTYzIDY3LjE2NiA0NC43ODcgNjcuNjc1NSA0My41MDUgNjcuNjc2QzQyLjIyMzEgNjcuNjc1NSA0MC45OTM5IDY3LjE2NiA0MC4wODc0IDY2LjI1OTZDMzkuMTgxIDY1LjM1MzEgMzguNjcxNSA2NC4xMjM5IDM4LjY3MSA2Mi44NDJWNTguMDA4QzM4LjY3MSA1Mi42NzEgMzQuMzM5IDQ4LjMzOSAyOS4wMDQgNDguMzM5TDQuODM0IDQ4LjM0WiIgZmlsbD0iYmxhY2siPjwvcGF0aD48L3N2Zz4g');
        add_submenu_page('breakdance', 'Home', 'Home', 'manage_options', 'breakdance', 'Breakdance\Admin\breakdanceHomePage');
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('breakdance', 'Templates', 'Templates', 'manage_options', BREAKDANCE_TEMPLATE_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('breakdance', 'Headers', 'Headers', 'manage_options', BREAKDANCE_HEADER_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('breakdance', 'Footers', 'Footers', 'manage_options', BREAKDANCE_FOOTER_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('breakdance', 'Global Blocks', 'Global Blocks', 'manage_options', BREAKDANCE_BLOCK_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        add_submenu_page('breakdance', 'Popups', 'Popups', 'manage_options', BREAKDANCE_POPUP_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        add_submenu_page('breakdance', 'Form Submissions', 'Form Submissions', $formCap, 'edit.php?post_type=breakdance_form_res');
        add_submenu_page('breakdance', 'Design Library', 'Design Library', 'manage_options', 'breakdance_design_library', "Breakdance\DesignLibrary\getDesignLibraryAppLoader");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('breakdance', 'Quickstart', 'Quickstart', 'manage_options', 'breakdance_onboarding', 'Breakdance\Onboarding\getOnboardingAppLoader');

        bdox_run_action('breakdance_admin_menu');
    } else if (\Breakdance\Forms\Submission\canViewSubmissions()) {
        add_menu_page('Breakdance Form Submissions', 'Submissions', $formCap, 'edit.php?post_type=breakdance_form_res', '', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4gPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2OCIgaGVpZ2h0PSI2OCIgdmlld0JveD0iMCAwIDQ5IDY4IiBmaWxsPSJub25lIj48cGF0aCBkPSJNMjcuODk5NCA2Ni4wMDFDMzEuNzA5OCA2Ni4wMDEgMzQuNzk4OCA2Mi45MTIgMzQuNzk4OCA1OS4xMDE2QzM0Ljc5ODggNTUuMjkxMSAzMS43MDk4IDUyLjIwMjEgMjcuODk5NCA1Mi4yMDIxQzI0LjA4OSA1Mi4yMDIxIDIxIDU1LjI5MTEgMjEgNTkuMTAxNkMyMSA2Mi45MTIgMjQuMDg5IDY2LjAwMSAyNy44OTk0IDY2LjAwMVoiIGZpbGw9ImJsYWNrIj48L3BhdGg+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik00LjgzNCA0OC4zNEMzLjU1MTg0IDQ4LjMzOTIgMi4zMjI0NSA0Ny44Mjk0IDEuNDE2MDIgNDYuOTIyNkMwLjUwOTU4MSA0Ni4wMTU4IDAuMDAwMjY0ODI3IDQ0Ljc4NjIgMCA0My41MDRDMC4wMDEwNTk5IDQyLjIyMjIgMC41MTA4NjQgNDAuOTkzMiAxLjQxNzQzIDQwLjA4N0MyLjMyNCAzOS4xODA4IDMuNTUzMTkgMzguNjcxNSA0LjgzNSAzOC42NzFIMTQuNTM1QzE1LjgxMTMgMzguNjYyIDE3LjAzMjQgMzguMTQ4OCAxNy45MzE5IDM3LjI0MzNDMTguODMxNCAzNi4zMzc4IDE5LjMzNjUgMzUuMTEzNCAxOS4zMzcgMzMuODM3VjQuODMzQzE5LjMzNzggMy41NTE0NSAxOS44NDcyIDIuMzIyNjIgMjAuNzUzNCAxLjQxNjQzQzIxLjY1OTYgMC41MTAyNCAyMi44ODg1IDAuMDAwNzk0NzU4IDI0LjE3IDBDMjUuNDUxNyAwLjAwMDUyOTgyOCAyNi42ODA4IDAuNTA5ODU4IDI3LjU4NzIgMS40MTYwOEMyOC40OTM2IDIuMzIyMyAyOS4wMDMyIDMuNTUxMjggMjkuMDA0IDQuODMzVjE4LjUwM0MyOS4wMDQ4IDE5Ljc4NDUgMzAuNDIwNCAyMS45MTk2IDMwLjQyMDQgMjEuOTE5NkMzMS4zMjY2IDIyLjgyNTggMzIuNTU1NSAyMy4zMzUyIDMzLjgzNyAyMy4zMzZDMzUuMTE4OSAyMy4zMzU1IDM2LjM0ODEgMjIuODI2IDM3LjI1NDYgMjEuOTE5NkMzOC4xNjEgMjEuMDEzMSAzOC42NzA1IDE5Ljc4MzkgMzguNjcxIDE4LjUwMlY0LjgzM0MzOC42NzE4IDMuNTUxMjggMzkuMTgxNCAyLjMyMjMgNDAuMDg3OCAxLjQxNjA4QzQwLjk5NDIgMC41MDk4NTggNDIuMjIzMyAwLjAwMDUyOTgyOCA0My41MDUgMEM0NC43ODY4IDAuMDAwNTI5NTY1IDQ2LjAxNiAwLjUwOTgyNSA0Ni45MjI2IDEuNDE2MDJDNDcuODI5MSAyLjMyMjIxIDQ4LjMzODkgMy41NTExOSA0OC4zNCA0LjgzM1Y2Mi44NDJDNDguMzM5MiA2NC4xMjQgNDcuODI5NSA2NS4zNTMyIDQ2LjkyMjkgNjYuMjU5NkM0Ni4wMTYzIDY3LjE2NiA0NC43ODcgNjcuNjc1NSA0My41MDUgNjcuNjc2QzQyLjIyMzEgNjcuNjc1NSA0MC45OTM5IDY3LjE2NiA0MC4wODc0IDY2LjI1OTZDMzkuMTgxIDY1LjM1MzEgMzguNjcxNSA2NC4xMjM5IDM4LjY3MSA2Mi44NDJWNTguMDA4QzM4LjY3MSA1Mi42NzEgMzQuMzM5IDQ4LjMzOSAyOS4wMDQgNDguMzM5TDQuODM0IDQ4LjM0WiIgZmlsbD0iYmxhY2siPjwvcGF0aD48L3N2Zz4g');
        add_submenu_page('breakdance', 'Form Submissions', 'Form Submissions', 'editor', 'edit.php?post_type=breakdance_form_res');
    }
}


function oxygen_admin_menu()
{
    if (\Breakdance\Permissions\hasPermission('full')) {
        add_menu_page(
            'Oxygen',
            'Oxygen',
            'manage_options',
            'oxygen',
            '',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzMiIGhlaWdodD0iMzMiIHZpZXdCb3g9IjAgMCAzMyAzMyIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xNi41IDI5LjVDMTcuNzgwNiAyOS41IDE5LjAxOCAyOS4zMTQ4IDIwLjE4NjggMjguOTY5OEMxOS43MjQyIDI4LjA3NDYgMTkuNDYzIDI3LjA1ODYgMTkuNDYzIDI1Ljk4MTVDMTkuNDYzIDIyLjM4MTQgMjIuMzgxNCAxOS40NjMgMjUuOTgxNSAxOS40NjNDMjcuMDU4NiAxOS40NjMgMjguMDc0NiAxOS43MjQyIDI4Ljk2OTggMjAuMTg2OEMyOS4zMTQ4IDE5LjAxOCAyOS41IDE3Ljc4MDYgMjkuNSAxNi41QzI5LjUgOS4zMjAzIDIzLjY3OTcgMy41IDE2LjUgMy41QzkuMzIwMyAzLjUgMy41IDkuMzIwMyAzLjUgMTYuNUMzLjUgMjMuNjc5NyA5LjMyMDMgMjkuNSAxNi41IDI5LjVaTTMxLjM5NjMgMjIuMzUxMkMzMi4xMDg4IDIwLjUzODkgMzIuNSAxOC41NjUxIDMyLjUgMTYuNUMzMi41IDcuNjYzNDQgMjUuMzM2NiAwLjUgMTYuNSAwLjVDNy42NjM0NCAwLjUgMC41IDcuNjYzNDQgMC41IDE2LjVDMC41IDI1LjMzNjYgNy42NjM0NCAzMi41IDE2LjUgMzIuNUMxOC41NjUxIDMyLjUgMjAuNTM4OSAzMi4xMDg4IDIyLjM1MTIgMzEuMzk2M0MyMy4zODg3IDMyLjA5MzMgMjQuNjM3NiAzMi41IDI1Ljk4MTUgMzIuNUMyOS41ODE2IDMyLjUgMzIuNSAyOS41ODE2IDMyLjUgMjUuOTgxNUMzMi41IDI0LjYzNzYgMzIuMDkzMyAyMy4zODg3IDMxLjM5NjMgMjIuMzUxMlpNMjkuNSAyNS45ODE1QzI5LjUgMjcuOTI0NyAyNy45MjQ3IDI5LjUgMjUuOTgxNSAyOS41QzI0LjAzODMgMjkuNSAyMi40NjMgMjcuOTI0NyAyMi40NjMgMjUuOTgxNUMyMi40NjMgMjQuMDM4MyAyNC4wMzgzIDIyLjQ2MyAyNS45ODE1IDIyLjQ2M0MyNy45MjQ3IDIyLjQ2MyAyOS41IDI0LjAzODMgMjkuNSAyNS45ODE1WiIgZmlsbD0iY3VycmVudENvbG9yIi8+Cjwvc3ZnPg=='
        );

        add_submenu_page('oxygen', 'Home', 'Home', 'manage_options', 'oxygen', 'Breakdance\Admin\oxygenHomePage');
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('oxygen', 'Templates', 'Templates', 'manage_options', BREAKDANCE_TEMPLATE_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('oxygen', 'Headers', 'Headers', 'manage_options', BREAKDANCE_HEADER_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('oxygen', 'Footers', 'Footers', 'manage_options', BREAKDANCE_FOOTER_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        /** @psalm-suppress UndefinedFunction  */
        add_submenu_page('oxygen', __bdox('global_blocks'), __bdox('global_blocks'), 'manage_options', BREAKDANCE_BLOCK_POST_TYPE, "Breakdance\Themeless\ManageTemplates\getManageBreakdancePostTypesSpaHtml");
        add_submenu_page('oxygen', 'Design Library', 'Design Library', 'manage_options', 'breakdance_design_library', "Breakdance\DesignLibrary\getDesignLibraryAppLoader");

        bdox_run_action('breakdance_admin_menu');
    }
}
