<?php


$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['find_find'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'find_find',
    'FILE:EXT:find/Configuration/FlexForms/flexform_find.xml'
);
