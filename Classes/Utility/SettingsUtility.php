<?php
namespace Subugoe\Find\Utility;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Ingo Pfennigstorf <pfennigstorf@sub-goettingen.de>
 *      Goettingen State Library
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Configurations and settings
 */
class SettingsUtility
{
    /**
     * Returns the merged settings for the given name.
     * Uses settings.$settingName.default and adds
     * settings.$settingsName.$actionName to it.
     * Settings array keys need to be non-numeric if they are supposed to be overriden.
     *
     * @param string $settingName the key of the subarray of $this->settings to work on
     * @param array  $settings
     * @param string $actionName
     *
     * @return array highlight configuration
     */
    public static function getMergedSettings($settingName, $settings, $actionName = 'index')
    {
        $config = [];

        if (array_key_exists($settingName, $settings)) {
            $setting = $settings[$settingName];

            if (array_key_exists('default', $setting)) {
                $config = $setting['default'];

                if (array_key_exists($actionName, $setting)) {
                    $actionConfig = $setting[$actionName];
                    $config = array_replace_recursive($config, $actionConfig);
                }
            }
        }

        return $config;
    }

    /**
     * Overrides TypoScript settings with respective keys from a 'override' sub-index.
     *
     * @param array $settings
     *
     * @return array
     */
    public static function overrideFlexFormSettings(array $settings = [])
    {
        /*
         * Merge expected structure into settings so it's there even if no
         * FlexForm was used to configure plugin.
         */
        ArrayUtility::mergeRecursiveWithOverrule(
            $settings,
            ['override' => ['queryFields' => [], 'facets' => []]]
        );

        $overriddenFacets = array_map(
            function ($facet) use (&$settings) {
                reset($facet);
                $type = key($facet);
                $definition = current($facet);

                if (ucfirst($type) === 'CategoryList') {
                    $categories = explode(',', $definition['categories']);
                    if (count($categories) > 0) {
                        $settings['additionalFilters'][] = strtolower($definition['field']).':('.implode(' OR ', $categories).')';
                    }
                }

                return [
                    'id'    => strtolower($definition['id']),
                    'field' => strtolower($definition['field']),
                    'type'  => ucfirst($type),
                ];
            },
            $settings['override']['facets']
        );

        $overriddenQueryFields = array_map(
            function ($queryField) {
                reset($queryField);
                $type = key($queryField);
                $definition = current($queryField);

                return [
                    'id'       => strtolower($definition['id']),
                    'extended' => (bool) $definition['extended'],
                    'type'     => ucfirst($type),
                ];
            },
            $settings['override']['queryFields']
        );

        ArrayUtility::mergeRecursiveWithOverrule(
            $settings,
            [
                'facets'      => $overriddenFacets,
                'queryFields' => $overriddenQueryFields,
            ]
        );

        unset($settings['override']['queryFields']);
        unset($settings['override']['facets']);

        ArrayUtility::mergeRecursiveWithOverrule($settings, $settings['override']);

        return $settings;
    }
}
