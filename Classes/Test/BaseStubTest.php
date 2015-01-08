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
 * @subpackage Test
 */
abstract class Tx_ExtbaseFunctionals_Test_BaseStubTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase
{
    /**
     * @var array
     */
    private $stubs;

    /**
     * @return void
     */
    abstract protected function registerStubs();

    /**
     * @param string $name
     * @return void
     */
    protected function registerStub($name)
    {
        /** @var Tx_ExtbaseFunctionals_Stub_StubInterface $stub */
        $stub = $this->objectManager->get($name);
        $stub->tearDown();
        $stub->setUp($this);

        /** @var Tx_Extbase_Object_Container_Container $container */
        $container = $this->objectManager->get('Tx_Extbase_Object_Container_Container');
        $container->registerImplementation($stub->getOriginalClassName(), get_class($stub));

        $this->stubs[$name] = $stub;
    }

    /**
     * @param string $name
     * @return Tx_ExtbaseFunctionals_Stub_StubInterface
     * @throws RuntimeException
     */
    protected function getStub($name)
    {
        if (false === $this->stubs[$name] instanceof Tx_ExtbaseFunctionals_Stub_StubInterface) {
            throw new RuntimeException('A Stub with the name "' . $name . '" is not registered', 1409569219);
        }
        $this->stubs[$name]->tearDown();
        $this->stubs[$name]->setUp($this);
        return $this->stubs[$name];
    }
}
