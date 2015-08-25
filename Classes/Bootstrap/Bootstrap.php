<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
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
namespace Aoe\ExtbaseFunctionals\Bootstrap;

use TYPO3\CMS\Core\Tests\FunctionalTestCaseBootstrapUtility;

/**
 * Covers the functionality from FunctionalTestCaseBootstrapUtility which is provided by TYPO3
 * but allows us to create only one test instance for each extension. TYPO3 by default, create a
 * test instance and database for each test case which causes a lot of overhead.
 *
 * @package ExtbaseFunctionals
 * @subpackage Bootstrap
 */
class Bootstrap
{
    /**
     * @var FunctionalTestCaseBootstrapUtility
     */
    private static $functionalTestCaseBootstrapUtility;

    /**
     * @var \ReflectionClass
     */
    private static $functionalTestCaseBootstrapUtilityReflection;

    /**
     * initialize FunctionalTestCaseBootstrapUtility for internal usage
     */
    public function __construct()
    {
        self::$functionalTestCaseBootstrapUtility = new FunctionalTestCaseBootstrapUtility();
        self::$functionalTestCaseBootstrapUtilityReflection = new \ReflectionClass(
            self::$functionalTestCaseBootstrapUtility
        );
    }

    /**
     * @return void
     */
    public static function tearDownTestDatabase()
    {
        $method = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('tearDownTestDatabase');
        $method->setAccessible(true);
        $method->invoke(self::$functionalTestCaseBootstrapUtility);
    }

    /**
     * @return void
     */
    public static function tearDownTestInstance()
    {
        $method = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('removeOldInstanceIfExists');
        $method->setAccessible(true);
        $method->invoke(self::$functionalTestCaseBootstrapUtility);
    }

    /**
     * set up
     * @return void
     */
    public function setUp()
    {
        self::$functionalTestCaseBootstrapUtility->setUp(
            uniqid('extbase_functionals'),
            $this->getAdditionalCoreExtensions(),
            $this->getExtensions(),
            $this->getAdditionalPaths(),
            array(
                'SYS' => array(
                    'encryptionKey' => 'fc86c6ab5c35074c5c72d2a851143eca',
                    'trustedHostsPattern' => '.*',
                )
            ),
            array()
        );
        $this->registerShutdownTestDatabase();
        $this->registerShutdownTestInstance();
    }

    /**
     * @return array
     */
    private function getAdditionalCoreExtensions()
    {
        $additionalCoreExtensions = array();
        if (defined('EXTBASE_FUNCTIONALS_ADDITIONAL_CORE_EXTENSION')) {
            $additionalCoreExtensions = explode(',', constant('EXTBASE_FUNCTIONALS_ADDITIONAL_CORE_EXTENSION'));
        }
        return $additionalCoreExtensions;
    }

    /**
     * @return array
     */
    private function getAdditionalPaths()
    {
        $additionalPaths = array();
        if (defined('EXTBASE_FUNCTIONALS_ADDITIONAL_PATHS')) {
            $additionalPaths = explode(',', constant('EXTBASE_FUNCTIONALS_ADDITIONAL_PATHS'));
        }
        $parsed = array();
        foreach ($additionalPaths as $full) {
            $pieces = explode(':', $full);
            $parsed[$pieces[0]] = $pieces[1];
        }
        return $parsed;
    }

    /**
     * @return array
     */
    private function getExtensions()
    {
        $extensionDir = ORIGINAL_ROOT . '/typo3conf/ext/';
        $extensions = array_diff(scandir($extensionDir), array('..', '.'));
        array_walk($extensions, function (&$item) {
            $item = 'typo3conf/ext/' . $item;
        });
        return $extensions;
    }

    /**
     * @return Bootstrap
     */
    private function registerShutdownTestDatabase()
    {

        register_shutdown_function(array($this, 'tearDownTestDatabase'));
    }

    /**
     * @return Bootstrap
     */
    private function registerShutdownTestInstance()
    {

        register_shutdown_function(array($this, 'tearDownTestInstance'));
    }
}
