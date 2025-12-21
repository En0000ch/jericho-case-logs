<?php

namespace Breakdance\Onboarding;

function getOnboardingAppLoader()
{
?>

    <style>
        .breakdance_page_breakdance_onboarding {
            background: #fff;
        }

        .breakdance_page_breakdance_onboarding #adminmenumain,
        .breakdance_page_breakdance_onboarding #wpadminbar,

        .notice,
        div.error,
        div.updated,
        #wpfooter {
            display: none;
        }

        .breakdance_page_breakdance_onboarding #wpbody-content,
        .breakdance_page_breakdance_onboarding #wpcontent {
            padding: 0;
            margin: 0;
        }

        html.wp-toolbar {
            padding-top: 0;
        }


        .onboarding-app iframe {
            display: block;
            width: 100%;
            height: 100vh;
        }
    </style>


    <div class="onboarding-app">
        <iframe id="manage-templates-wrapper-iframe" width="100%" frameborder="0"
            src="<?= site_url("?breakdance=onboarding-app") ?>">
        </iframe>
    </div>

<?php
}
