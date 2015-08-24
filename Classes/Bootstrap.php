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

use Aoe\ExtbaseFunctionals\Bootstrap\Bootstrap;

require_once dirname(__FILE__) . '/Bootstrap/Bootstrap.php';
require_once dirname(__FILE__) . '/../../../../typo3/sysext/core/Tests/Exception.php';
require_once dirname(__FILE__) . '/../../../../typo3/sysext/core/Tests/FunctionalTestCaseBootstrapUtility.php';

$bootstrap = new Bootstrap();
$bootstrap->setUp();
