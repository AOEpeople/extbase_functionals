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
namespace Aoe\ExtbaseFunctionals;

use TYPO3\CMS\Core\Core\Bootstrap;

/**
 * @package ExtbaseFunctionals
 */
class UnitTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors()
            ->defineSitePath()
            ->setTypo3Context()
            ->createNecessaryDirectoriesInDocumentRoot()
            ->includeAndStartCoreBootstrap()
            ->initializeConfiguration()
            ->finishCoreBootstrap();
    }

    /**
     * Makes sure error messages during the tests get displayed no matter what is set in php.ini.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function enableDisplayErrors()
    {
        @ini_set('display_errors', 1);

        return $this;
    }

    /**
     * Defines the PATH_site and PATH_thisScript constant and sets $_SERVER['SCRIPT_NAME'].
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function defineSitePath()
    {
        /** @var string */
        define('PATH_site', $this->getWebRoot());
        /** @var string */
        define('PATH_thisScript', PATH_site . 'typo3/cli_dispatch.phpsh');
        $_SERVER['SCRIPT_NAME'] = PATH_thisScript;

        return $this;
    }

    /**
     * Returns the absolute path the TYPO3 document root.
     *
     * @return string the TYPO3 document root using Unix path separators
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

    /**
     * Defines TYPO3_MODE, TYPO3_cliMode and sets the environment variable TYPO3_CONTEXT.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function setTypo3Context()
    {
        /** @var string */
        define('TYPO3_MODE', 'BE');
        /** @var string */
        define('TYPO3_cliMode', true);
        putenv('TYPO3_CONTEXT=Testing');

        return $this;
    }

    /**
     * Creates the following directories in the TYPO3 document root:
     * - typo3conf
     * - typo3conf/ext
     * - typo3temp
     * - uploads
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $this->createDirectory(PATH_site . 'uploads');
        $this->createDirectory(PATH_site . 'typo3temp');
        $this->createDirectory(PATH_site . 'typo3conf/ext');

        return $this;
    }

    /**
     * Creates the directory $directory (recursively if required).
     *
     * If $directory already exists, this method is a no-op.
     *
     * @param string $directory absolute path of the directory to be created
     * @return void
     * @throws \RuntimeException
     */
    protected function createDirectory($directory)
    {
        if (is_dir($directory)) {
            return;
        }
        @mkdir($directory, 0777, true);
        clearstatcache();
        if (!is_dir($directory)) {
            throw new \RuntimeException('Directory "' . $directory . '" could not be created', 1423043755);
        }
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = PATH_site . '/typo3/vendor/autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            die('ClassLoader can\'t be loaded. Please check your path or set an environment variable \'TYPO3_PATH_WEB\' to your root path.');
        }
        $classLoader = require $classLoaderFilepath;

        Bootstrap::getInstance()
            ->initializeClassLoader($classLoader)
            ->baseSetup();

        return $this;
    }

    /**
     * Provides the default configuration in $GLOBALS['TYPO3_CONF_VARS'].
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function initializeConfiguration()
    {
        $configurationManager = new \TYPO3\CMS\Core\Configuration\ConfigurationManager();
        $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

        // avoid failing tests that rely on HTTP_HOST retrieval
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';

        return $this;
    }

    /**
     * Finishes the last steps of the Core Bootstrap.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function finishCoreBootstrap()
    {
        Bootstrap::getInstance()
            ->disableCoreCache()
            ->initializeCachingFramework()
            ->initializePackageManagement(\TYPO3\CMS\Core\Package\UnitTestPackageManager::class)
            ->ensureClassLoadingInformationExists();

        return $this;
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new UnitTestsBootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
