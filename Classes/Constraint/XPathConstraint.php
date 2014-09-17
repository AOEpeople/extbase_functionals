<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 AOE GmbH <dev@aoe.com>
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
 ***************************************************************/

/**
 * @package Checkout
 * @subpackage Tests
 */
class Tx_ExtbaseFunctionals_Constraint_XPathConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @var string
     */
    private $xPath;

    /**
     * @param string $xPath
     */
    public function __construct($xPath)
    {
        $this->xPath = $xPath;
    }

    /**
     * @param string $other
     * @return boolean
     */
    protected function matches($other)
    {
        $document = new DOMDocument();
        $document->loadHTML($other);
        $xpath = new DOMXpath($document);
        $elements = $xpath->query($this->xPath);
        if ($elements->length < 1) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'matches xpath "%s"',
            $this->xPath
        );
    }
}
