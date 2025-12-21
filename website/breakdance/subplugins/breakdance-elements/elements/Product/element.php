<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "EssentialElements\\Product",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class Product extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'BoxOpenIcon';
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
        return 'Product';
    }

    static function className()
    {
        return 'bde-product';
    }

    static function category()
    {
        return 'woocommerce';
    }

    static function badge()
    {
        return ['backgroundColor' => 'var(--brandWooCommerceBackground)', 'textColor' => 'var(--brandWooCommerce)', 'label' => 'Woo'];
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
        return false;
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
        "image",
        "Image",
        [c(
        "position",
        "Position",
        [],
        ['type' => 'button_bar', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['text' => 'Right', 'value' => 'right']]],
        false,
        false,
        [],
      )],
        ['type' => 'section'],
        false,
        false,
        [],
      ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Spacing",
      "spacing",
       ['type' => 'popout']
     ), c(
        "advanced",
        "Advanced",
        [getPresetSection(
      "EssentialElements\\WooGlobalStylerOverride",
      "Override Global Styles",
      "override_global_styles",
       ['type' => 'popout']
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
        "content",
        "Content",
        [c(
        "product",
        "Product",
        [],
        ['type' => 'post_chooser', 'layout' => 'vertical', 'postChooserOptions' => ['multiple' => false, 'showThumbnails' => true, 'postType' => 'product']],
        false,
        false,
        [],
      ), c(
        "disable_upsells",
        "Disable Upsells",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
        false,
        false,
        [],
      ), c(
        "disable_related",
        "Disable Related",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
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
        return ['0' =>  ['scripts' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.js'],'styles' => ['%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/custom-tabs@1/tabs.css'],'builderCondition' => 'return true;','frontendCondition' => 'return true;',],'1' =>  ['inlineScripts' => ['new BreakdanceTabs(\'%%SELECTOR%%\', { activeTab: 1 });','const hash = window.location.hash;
const url = window.location.href;
const tabs = document.querySelector(\'%%SELECTOR%%\');

function triggerTab(tab, shouldScroll) {
  const button = document.querySelector(`.js-tab[data-value="${tab}"]`);
  
  if (button) {
    button.click();

    if (shouldScroll) {
      setTimeout(() => {
        button.scrollIntoView({
          alignToTop: true,
          behavior: \'instant\'
        }); 
      }, 1);
    }
  }

}

if (hash.toLowerCase().indexOf(\'comment-\') >= 0 || hash === \'#reviews\' || hash === \'#tab-reviews\') {
  triggerTab(\'reviews\', true);
} else if (url.indexOf(\'comment-page-\') > 0 || url.indexOf(\'cpage=\') > 0) {
  triggerTab(\'reviews\', true);
} else if (hash === \'#tab-additional_information\') {
  triggerTab(\'additional_information\', true);
}

document.body.addEventListener("click", (event) => {
  if (event.target.closest(\'.woocommerce-review-link\')) {
    triggerTab(\'reviews\');
  }
});'],'builderCondition' => 'return false;','frontendCondition' => 'return true;',],];
    }

    static function settings()
    {
        return ['dependsOnGlobalScripts' => true, 'requiredPlugins' => ['WooCommerce'], 'proOnly' => true];
    }

    static function addPanelRules()
    {
        return false;
    }

    static public function actions()
    {
        return [

'onPropertyChange' => [['script' => '(function() {
              if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
                window.breakdanceTabsInstances[%%ID%%].destroy();
              }

              window.breakdanceTabsInstances[%%ID%%] = new BreakdanceTabs(\'%%SELECTOR%%\', { activeTab: 1 });
            }());',
],],

'onMountedElement' => [['script' => '(function() {
                if (!window.breakdanceTabsInstances) window.breakdanceTabsInstances = {};

                if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
                  window.breakdanceTabsInstances[%%ID%%].destroy();
                }

                window.breakdanceTabsInstances[%%ID%%] = new BreakdanceTabs(\'%%SELECTOR%%\', { activeTab: 1 } );
              }());',
],],

'onMovedElement' => [['script' => '(function() {
              if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
                window.breakdanceTabsInstances[%%ID%%].update();
              }
            }());',
],],

'onBeforeDeletingElement' => [['script' => '  (function() {
                if (window.breakdanceTabsInstances && window.breakdanceTabsInstances[%%ID%%]) {
                  window.breakdanceTabsInstances[%%ID%%].destroy();
                  delete window.breakdanceTabsInstances[%%ID%%];
                }
              }());',
],],];
    }

    static function nestingRule()
    {
        return ["type" => "final",   ];
    }

    static function spacingBars()
    {
        return [['cssProperty' => 'margin-top', 'location' => 'outside-top', 'affectedPropertyPath' => 'design.spacing.margin_top'], ['cssProperty' => 'margin-bottom', 'location' => 'outside-bottom', 'affectedPropertyPath' => 'design.spacing.margin_bottom']];
    }

    static function attributes()
    {
        return false;
    }

    static function experimental()
    {
        return false;
    }

    static function order()
    {
        return 50;
    }

    static function dynamicPropertyPaths()
    {
        return [];
    }

    static function additionalClasses()
    {
        return [['name' => 'breakdance-woocommerce', 'template' => 'yes']];
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
        return ['content'];
    }
}
