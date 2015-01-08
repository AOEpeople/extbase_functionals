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
     * @var string
     */
    private $propertyPath;

    /**
     * @param integer $code
     * @param string $message
     * @param string $propertyPath
     */
    public function __construct($code, $message = '', $propertyPath = '')
    {
        $this->code = $code;
        $this->message = $message;
        $this->propertyPath = $propertyPath;
        parent::__construct();
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other)
    {
        if ($other instanceof \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController) {
            return $this->checkErrors($this->getArguments($other)->getValidationResults()->getFlattenedErrors());
        }
        return false;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments
     * @return boolean
     */
    private function hasError(\TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments)
    {
        foreach ($arguments as $argument) {
            /** @var \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument */
            /** @var \TYPO3\CMS\Extbase\Error\Result $result */
            $result = $argument->getValidationResults();
            if ($result->hasErrors()) {
                if ($this->checkErrors($result->getFlattenedErrors())) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $errors
     * @param string $propertyPath
     * @return boolean
     */
    private function checkErrors(array $errors, $propertyPath = '')
    {
        foreach ($errors as $key => $error) {
            if (is_string($key)) {
                $propertyPath = trim($propertyPath . '.' . $key, '.');
            }
            if (is_array($error)) {
                return $this->checkErrors($error, $propertyPath);
            }
            /** @var \TYPO3\CMS\Extbase\Validation\Error $error */
            if ($error->getCode() === $this->code &&
                ($this->message === '' || $this->message === $error->getMessage()) &&
                ($this->propertyPath === '' || $this->propertyPath === $propertyPath)
            ) {
                return true;
            }
            return false;
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
            'will produce error with code "%s", message "%s" and property path "%s"',
            $this->code,
            $this->message,
            $this->propertyPath
        );
    }

    /**
     * @param mixed $other
     * @return string
     */
    protected function failureDescription($other)
    {
        return $this->exporter->export($this->getArguments($other)->getValidationResults()->getFlattenedErrors())
        . ' ' . $this->toString();
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

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller
     * @return \TYPO3\CMS\Extbase\Mvc\Controller\Arguments
     */
    private function getArguments(\TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller)
    {
        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('arguments');
        $property->setAccessible(true);
        return $property->getValue($controller);
    }
}
