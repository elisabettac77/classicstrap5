<?php

// Integration Support for WindPress plugin
// https://wind.press/
// A great solution to add TailWind CSS

function pico_scanner_classicstrap_provider(): array
{
    // Any files with this extension will be scanned
    $file_extensions = [
        'php',
        'js',
        'html',
    ];

    // Exclude the classicstrap theme's internal directories
    $parentNotPath = [
        'classicstrap5/css-output',
        'classicstrap5/inc',
        'classicstrap5/js',
        'classicstrap5/languages',
        'classicstrap5/sass',
    ];

    $contents = [];

    // The current active theme
    $wpTheme = wp_get_theme();

    // if the theme is not classicstrap and its' child, early return
    if (!$wpTheme->get('Name') !== 'classicstrap5' && !$wpTheme->get('Name') !== 'classicstrap5 Child Base') {
        return $contents;
    }

    $themeDir = $wpTheme->get_stylesheet_directory();

    $finder = new \WindPressDeps\Symfony\Component\Finder\Finder();

    // Check if the current theme is a child theme and get the parent theme directory
    $has_parent = $wpTheme->parent() ? true : false;
    $parentThemeDir = $wpTheme->parent()->get_stylesheet_directory() ?? null;

    $finder->files()->notPath($parentNotPath);

    // Scan the theme directory according to the file extensions
    foreach ($file_extensions as $extension) {
        $finder->files()->in($themeDir)->name('*.' . $extension);
        if ($has_parent) {
            $finder->files()->in($parentThemeDir)->name('*.' . $extension);
        }
    }

    // Get the file contents and send to the compiler
    foreach ($finder as $file) {
        $contents[] = [
            'name' => $file->getRelativePathname(),
            'content' => $file->getContents(),
        ];
    }

    return $contents;
}

/**
 * @param array $providers The collection of providers that will be used to scan the design payload
 * @return array
 */
function pico_register_classicstrap_provider(array $providers): array
{
    $providers[] = [
        'id' => 'classicstrap',
        'name' => 'classicstrap Theme',
        'description' => 'Scans the classicstrap theme & child theme',
        'callback' => 'pico_scanner_classicstrap_provider', // The function that will be called to get the data
        'enabled' => \WindPress\WindPress\Utils\Config::get(sprintf(
            'integration.%s.enabled',
            'classicstrap' // The id of this custom provider
        ), true),
    ];

    return $providers;
}

add_filter('f!windpress/core/cache:compile.providers', 'pico_register_classicstrap_provider');

