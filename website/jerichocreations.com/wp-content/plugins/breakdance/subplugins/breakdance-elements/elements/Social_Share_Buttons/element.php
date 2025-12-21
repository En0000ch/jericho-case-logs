<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\SocialShareButtons",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class SocialShareButtons extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'StarIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function tagOptions()
    {
        return [];
    }

    static function tagControlPath()
    {
        return false;
    }

    static function name()
    {
        return 'Social Share Buttons';
    }

    static function className()
    {
        return 'bde-social-share-buttons';
    }

    static function category()
    {
        return 'blocks';
    }

    static function badge()
    {
        return false;
    }

    static function slug()
    {
        return __CLASS__;
    }

    static function template()
    {
        return file_get_contents(__DIR__ . '/html.twig');
    }

    static function defaultCss()
    {
        return file_get_contents(__DIR__ . '/default.css');
    }

    static function defaultProperties()
    {
        return ['content' => ['buttons' => ['buttons' => [['network' => 'Facebook'], ['network' => 'Twitter'], ['network' => 'LinkedIn'], ['network' => 'Email']]], 'share' => ['url' => 'page', 'text' => 'title']], 'design' => ['style' => ['display' => 'icon-text', 'style' => 'flat'], 'button' => ['icon_size' => ['breakpoint_base' => ['number' => 40, 'unit' => 'px', 'style' => '40px']], 'padding' => ['breakpoint_base' => ['number' => 12, 'unit' => 'px', 'style' => '12px']]], 'spacing' => ['between_buttons' => ['breakpoint_base' => ['number' => 20, 'unit' => 'px', 'style' => '20px'], 'breakpoint_phone_landscape' => ['number' => 15, 'unit' => 'px', 'style' => '15px']]], 'position' => ['placement' => 'inplace', 'alignment' => 'flex-start'], 'typography' => null]];
    }

    static function defaultChildren()
    {
        return false;
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [c(
        "style",
        "Style",
        [c(
        "display",
        "Display",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'icon-text', 'text' => 'Icon & Text'], ['text' => 'Icon', 'value' => 'icon'], ['text' => 'Text', 'value' => 'text']]],
        false,
        false,
        [],
        
      ), c(
        "style",
        "Style",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'outline', 'text' => 'Outline'], ['text' => 'Minimal', 'value' => 'minimal'], ['text' => 'Box', 'value' => 'box'], ['text' => 'Flat', 'value' => 'flat']], 'condition' => ['path' => 'design.style.display', 'operand' => 'is none of', 'value' => ['icon']]],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "button",
        "Button",
        [c(
        "icon_size",
        "Icon Size",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['px'], 'defaultType' => 'px'], 'rangeOptions' => ['step' => 1, 'min' => 12, 'max' => 120]],
        true,
        false,
        [],
        
      ), c(
        "padding",
        "Padding",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['px'], 'defaultType' => 'px'], 'rangeOptions' => ['step' => 1, 'min' => 0, 'max' => 30]],
        true,
        false,
        [],
        
      ), c(
        "colors",
        "Colors",
        [c(
        "icon",
        "Icon",
        [],
        ['type' => 'color', 'layout' => 'inline', 'colorOptions' => ['type' => 'solidOnly']],
        false,
        true,
        [],
        
      ), c(
        "background",
        "Background",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
        
      ), c(
        "border",
        "Border",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        false,
        true,
        [],
        
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      ), c(
        "border",
        "Border",
        [c(
        "style",
        "Style",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'square', 'text' => 'Square'], ['text' => 'Circle', 'value' => 'circle'], ['text' => 'Custom', 'value' => 'custom']]],
        false,
        false,
        [],
        
      ), c(
        "radius",
        "Radius",
        [],
        ['type' => 'border_radius', 'layout' => 'vertical', 'condition' => ['path' => 'design.button.border.style', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      ), c(
        "position",
        "Position",
        [c(
        "placement",
        "Placement",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'inplace', 'text' => 'Static'], ['text' => 'Floating', 'value' => 'floating']]],
        false,
        false,
        [],
        
      ), c(
        "floating_position",
        "Floating Position",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left', 'icon' => 'FlexAlignLeftIcon'], ['text' => 'Right', 'value' => 'right', 'icon' => 'FlexAlignRightIcon'], ['text' => 'Top', 'value' => 'top', 'icon' => 'FlexAlignTopIcon'], ['text' => 'Bottom', 'value' => 'bottom', 'icon' => 'FlexAlignBottomIcon']], 'buttonBarOptions' => ['size' => 'small'], 'condition' => ['path' => 'design.position.placement', 'operand' => 'equals', 'value' => 'floating']],
        false,
        false,
        [],
        
      ), c(
        "alignment",
        "Alignment",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'flex-start', 'text' => 'Left', 'icon' => 'AlignLeftIcon'], ['text' => 'Center', 'value' => 'center', 'icon' => 'AlignCenterIcon'], ['text' => 'Right', 'value' => 'flex-end', 'icon' => 'AlignRightIcon'], ['text' => 'Justify', 'value' => 'space-between', 'icon' => 'AlignJustifyIcon']], 'buttonBarOptions' => ['size' => 'small']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      ), c(
        "typography",
        "Typography",
        [getPresetSection(
      "EssentialElements\\typography",
      "Text",
      "text",
       ['type' => 'popout']
     )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      ), c(
        "spacing",
        "Spacing",
        [getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Container",
      "container",
       ['type' => 'popout']
     ), c(
        "between_buttons",
        "Between Buttons",
        [],
        ['type' => 'unit', 'layout' => 'inline', 'unitOptions' => ['types' => ['px', '%', 'em', 'rem', 'custom'], 'defaultType' => 'px'], 'rangeOptions' => ['step' => 1, 'min' => 0, 'max' => 100]],
        true,
        false,
        [],
        
      )],
        ['type' => 'section'],
        false,
        false,
        [],
        
      )];
    }

    static function contentControls()
    {
        return [c(
        "buttons",
        "Buttons",
        [c(
        "buttons",
        "Buttons",
        [c(
        "network",
        "Network",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'Facebook', 'text' => 'Facebook'], ['text' => 'Twitter', 'value' => 'Twitter'], ['text' => 'Pinterest', 'value' => 'Pinterest'], ['text' => 'LinkedIn', 'value' => 'LinkedIn'], ['text' => 'VK', 'value' => 'VK'], ['text' => 'Tumblr', 'value' => 'Tumblr'], ['text' => 'Reddit', 'value' => 'Reddit'], ['text' => 'Digg', 'value' => 'Digg'], ['text' => 'StumbleUpon', 'value' => 'StumbleUpon'], ['text' => 'Pocket', 'value' => 'Pocket'], ['text' => 'WhatsApp', 'value' => 'WhatsApp'], ['text' => 'Xing', 'value' => 'Xing'], ['text' => 'Telegram', 'value' => 'Telegram'], ['text' => 'Skype', 'value' => 'Skype'], ['text' => 'Print', 'value' => 'Print'], ['text' => 'Email', 'value' => 'Email']]],
        false,
        false,
        [],
        
      ), c(
        "custom_label",
        "Custom Label",
        [],
        ['type' => 'text', 'layout' => 'inline'],
        false,
        false,
        [],
        
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{network}', 'defaultTitle' => 'Network', 'buttonName' => 'Add Button']],
        false,
        false,
        [],
        
      ), c(
        "responsive",
        "Responsive",
        [c(
        "button_text",
        "Button Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "share",
        "Share",
        [c(
        "url",
        "URL",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'page', 'text' => 'Current Page'], ['text' => 'Custom URL', 'value' => 'custom_url']]],
        false,
        false,
        [],
        
      ), c(
        "custom_url",
        "Custom URL",
        [],
        ['type' => 'url', 'layout' => 'vertical', 'condition' => ['path' => 'content.share.url', 'operand' => 'equals', 'value' => 'custom_url']],
        false,
        false,
        [],
        
      ), c(
        "text",
        "Text",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'title', 'text' => 'Page Title'], ['text' => 'Custom Text', 'value' => 'custom_text']]],
        false,
        false,
        [],
        
      ), c(
        "custom_text",
        "Custom Text",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'textOptions' => ['multiline' => true], 'placeholder' => '', 'condition' => ['path' => 'content.share.text', 'operand' => 'equals', 'value' => 'custom_text']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )];
    }

    static function settingsControls()
    {
        return [];
    }

    static function dependencies()
    {
        return ['0' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/social-share-buttons@1/social-share-buttons.js'],],'1' =>  ['inlineScripts' => ['new BreakdanceSocialShareButtons(\'%%SELECTOR%%\');'],'builderCondition' => 'return false;','frontendCondition' => 'return true;',],];
    }

    static function settings()
    {
        return ['bypassPointerEvents' => true];
    }

    static function addPanelRules()
    {
        return false;
    }

    static public function actions()
    {
        return [

'onPropertyChange' => [['script' => '(function() {
          if (window.breakdanceImageComparisonInstances && window.breakdanceSocialShareButtonsInstances[%%ID%%]) {
            window.breakdanceSocialShareButtonsInstances[%%ID%%].destroy();
          }

          window.breakdanceSocialShareButtonsInstances[%%ID%%] = new BreakdanceSocialShareButtons(\'%%SELECTOR%%\');
        }());',
],],

'onMountedElement' => [['script' => '(function() {
            if (!window.breakdanceSocialShareButtonsInstances) window.breakdanceSocialShareButtonsInstances = {};

            if (window.breakdanceSocialShareButtonsInstances && window.breakdanceSocialShareButtonsInstances[%%ID%%]) {
              window.breakdanceSocialShareButtonsInstances[%%ID%%].destroy();
            }

            window.breakdanceSocialShareButtonsInstances[%%ID%%] = new BreakdanceSocialShareButtons(\'%%SELECTOR%%\');
          }());
        ',
],],

'onMovedElement' => [['script' => '(function() {
          if (window.breakdanceSocialShareButtonsInstances && window.breakdanceSocialShareButtonsInstances[%%ID%%]) {
            window.breakdanceSocialShareButtonsInstances[%%ID%%].update();
          }
        }());',
],],

'onBeforeDeletingElement' => [['script' => ' (function() {
            if (window.breakdanceSocialShareButtonsInstances && window.breakdanceSocialShareButtonsInstances[%%ID%%]) {
              window.breakdanceSocialShareButtonsInstances[%%ID%%].destroy();
              delete window.breakdanceSocialShareButtonsInstances[%%ID%%];
            }
          }());',
],],];
    }

    static function nestingRule()
    {
        return ['type' => 'final'];
    }

    static function spacingBars()
    {
        return [['location' => 'outside-top', 'cssProperty' => 'margin-top', 'affectedPropertyPath' => 'design.spacing.container.margin_top.%%BREAKPOINT%%'], ['location' => 'outside-bottom', 'cssProperty' => 'margin-bottom', 'affectedPropertyPath' => 'design.spacing.container.margin_bottom.%%BREAKPOINT%%']];
    }

    static function attributes()
    {
        return false;
    }

    static function experimental()
    {
        return false;
    }

    static function availableIn()
    {
        return ['breakdance'];
    }


    static function order()
    {
        return 16500;
    }

    static function dynamicPropertyPaths()
    {
        return false;
    }

    static function additionalClasses()
    {
        return false;
    }

    static function projectManagement()
    {
        return false;
    }

    static function propertyPathsToWhitelistInFlatProps()
    {
        return false;
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return false;
    }
}
