<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
require_once __DIR__ . '/loader.php';

use Bootstrap\StatisticProvider;
use Bootstrap\StatisticWorker;
use \Workerman\Worker;
use \Workerman\WebServer;

// StatisticWorker
$statistic_worker = new StatisticWorker("Statistic://0.0.0.0:55655");
$statistic_worker->transport = 'udp';
$statistic_worker->name = 'StatisticWorker';

// Run the runAll method if it is not started in the root directory
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
