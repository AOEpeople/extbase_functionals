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
 * Base class for controller tests.
 *
 * @package ExtbaseFunctionals
 * @subpackage Test
 */
abstract class Tx_ExtbaseFunctionals_Test_BaseControllerTest
    extends Tx_ExtbaseFunctionals_Test_BaseStubTest
{
    /**
     * @var Tx_Extbase_MVC_Controller_ActionController
     */
    protected $controller;

    /**
     * @return string
     */
    abstract protected function initializeController();

    /**
     * @return string
     */
    abstract protected function getPluginName();

    /**
     * @return string
     */
    abstract protected function getExtensionName();

    /**
     * set up controller
     */
    public function setUp()
    {
        $bootstrap = new Tx_Extbase_Core_Bootstrap();
        $bootstrap->initialize(
            array(
                'extensionName' => $this->getExtensionName(),
                'pluginName' => $this->getPluginName()
            )
        );
        $this->registerStubs();
        $this->initializeController();
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array $arguments
     * @param string $method
     * @return Tx_Extbase_MVC_Response
     */
    protected function processRequestWith($controller, $action, array $arguments = array(), $method = 'GET')
    {
        $request = new Tx_Extbase_MVC_Web_Request();
        $request->setControllerActionName($action);
        $request->setControllerName($controller);
        $request->setMethod($method);
        $request->setControllerExtensionName($this->getExtensionName());
        $request->setHmacVerified(true);

        foreach ($arguments as $key => $value) {
            $request->setArgument($key, $value);
        }

        $response = new Tx_Extbase_MVC_Web_Response();

        try {
            $this->controller->processRequest($request, $response);
        } catch (Tx_Extbase_MVC_Exception_StopAction $ignoredException) {
        }

        return $response;
    }

    /**
     * @param string $expectedAction
     * @param string $expectedController
     * @param array $expectedParameters
     */
    protected function assertForward($expectedAction, $expectedController, array $expectedParameters = array())
    {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_ForwardConstraint(
            $expectedAction,
            $expectedController,
            $expectedParameters
        );
        self::assertThat($this->controller, $constraint);
    }

    /**
     * @param string $expectedAction
     * @param string $expectedController
     * @param array $expectedParameters
     * @param integer $expectedStatusCode
     */
    protected function assertRedirect(
        $expectedAction,
        $expectedController = null,
        array $expectedParameters = array(),
        $expectedStatusCode = 303
    ) {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_RedirectConstraint(
            $expectedAction,
            $expectedController,
            $expectedParameters,
            $expectedStatusCode
        );
        self::assertThat($this->controller, $constraint);
    }


    /**
     * @param string $actualString
     * @param string $expectedPath
     */
    protected function assertXPathPresent($actualString, $expectedPath)
    {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_XPathConstraint($expectedPath);
        self::assertThat($actualString, $constraint);
    }

    /**
     * @param string $actualString
     * @param string $expectedPath
     */
    protected function assertXPathNotPresent($actualString, $expectedPath)
    {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_XPathConstraint($expectedPath);
        $not = new PHPUnit_Framework_Constraint_Not($constraint);
        self::assertThat($actualString, $not);
    }

    /**
     * @param integer $expectedErrorCode
     * @param string $expectedErrorMessage
     */
    protected function assertError($expectedErrorCode, $expectedErrorMessage = '')
    {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_ErrorConstraint(
            $expectedErrorCode,
            $expectedErrorMessage
        );
        self::assertThat($this->controller, $constraint);
    }
}
