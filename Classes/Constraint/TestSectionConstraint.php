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
class Tx_ExtbaseFunctionals_Constraint_TestSectionConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var string
     */
    private $pattern = 'data-test-section="%s"';

    /**
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other)
    {
        return false !== strpos(
            $other,
            sprintf(
                $this->pattern,
                $this->string
            )
        );
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'has test section "%s"',
            $this->string
        );
    }
}
