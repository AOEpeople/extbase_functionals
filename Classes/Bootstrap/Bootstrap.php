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
 * @package ExtbaseFunctionals
 * @subpackage Bootstrap
 */
class Bootstrap
{
    /**
     * @var Bootstrap
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
        self::$functionalTestCaseBootstrapUtilityReflection = new \ReflectionClass(self::$functionalTestCaseBootstrapUtility);
    }

    /**
     * @return Bootstrap
     */
    public function defineSitePath()
    {
        define('PATH_site', $this->getWebRoot());
        define('ORIGINAL_ROOT', PATH_site);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function setUpInstancePath()
    {
        $instancePath = self::$functionalTestCaseBootstrapUtilityReflection->getProperty('instancePath');
        $instancePath->setAccessible(true);
        $instancePath->setValue(self::$functionalTestCaseBootstrapUtility, PATH_site);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function setUpLocalConfiguration()
    {
        $setUpLocalConfiguration = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('setUpLocalConfiguration');
        $setUpLocalConfiguration->setAccessible(true);
        $setUpLocalConfiguration->invoke(self::$functionalTestCaseBootstrapUtility, array(
            'SYS' => array(
                'encryptionKey' => 'fc86c6ab5c35074c5c72d2a851143eca',
                'trustedHostsPattern' => '.*',
            )
        ));

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function setUpPackageStates()
    {
        $extensions = array_diff(scandir($this->getWebRoot() . 'typo3conf/ext/'), array('..', '.'));
        $setUpLocalConfiguration = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('setUpPackageStates');
        $setUpLocalConfiguration->setAccessible(true);
        $setUpLocalConfiguration->invoke(
            self::$functionalTestCaseBootstrapUtility,
            array('scheduler', 'fluid', 'rtehtmlarea'),
            $extensions
        );

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function setUpBasicTypo3Bootstrap()
    {
        $setUpLocalConfiguration = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('setUpBasicTypo3Bootstrap');
        $setUpLocalConfiguration->setAccessible(true);
        $setUpLocalConfiguration->invoke(self::$functionalTestCaseBootstrapUtility);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function setUpTestDatabase()
    {
        $method = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('setUpTestDatabase');
        $method->setAccessible(true);
        $method->invoke(self::$functionalTestCaseBootstrapUtility);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function loadExtensionTables()
    {
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadExtensionTables(true);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function initializeTestDatabase()
    {
        $method = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('initializeTestDatabase');
        $method->setAccessible(true);
        $method->invoke(self::$functionalTestCaseBootstrapUtility);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function createDatabaseStructure()
    {
        $method = self::$functionalTestCaseBootstrapUtilityReflection->getMethod('createDatabaseStructure');
        $method->setAccessible(true);
        $method->invoke(self::$functionalTestCaseBootstrapUtility);

        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function registerShutdownTestDatabase()
    {

        register_shutdown_function(array($this, 'tearDownTestDatabase'));
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
     * @return string
     */
    protected function getWebRoot()
    {
        if (getenv('TYPO3_PATH_WEB')) {
            $webRoot = getenv('TYPO3_PATH_WEB') . '/';
        } else {
            $webRoot = getcwd() . '/';
        }

        return strtr($webRoot, '\\', '/');
    }
}
