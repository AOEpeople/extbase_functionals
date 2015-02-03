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
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected $controller;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

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
     * overwrite this method to mock settings for controller
     *
     * @return array
     */
    protected function getPluginSettings()
    {
        return array();
    }

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService
     */
    protected $mvcPropertyMappingConfigurationService;

    /**
     * set up controller
     */
    public function setUp()
    {
        $this->initializeObjectManager();
        $this->initializeExtbase();
        $this->registerStubs();
        $this->initializeController();
    }

    /**
     * Initializes the object manager implementation
     */
    public function initializeObjectManager()
    {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Extbase\\Object\\ObjectManager'
        );
    }

    /**
     * Initialize/Bootstrap the extbase framework
     */
    public function initializeExtbase()
    {
        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->initialize(
            array(
                'extensionName' => $this->getExtensionName(),
                'pluginName' => $this->getPluginName(),
                'settings.' => $this->getPluginSettings()
            )
        );
        $this->mvcPropertyMappingConfigurationService = $this->objectManager->get(
            'TYPO3\\CMS\\Extbase\\Mvc\\Controller\\MvcPropertyMappingConfigurationService'
        );
    }

    /**
     * @param array $settings
     * @return void
     */
    public function emulateSettings(array $settings)
    {
        /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->objectManager->get(
            'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface'
        );
        $configurationSettings = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
        $mergedSettings = array_merge($configurationSettings, $settings);
        $configuration = $this->getMockBuilder('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface')
            ->setMethods(
                array(
                    'setContentObject',
                    'getContentObject',
                    'getConfiguration',
                    'setConfiguration',
                    'isFeatureEnabled',
                )
            )
            ->getMock();
        $configuration->expects($this->any())->method('getConfiguration')->will($this->returnValue($mergedSettings));
        $this->controller->injectConfigurationManager($configuration);
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array $arguments
     * @param string $method
     * @return \TYPO3\CMS\Extbase\Mvc\Response
     */
    protected function processRequestWith($controller, $action, array $arguments = array(), $method = 'GET')
    {
        $requestArguments = $this->convertArgumentsToRequestArguments($arguments);

        $response = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response');
        $request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request');
        $request->setControllerActionName($action);
        $request->setControllerExtensionName($this->getExtensionName());
        $request->setControllerName($controller);
        $request->setMethod($method);
        $request->setHmacVerified(true);

        $trustedProperties = $this->mvcPropertyMappingConfigurationService->generateTrustedPropertiesToken(
            $this->convertRequestArgumentsToFieldNames($requestArguments),
            'tx_checkout_checkout'
        );
        $request->setArgument('__trustedProperties', $trustedProperties);
        foreach ($requestArguments as $key => $value) {
            $request->setArgument($key, $value);
        }

        try {
            $this->controller->processRequest($request, $response);
        } catch (\TYPO3\CMS\Extbase\Mvc\Exception\StopActionException $ignoredException) {
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
     * @param string $propertyPath
     */
    protected function assertError($expectedErrorCode, $expectedErrorMessage = '', $propertyPath = '')
    {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_ErrorConstraint(
            $expectedErrorCode,
            $expectedErrorMessage,
            $propertyPath
        );
        self::assertThat($this->controller, $constraint);
    }

    /**
     */
    protected function assertNoError()
    {
        $constraint = new Tx_ExtbaseFunctionals_Constraint_NoErrorConstraint();
        self::assertThat($this->controller, $constraint);
    }

    /**
     * Convert arguments (array, which can contain e.g. Domain-Model-Objects) to a flat array (as it would be used in 'normal' Web-Requests)
     *
     * @param array $arguments
     * @return array
     */
    private function convertArgumentsToRequestArguments(array $arguments)
    {
        $requestArguments = array();
        foreach ($arguments as $key => $value) {
            if (is_object($value)) {
                if (false === stripos(get_class($value), 'Tx_Checkout_')) {
                    continue;
                }

                $objectArguments = array();
                $reflection = new ReflectionClass($value);
                foreach ($reflection->getProperties() as $property) {
                    /* @var $property ReflectionProperty */
                    $property->setAccessible(true);
                    $propertyValue = $property->getValue($value);

                    if (is_object($propertyValue)) {
                        if ($propertyValue instanceof DateTime) {
                            $propertyValue = date('d.m.Y', $propertyValue->getTimestamp());
                        } else {
                            //@TODO: if the property is another object, we must generate new fields for that
                        }
                    }

                    $objectArguments[$property->getName()] = $propertyValue;
                }

                $requestArguments[$key] = $this->convertArgumentsToRequestArguments($objectArguments);
            } else {
                $requestArguments[$key] = $value;
            }
        }

        return $requestArguments;
    }

    /**
     * @param array $requestArguments
     * @param string $prefix
     * @return array
     */
    private function convertRequestArgumentsToFieldNames(array $requestArguments, $prefix = 'tx_checkout_checkout')
    {
        $fieldNames = array();
        $format = '[%s]';

        foreach ($requestArguments as $key => $requestArgument) {
            $fieldName = $prefix . sprintf($format, $key);

            if (is_array($requestArgument)) {
                $fieldNames = array_merge(
                    $fieldNames,
                    $this->convertRequestArgumentsToFieldNames($requestArgument, $fieldName)
                );
            } else {
                $fieldNames[] = $fieldName;
            }
        }

        return $fieldNames;
    }
}
