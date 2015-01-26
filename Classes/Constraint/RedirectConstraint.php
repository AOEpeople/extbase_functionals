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
class Tx_ExtbaseFunctionals_Constraint_RedirectConstraint extends PHPUnit_Framework_Constraint
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
    private $arguments = array();

    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @param string $action
     * @param string $controller
     * @param array $arguments
     * @param integer $statusCode
     */
    public function __construct($action, $controller = null, array $arguments = array(), $statusCode = 303)
    {
        $this->action = $action;
        $this->controller = $controller;
        $this->arguments = $arguments;
        $this->statusCode = $statusCode;
        parent::__construct();
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other)
    {
        if ($other instanceof \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController) {
            $response = $this->getResponse($other);
            $headers = $this->getHeadersAsString($other);
            if ($this->getStatusCode($response) !== $this->statusCode) {
                return false;
            }
            if (null !== $this->controller
                && false === strpos($headers, 'controller%5D=' . $this->controller)
            ) {
                return false;
            }
            foreach ($this->arguments as $key => $value) {
                if (false === strpos($headers, $key . '%5D=' . $value)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'will redirect to Action "%s" in Controller "%s" with status code "%s" and arguments "%s"',
            $this->action,
            $this->controller,
            $this->statusCode,
            implode(',', $this->arguments)
        );
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $other
     * @return string
     */
    protected function failureDescription(\TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $other)
    {
        return '"' . $this->getHeadersAsString($other) . '" ' . $this->toString();
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller
     * @return string
     */
    private function getHeadersAsString(\TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller)
    {
        return implode("\n", $this->getResponse($controller)->getHeaders());
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Response
     */
    private function getResponse(\TYPO3\CMS\Extbase\Mvc\Controller\AbstractController $controller)
    {
        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);
        return $property->getValue($controller);
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Response $response
     * @return integer
     */
    private function getStatusCode(\TYPO3\CMS\Extbase\Mvc\Web\Response $response)
    {
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        return $property->getValue($response);
    }
}
