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

use Aoe\ExtbaseFunctionals\Stub\StubInterface;
use RuntimeException;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;

/**
 * @package ExtbaseFunctionals
 * @subpackage Test
 */
abstract class BaseStubTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $coreExtensionsToLoad = array(
        'core',
        'backend',
        'frontend',
        'lang',
        'extbase',
        'install',
    );

    /**
     * @var array
     */
    protected $testExtensionsToLoad = array('typo3conf/ext/extbase_functionals');

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    private $stubs;

    /**
     * @return void
     */
    abstract protected function registerStubs();

    /**
     * Initializes the object manager implementation
     */
    protected function initializeObjectManager()
    {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Extbase\\Object\\ObjectManager'
        );
    }

    /**
     * @param string $name
     * @return void
     */
    protected function registerStub($name)
    {
        /** @var StubInterface $stub */
        $stub = $this->objectManager->get($name);
        $stub::tearDown();
        $stub::setUp($this);

        /** @var \TYPO3\CMS\Extbase\Object\Container\Container $container */
        $container = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Object\\Container\\Container');
        $container->registerImplementation($stub::getOriginalClassName(), get_class($stub));

        $this->stubs[$name] = $stub;
    }

    /**
     * @param string $name
     * @return StubInterface
     * @throws RuntimeException
     */
    protected function getStub($name)
    {
        if (false === $this->stubs[$name] instanceof StubInterface) {
            throw new RuntimeException('A Stub with the name "' . $name . '" is not registered', 1409569219);
        }
        /** @var StubInterface $stub */
        $stub = $this->stubs[$name];
        $stub::tearDown();
        $stub::setUp($this);
        return $stub;
    }
}
