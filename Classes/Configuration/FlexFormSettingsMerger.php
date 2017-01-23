<?php

namespace Subugoe\Find\Configuration;

/**
 * Merges settings from a FlexForm with a given TypoScript
 * context array.
 */
class FlexFormSettingsMerger
{
    /**
     * @var array
     */
    protected $typoScriptSettings;

    /**
     * @param array $typoScriptSettings
     */
    public function initialize(array $typoScriptSettings)
    {
        $this->typoScriptSettings = $typoScriptSettings;
    }

    /**
     * @return array
     */
    public function process()
    {
        return [];
    }
}
