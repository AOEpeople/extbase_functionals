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
namespace Aoe\ExtbaseFunctionals\Stub;

use Aoe\ExtbaseFunctionals\DataBuilder\AbstractBuilder;
use PHPUnit_Framework_TestCase;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @package ExtbaseFunctionals
 * @subpackage Stub
 */
interface StubInterface extends SingletonInterface
{
    /**
     * @param PHPUnit_Framework_TestCase $testCase
     * @return void
     */
    public function setUp(PHPUnit_Framework_TestCase $testCase);

    /**
     * @return void
     */
    public function tearDown();

    /**
     * @return AbstractBuilder
     */
    public function getBuilder();

    /**
     * @return string
     */
    public function getOriginalClassName();
}
