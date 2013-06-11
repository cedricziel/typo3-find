<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2013
 *      Ingo Pfennigstorf <pfennigstorf@sub-goettingen.de>
 *      Sven-S. Porst <porst@sub.uni-goettingen.de>
 *      Göttingen State and University Library
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

/**
 * Returns additional parameters needed to create links for facets.
 *
 * Arguments:
 *	- type: the facet (i.e. typically the Solr field)’s name
 *	- itemName: the value of the facet’s item in question if needed
 *  - activeFacets: the array of active facets
 *  - mode: return an array for
 *		- add: f.link.action’s »arguments«, adding a facet selection
 * 		- remove: f.link.action’s »argumentsToBeExcludedFromQueryString«, removing a facet selection
 *					leaving out the itemName parameter removes all selected items for the facet facetName
 */
class Tx_SolrFrontend_ViewHelpers_FacetLinkArgumentsViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Register arguments.
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('facetName', 'string', 'The name of the facet to create the link for', TRUE);
		$this->registerArgument('itemName', 'string', 'The name of the facet item to create the link for', FALSE, '');
		$this->registerArgument('activeFacets', 'array', 'Array of active facets', FALSE, Array());
		$this->registerArgument('mode', 'string', 'One of »add« or »remove« depending on whether the result is used with »arguments« or with »argumentsToBeExcludedFromQueryString«', FALSE, 'add');
		$this->registerArgument('type', 'string', 'Query type [string, range]', FALSE, 'string');
	}


	/**
	 * Create the return array required to add/remove the URL parameters by
	 * passing it to f.link.action’s »arguments«
	 * or »argumentsToBeExcludedFromQueryString«.
	 *
	 * @return array
	 */
	public function render() {
		$result = array();
		$activeFacets = $this->arguments['activeFacets'];
		$facetData = array('name' => $this->arguments['facetName']);
		if ($this->arguments['type'] === 'string') {
			$facetData['value'] = '"' . $this->arguments['itemName'] . '"';
		}
		else {
			$facetData['value'] = $this->arguments['itemName'];
		}

		if ($this->arguments['mode'] === 'remove' && $activeFacets) {
			if ($facetData['value']) {
				// Remove this specific facet.
				$thisFacetsIndex = array_search($facetData, $activeFacets, FALSE);
				if ($thisFacetsIndex !== FALSE) {
					$result[] = 'tx_solrfrontend_solrfrontend[facet][' . $thisFacetsIndex . ']';
				}
			}
			else {
				// Remove all facets of this type.
				foreach ($activeFacets as $activeFacetIndex => $activeFacet) {
					if ($activeFacet['name'] === $facetData['name']) {
						$result[] = 'tx_solrfrontend_solrfrontend[facet][' . $activeFacetIndex . ']';
					}
				}
			}

			// Go back to page 1.
			$result[] = 'tx_solrfrontend_solrfrontend[offset]';
		}
		else if ($this->arguments['mode'] === 'add') {
			$position = 0;
			while ($activeFacets && array_key_exists($position, $activeFacets) && $position < 10) {
				$position++;
			}
			$result['facet'] = array(
				 $position => $facetData['name'] . ':' . $facetData['value']
			);
		}

		return $result;
	}
}

?>