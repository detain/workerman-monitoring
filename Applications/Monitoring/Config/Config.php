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
namespace Statistics;
class Config
{
	// Data source Port, will send udp broadcast to this port to obtain ip, and then get statistics from this port with tcp protocol
    public static $ProviderPort = 55855;
    
	// The administrator user name and the username and password are all empty strings.
    public static $adminName = '';
    
	// Administrator password, user name and password are all empty strings, no need to verify
    public static $adminPassword = '';
    
    public static $dataPath = '';
}

Config::$dataPath = __DIR__ . '/../data/';