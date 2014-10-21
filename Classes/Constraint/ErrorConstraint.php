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
class Tx_ExtbaseFunctionals_Constraint_ErrorConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @var integer
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @param integer $code
     * @param string $message
     */
    public function __construct($code, $message = '')
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other)
    {
        if ($other instanceof Tx_Extbase_MVC_Controller_AbstractController) {
            return $this->hasError($this->getRequest($other)->getErrors());
        }
        return false;
    }

    /**
     * @param mixed $errors
     * @return boolean
     */
    private function hasError($errors)
    {
        if (is_array($errors)) {
            foreach ($errors as $error) {
                if ($this->hasError($error)) {
                    return true;
                }
            }
        }
        if ($errors instanceof Tx_Extbase_Validation_PropertyError) {
            if ($this->checkError($errors)) {
                return true;
            }
            if ($this->hasError($errors->getErrors())) {
                return true;
            }
        }
        if ($errors instanceof Tx_Extbase_Error_Error) {
            if ($this->checkError($errors)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Tx_Extbase_Error_Error $error
     * @return boolean
     */
    private function checkError(Tx_Extbase_Error_Error $error)
    {
        if ($error->getCode() === $this->code && ($this->message === '' || $this->message === $error->getMessage())) {
            return true;
        }
        return false;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'will produce error with code "%s" and message "%s"',
            $this->code,
            $this->message
        );
    }

    /**
     * @param mixed $other
     * @return string
     */
    protected function failureDescription($other)
    {
        return PHPUnit_Util_Type::export($this->getRequest($other)) . ' ' . $this->toString();
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
