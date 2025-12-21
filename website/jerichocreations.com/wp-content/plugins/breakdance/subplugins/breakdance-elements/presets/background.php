<?php

namespace EssentialElements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\Elements\PresetSections\PresetSectionsController::getInstance()->register(
    "EssentialElements\\background",
    c(
        "background",
        "Background",
        [c(
        "color",
        "Color",
        [],
        ['type' => 'color', 'layout' => 'inline'],
        true,
        false,
        [],
        
      ), c(
        "clip",
        "Clip",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'border-box', 'value' => 'border-box'], ['text' => 'padding-box', 'value' => 'padding-box'], ['text' => 'content-box', 'value' => 'content-box']]],
        true,
        false,
        [],
        
      ), c(
        "layers",
        "Layers",
        [c(
        "type",
        "Type",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'Image', 'value' => 'image'], ['text' => 'Overlay Color', 'value' => 'overlay_color'], ['text' => 'Gradient', 'value' => 'gradient'], ['text' => 'None', 'value' => 'none']]],
        false,
        false,
        [],
        
      ), c(
        "image",
        "Image",
        [],
        ['type' => 'wpmedia', 'layout' => 'vertical', 'condition' => [[['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'image']]], 'mediaOptions' => ['multiple' => false]],
        false,
        false,
        [],
        ['accepts' => 'image_url', 'proOnly' => false]
      ), c(
        "size",
        "Size",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'auto', 'value' => 'auto'], ['text' => 'cover', 'value' => 'cover'], ['text' => 'contain', 'value' => 'contain'], ['text' => 'custom...', 'value' => 'custom']], 'condition' => ['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
        
      ), c(
        "size_manual_width",
        "Width",
        [],
        ['type' => 'unit', 'condition' => ['path' => '%%CURRENTPATH%%.size', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
        
      ), c(
        "size_manual_height",
        "Height",
        [],
        ['type' => 'unit', 'condition' => ['path' => '%%CURRENTPATH%%.size', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
        
      ), c(
        "repeat",
        "Repeat",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'no-repeat', 'value' => 'no-repeat'], ['text' => 'repeat', 'value' => 'repeat'], ['text' => 'repeat-x', 'value' => 'repeat-x'], ['text' => 'repeat-y', 'value' => 'repeat-y']], 'condition' => ['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
        
      ), c(
        "attachment",
        "Attachment",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'scroll', 'value' => 'scroll'], ['text' => 'fixed', 'value' => 'fixed']], 'condition' => ['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
        
      ), c(
        "position",
        "Position",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'center', 'value' => 'center'], ['text' => 'custom...', 'value' => 'custom']], 'condition' => ['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'image']],
        false,
        false,
        [],
        
      ), c(
        "position_manual_left",
        "Left",
        [],
        ['type' => 'unit', 'condition' => ['path' => '%%CURRENTPATH%%.position', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
        
      ), c(
        "position_manual_top",
        "Top",
        [],
        ['type' => 'unit', 'condition' => ['path' => '%%CURRENTPATH%%.position', 'operand' => 'equals', 'value' => 'custom']],
        false,
        false,
        [],
        
      ), c(
        "gradient",
        "Gradient",
        [],
        ['type' => 'color', 'layout' => 'vertical', 'colorOptions' => ['type' => 'gradientOnly'], 'condition' => [[['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'gradient']]]],
        false,
        false,
        [],
        
      ), c(
        "overlay_color",
        "Overlay Color",
        [],
        ['type' => 'color', 'condition' => [[['path' => '%%CURRENTPATH%%.type', 'operand' => 'equals', 'value' => 'overlay_color']]]],
        false,
        false,
        [],
        
      ), c(
        "blend",
        "Blend Mode",
        [],
        ['type' => 'dropdown', 'items' => [['text' => 'normal', 'value' => 'normal'], ['text' => 'multiply', 'value' => 'multiply'], ['text' => 'screen', 'value' => 'screen'], ['text' => 'overlay', 'value' => 'overlay'], ['text' => 'darken', 'value' => 'darken'], ['text' => 'lighten', 'value' => 'lighten'], ['text' => 'color-dodge', 'value' => 'color-dodge'], ['text' => 'color-burn', 'value' => 'color-burn'], ['text' => 'hard-light', 'value' => 'hard-light'], ['text' => 'soft-light', 'value' => 'soft-light'], ['text' => 'difference', 'value' => 'difference'], ['text' => 'exclusion', 'value' => 'exclusion'], ['text' => 'hue', 'value' => 'hue'], ['text' => 'saturation', 'value' => 'saturation'], ['text' => 'color', 'value' => 'color'], ['text' => 'luminosity', 'value' => 'luminosity']]],
        false,
        false,
        [],
        
      )],
        ['type' => 'repeater', 'layout' => 'vertical'],
        true,
        false,
        [],
        
      )],
        ['type' => 'section', 'sectionOptions' => ['type' => 'popout', 'preset' => ['slug' => 'EssentialElements\\background']]],
        false,
        false,
        [],
        
      ),
    true,
    ['relativeDynamicPropertyPaths' => [], 'codeHelp' => '{{ macros.background(%%TWIG_PATH%%) }}']
);

