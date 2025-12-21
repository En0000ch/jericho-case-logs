<?php

namespace Breakdance\GoogleFontsPlugin;

/*
do operations like this really have to happen for every request?
should it really be on init? or instead, maybe it sould be on some sort
of setup hook fired by breakdance before rendering... or when its a builder AJAX
request that needs these fonts?
 */

use Breakdance\Fonts\FontsController;

add_action('breakdance_register_fonts', '\Breakdance\GoogleFontsPlugin\loadGoogleFonts');

function loadGoogleFonts(FontsController $fontsController)
{
    $fonts = getFontListFromFile();

    foreach ($fonts as $font) {

        $slug = slugFromFontFamilyName((string) $font['family']);
        $cssName = '"' . (string) $font['family'] . '"';
        $dropdownLabel = (string) $font['family'];
        /** @var ElementDependencyWithoutConditions $dependencies */
        $dependencies = [
            'googleFonts' => [$font['family']],
        ];
        $previewImageUrl = generateFontPreviewUrl((string) $font['family']);
        $category = (string) $font['category'];


        $fallbackString = "";

        if ($font['category'] === 'serif') {
            $fallbackString = 'serif';
        } else if ($font['category'] === 'sans-serif') {
            $fallbackString = 'sans-serif';
        } else if ($font['category'] === 'display') {
            $fallbackString = 'sans-serif';
        } else {
            $fallbackString = 'sans-serif';
        }

        $fontsController->registerFont(
            $slug,
            $cssName,
            $dropdownLabel,
            $fallbackString,
            $dependencies,
            $previewImageUrl,
            $category

        );
    }
}

/**
 * @param string $fontFamily
 * @return string
 */
function generateFontPreviewUrl($fontFamily)
{
    $fontFamilyUri = str_replace(' ', '%20', $fontFamily);
    return "https://raw.githubusercontent.com/khoben/gfont-previews/master/output/previews/" . $fontFamilyUri . "-regular.png";
}

/**
 * @return array{family:string,category:string}[]
 */
function getFontListFromFile()
{
    if (!is_readable(\Breakdance\Fonts\Consts::GOOGLE_FONT_FILE)) {
        return [];
    }
    $fileContents = file_get_contents(\Breakdance\Fonts\Consts::GOOGLE_FONT_FILE);

    if ($fileContents === false) {
        return [];
    }

    /**
     * @psalm-suppress MixedAssignment
     * @var array{items:array{family:string,category:string}[]}
     */
    $validated_google_font_data = json_decode($fileContents, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    return $validated_google_font_data['items'];
}

/**
 * @param string $fontFamily
 * @return string
 */
function slugFromFontFamilyName($fontFamily)
{
    return "gfont-" . strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $fontFamily));
}
