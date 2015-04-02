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

namespace Aoe\ExtbaseFunctionals\Constraint;

use PHPUnit_Framework_Constraint;
use ReflectionClass;

/**
 * @package ExtbaseFunctionals
 * @subpackage Constraint
 */
class ForwardConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param string $action
     * @param string $controller
     * @param array $arguments
     */
    public function __construct($action, $controller = null, array $arguments = array())
    {
        $this->action = $action;
        $this->controller = $controller;
        $this->arguments = $arguments;
        parent::__construct();
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other)
    {
        if ($other instanceof \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController) {
            $request = $this->getRequest($other);
            if (null !== $this->controller && $request->getControllerName() !== $this->controller) {
                return false;
            }
            foreach ($this->arguments as $expectedArgumentName => $expectedArgumentValue) {
                if (false === array_key_exists($expectedArgumentName, $request->getArguments()) ||
                    false === in_array($expectedArgumentValue, $request->getArguments(), true)
                ) {
                    return false;
                }
            }
            if ($request->getControllerActionName() !== $this->action) {
                return false;
            }
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
            'will forward to Action "%s" in Controller "%s" with arguments "%s"',
            $this->action,
            $this->controller,
            implode(',', $this->arguments)
        );
    }

    /**
     * @param mixed $other
     * @return string
     */
    protected function failureDescription($other)
    {
        return $this->exporter->export($this->getRequest($other)) . ' ' . $this->toString();
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller
     * @return \TYPO3\CMS\Extbase\Mvc\Request
     */
    private function getRequest(\TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller)
    {
        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('request');
        $property->setAccessible(true);
        return $property->getValue($controller);
    }
}
