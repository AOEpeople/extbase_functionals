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
namespace Aoe\ExtbaseFunctionals\Test;

use Aoe\ExtbaseFunctionals\Configuration\FunctionalTestConfigurationInterface;
use Aoe\ExtbaseFunctionals\Constraint\ErrorConstraint;
use Aoe\ExtbaseFunctionals\Constraint\ForwardConstraint;
use Aoe\ExtbaseFunctionals\Constraint\NoErrorConstraint;
use Aoe\ExtbaseFunctionals\Constraint\RedirectConstraint;
use Aoe\ExtbaseFunctionals\Constraint\XPathConstraint;
use DateTime;
use PHPUnit_Framework_Constraint_Not;
use ReflectionClass;
use ReflectionProperty;
use Aoe\ExtbaseFunctionals\Test\BaseStubTest;

/**
 * Base class for controller tests.
 *
 * @package ExtbaseFunctionals
 * @subpackage Test
 */
abstract class BaseControllerTest
    extends BaseStubTest
{
    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected $controller;

    /**
     * @var FunctionalTestConfigurationInterface
     */
    protected $configuration;

    /**
     * @return string
     */
    abstract protected function initializeController();

    /**
     * @return FunctionalTestConfigurationInterface
     */
    abstract protected function getFunctionalTestConfiguration();

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService
     */
    protected $mvcPropertyMappingConfigurationService;

    /**
     * set up controller
     */
    public function setUp()
    {
        parent::setUp();
        $this->initializeFunctionalTestConfiguration();
        $this->initializeObjectManager();
        $this->initializeExtbase();
        $this->registerStubs();
        $this->initializeController();
    }

    /**
     * Initializes the object manager implementation
     */
    protected function initializeFunctionalTestConfiguration()
    {
        $this->configuration = $this->getFunctionalTestConfiguration();
    }

    /**
     * Initialize/Bootstrap the extbase framework
     */
    protected function initializeExtbase()
    {
        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->initialize(
            array(
                'extensionName' => $this->configuration->getExtensionName(),
                'pluginName' => $this->configuration->getPluginName(),
                'settings.' => $this->configuration->getPluginSettings()
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
    protected function emulateSettings(array $settings)
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
        $request->setControllerVendorName($this->configuration->getVendorName());
        $request->setControllerActionName($action);
        $request->setControllerExtensionName($this->configuration->getExtensionName());
        $request->setControllerName($controller);
        $request->setMethod($method);
        $request->setHmacVerified(true);
        $prefix = 'tx_' . strtolower($this->configuration->getExtensionName()) . '_' . $this->configuration->getPluginName();
        $trustedProperties = $this->mvcPropertyMappingConfigurationService->generateTrustedPropertiesToken(
            $this->convertRequestArgumentsToFieldNames($requestArguments, $prefix),
            $prefix
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
        $constraint = new ForwardConstraint(
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
     * @param integer $expectedPageUid
     * @param integer $expectedStatusCode
     */
    protected function assertRedirect(
        $expectedAction,
        $expectedController = null,
        array $expectedParameters = array(),
        $expectedPageUid = null,
        $expectedStatusCode = 303
    ) {
        $constraint = new RedirectConstraint(
            $expectedAction,
            $expectedController,
            $expectedParameters,
            $expectedPageUid,
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
        $constraint = new XPathConstraint($expectedPath);
        self::assertThat($actualString, $constraint);
    }

    /**
     * @param string $actualString
     * @param string $expectedPath
     */
    protected function assertXPathNotPresent($actualString, $expectedPath)
    {
        $constraint = new XPathConstraint($expectedPath);
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
        $constraint = new ErrorConstraint(
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
        $constraint = new NoErrorConstraint();
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
                if (false === stripos(get_class($value), 'Tx_' . $this->configuration->getExtensionName() . '_') &&
                    false === stripos(
                        get_class($value),
                        $this->configuration->getVendorName() . '\\' . $this->configuration->getExtensionName()
                    )
                ) {
                    continue;
                } //@FIXME: No Dependencies to checkout!

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
     * @FIXME: no dependencies to checkout!
     */
    private function convertRequestArgumentsToFieldNames(array $requestArguments, $prefix = '')
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
