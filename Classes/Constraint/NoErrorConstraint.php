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
 * @package ExtbaseFunctionals
 * @subpackage Constraint
 */
class Tx_ExtbaseFunctionals_Constraint_NoErrorConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other)
    {
        if (count($this->getRequest($other)->getErrors()) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'will produce no errors'
        );
    }

    /**
     * @param mixed $other
     * @return string
     */
    protected function failureDescription($other)
    {
        return PHPUnit_Util_Type::export($this->getRequest($other)->getErrors()) . ' ' . $this->toString();
    }

    /**
     * @param Tx_Extbase_MVC_Controller_AbstractController $controller
     * @return Tx_Extbase_MVC_Request
     */
    private function getRequest(Tx_Extbase_MVC_Controller_AbstractController $controller)
    {
        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('request');
        $property->setAccessible(true);
        return $property->getValue($controller);
    }
}
