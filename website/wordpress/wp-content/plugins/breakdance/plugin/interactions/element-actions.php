<?php
// @psalm-ignore-file

namespace Breakdance\Interactions;


add_filter('breakdance_element_actions', '\Breakdance\Interactions\addActions', 100, 1);

/**
 * @param BuilderActions[] $actions
 *
 * @return BuilderActions[]
 *
 * this return type is causing an error in psalm I couldn't solve so I'm ignoring this file
 */
function addActions($actions)
{
    $actions[] = [
        'onPropertyChange' => [
            [
                'script' => <<<JS
                  const customEvent = new CustomEvent('breakdance_refresh_interactions', {
                    bubbles: true
                  });
                  document.querySelector('%%SELECTOR%%').dispatchEvent(customEvent);
                JS,
                'dependencies' => ['settings.interactions']
            ],
        ],
        'onMovedElement' => [
            [
                'script' => <<<JS
                  const customEvent = new CustomEvent('breakdance_refresh_interactions', {
                    bubbles: true
                  });
                  document.querySelector('%%SELECTOR%%').dispatchEvent(customEvent);
                JS,
            ]
        ],
        'onMountedElement' => [
            [
                'script' => <<<JS
                  // TODO: This will run on every element mount, how can we make it more efficient?
                  const interactions = {{ settings.interactions.interactions ? 'true' : 'false' }};

                  if (interactions) {
                    const customEvent = new CustomEvent('breakdance_refresh_interactions', {
                      bubbles: true
                    });
                    document.querySelector('%%SELECTOR%%').dispatchEvent(customEvent);
                  }
                JS,
            ]
        ],
        'onBeforeDeletingElement' => [
            [
                'script' => <<<JS
                  const customEvent = new CustomEvent('breakdance_refresh_interactions', {
                    bubbles: true
                  });
                  document.querySelector('%%SELECTOR%%')?.dispatchEvent(customEvent);
                JS,
            ]
        ],
    ];

    return $actions;
}
