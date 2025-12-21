<?php

namespace Breakdance\Interactions;

use function Breakdance\Elements\control;
use function Breakdance\Elements\controlSection;
use function Breakdance\String\snake;

class InteractionProvider
{

    use \Breakdance\Singleton;

    /**
     * @var InteractionAction[]
     */
    public $actions = [];
    /**
     * @var InteractionTrigger[]
     */
    public $triggers = [];

    /**
     * Get the builder's path.
     * @return string
     */
    public $path = 'settings.interactions.interactions';

    public function __construct()
    {
        // Defaults
        $this->triggers = [
            // Element triggers
            new \Breakdance\Interactions\Triggers\Click(),
            new \Breakdance\Interactions\Triggers\MouseEnter(),
            new \Breakdance\Interactions\Triggers\MouseLeave(),
            new \Breakdance\Interactions\Triggers\ScrollIntoView(),
            new \Breakdance\Interactions\Triggers\KeyUp(),
            new \Breakdance\Interactions\Triggers\KeyDown(),

            // Page triggers
            new \Breakdance\Interactions\Triggers\PageLoaded(),
            new \Breakdance\Interactions\Triggers\PageScrolled(),

            //new \Breakdance\Interactions\Triggers\MouseLeaveWindow(),
            //new \Breakdance\Interactions\Triggers\MouseMoveInViewport(),
            // new \Breakdance\Interactions\Triggers\VisibilityChange(),

            // Element-dependent triggers
            //new \Breakdance\Interactions\Triggers\DropdownMenuOpens(),
            //new \Breakdance\Interactions\Triggers\MobileMenuOpens(),
            //new \Breakdance\Interactions\Triggers\FormSubmit(),
            //new \Breakdance\Interactions\Triggers\SliderChange(),
            //new \Breakdance\Interactions\Triggers\TabChange(),
        ];

        $this->actions = [
            //new \Breakdance\Interactions\Actions\StartAnimation,

            new \Breakdance\Interactions\Actions\ShowElement(),
            new \Breakdance\Interactions\Actions\HideElement(),
            //new \Breakdance\Interactions\Actions\ToggleElement(),

            new \Breakdance\Interactions\Actions\AddClass(),
            new \Breakdance\Interactions\Actions\RemoveClass(),
            new \Breakdance\Interactions\Actions\ToggleClass,

            new \Breakdance\Interactions\Actions\SetVariable(),

            new \Breakdance\Interactions\Actions\SetAttribute(),
            new \Breakdance\Interactions\Actions\RemoveAttribute(),
            //new \Breakdance\Interactions\Actions\ToggleAttribute(),
            new \Breakdance\Interactions\Actions\ScrollTo(),
            new \Breakdance\Interactions\Actions\Focus(),
            new \Breakdance\Interactions\Actions\Blur(),

            //new \Breakdance\Interactions\Actions\ControlPopup(),
            //new \Breakdance\Interactions\Actions\ControlSlider(),

            new \Breakdance\Interactions\Actions\JavascriptFunction()
        ];
    }

    /**
     * @param InteractionAction $action
     */
    public function registerAction(InteractionAction $action)
    {
        $this->actions[] = $action;
    }

    /**
     * @param InteractionTrigger $trigger
     */
    public function registerTrigger(InteractionTrigger $trigger)
    {
        $this->triggers[] = $trigger;
    }

    /**
     * Get a list of actions instances
     * @return InteractionAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Get a list of triggers instances
     * @return InteractionTrigger[]
     */
    public function getTriggers()
    {
        return $this->triggers;
    }

    /**
     * @param string $slug
     * @return InteractionAction|null
     */
    public function getActionBySlug($slug)
    {
        foreach ($this->actions as $action) {
            if ($slug == $action->slug()) {
                return $action;
            }
        }

        return null;
    }

    /**
     * @param string $slug
     * @return InteractionTrigger|null
     */
    public function getTriggerBySlug($slug)
    {
        foreach ($this->triggers as $trigger) {
            if ($slug == $trigger->slug()) {
                return $trigger;
            }
        }

        return null;
    }
}

function registerAction(InteractionAction $action)
{
    InteractionProvider::getInstance()->registerAction($action);
}

function registerTrigger(InteractionTrigger $trigger)
{
    InteractionProvider::getInstance()->registerTrigger($trigger);
}

/**
 * @param string $case
 * @param InteractionTrigger $trigger
 * @return Control
 */
function createActionControl($case, $trigger)
{
    return control(snake($case), $case, [
        'type' => 'interaction_action',
        'layout' => 'vertical',
        'condition' => [
            'path' => '%%CURRENTPATH%%.trigger',
            'operand' => 'equals',
            'value' => $trigger::slug(),
        ],
    ]);
}

/**
 * @return array{actions: InteractionAction[], triggers: InteractionTrigger[]}
 */
function getInteractionsData()
{
    return [
        'actions' => InteractionProvider::getInstance()->getActions(),
        'triggers' => InteractionProvider::getInstance()->getTriggers(),
    ];
}
