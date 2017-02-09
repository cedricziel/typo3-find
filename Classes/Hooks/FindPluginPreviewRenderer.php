<?php

namespace Subugoe\Find\Hooks;

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class FindPluginPreviewRenderer implements PageLayoutViewDrawItemHookInterface
{
    const FLUID_TEMPLATE = <<<TEMPLATE
{namespace s=Subugoe\Find\ViewHelpers}
<h4>Query fields</h4>
<f:if condition="{settings.settings.override.queryFields -> f:count() > 0}">
  <f:then>
    <f:for each="{settings.settings.override.queryFields}" as="queryField">
        <f:for each="{queryField}" as="field" key="type">
            <table class="table">
                <tr>
                    <th>Type</th>
                    <td>{type}</td>
                </tr>
                <tr>
                    <th>ID</th>
                    <td>{field.id}</td>
                </tr>
                <tr>
                    <th>Field</th>
                    <td>{field.field}</td>
                </tr>
            </table>
        </f:for>
    </f:for>
  </f:then>
  <f:else>
    <p>no local query field configuration - inheriting from TypoScript</p>
  </f:else>
</f:if>
<details>
<summary>Facets</summary>
<f:if condition="{settings.settings.override.facets -> f:count()} > 0">
  <f:then>
    <f:for each="{settings.settings.override.facets}" as="facet">
        <f:for each="{facet}" as="field" key="type">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <td>{field.id}</td>
                </tr>
                <tr>
                    <th>Field</th>
                    <td>{field.field}</td>
                </tr>
            </table>
        </f:for>
    </f:for>
  </f:then>
  <f:else>
    <p>no local facet configuration - inheriting from TypoScript</p>
  </f:else>
</f:if>
</details>
TEMPLATE;

    /**
     * @return StandaloneView
     */
    protected static function getFluidTemplate()
    {
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplateSource(static::FLUID_TEMPLATE);

        return $standaloneView;
    }

    /**
     * @param PageLayoutView $parentObject Calling parent object
     * @param bool           $drawItem
     * @param string         $headerContent
     * @param string         $itemContent
     * @param array          $row          Record row of tt_content
     *
     * @return void
     */
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        $contentType = $parentObject->CType_labels[$row['CType']];
        if ($row['CType'] === 'list' && $row['list_type'] === 'find_find') {
            $itemContent .= $parentObject->linkEditContent(
                    '<strong>'.htmlspecialchars($contentType).'</strong>',
                    $row
                ).'<br />';

            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            $flexFormData = $flexFormService->convertFlexFormContentToArray($row['pi_flexform']);

            ArrayUtility::mergeRecursiveWithOverrule(
                $flexFormData,
                [
                    'settings' => [
                        'override' => [
                            'queryFields' => [],
                            'facets'      => [],
                        ],
                    ],
                ]
            );

            $itemContent .= static::getFluidTemplate()->assign('settings', $flexFormData)->render();
            $itemContent .= '<details><summary>Structure</summary><pre>'.print_r($flexFormData, 1).'</pre></details>';

            $drawItem = false;
        }
    }
}
