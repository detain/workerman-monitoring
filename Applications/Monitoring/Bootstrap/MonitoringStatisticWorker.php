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
namespace Bootstrap;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Statistics\Config;

/**
 * 
* @author walkor <walkor@workerman.net>
 */
class StatisticWorker extends Worker
{
    /**
	 *  Maximum log buffer, write more than this value to write the disk
     * @var integer
     */
    const MAX_LOG_BUFFER_SIZE = 1024000;
    
    /**
	 * How often do you write data to disk?
     * @var integer
     */
    const WRITE_PERIOD_LENGTH = 60;
    
    /**
	 * How often do you clean up old disk data?
     * @var integer
     */
    const CLEAR_PERIOD_LENGTH = 86400;
    
    /**
	 * How long does the data expire?
     * @var integer
     */
    const EXPIRED_TIME = 1296000;
    
    /**
	 * Statistical data
     * ip=>modid=>interface=>['code'=>[xx=>count,xx=>count],'suc_cost_time'=>xx,'fail_cost_time'=>xx, 'suc_count'=>xx, 'fail_count'=>xx]
     * @var array
     */
    protected $statisticData = array();
    
    /**
	 * Log buffer
     * @var string
     */
    protected $logBuffer = '';
    
    /**
	 * a table of statistics
     * @var string
     */
    protected $statisticDir = 'statistic/statistic/';
    
    /**
	 * Directory for storing statistics logs
     * @var string
     */
    protected $logDir = 'statistic/log/';
    
    /**
	 * Provide a socket for statistical queries
     * @var resource
     */
    protected $providerSocket = null;
    
    public function __construct($socket_name)
    {
        parent::__construct($socket_name);
        $this->onWorkerStart = array($this, 'onStart');
        $this->onMessage = array($this, 'onMessage');
        $this->onWorkerStop = array($this, 'onStop');
    }
    
    /**
	 * Business processing
     * @see Man\Core.SocketWorker::dealProcess()
     */
    public function onMessage($connection, $data)
    {
		// decoding
        $module = $data['module'];
        $interface = $data['interface'];
        $cost_time = $data['cost_time'];
        $success = $data['success'];
        $time = $data['time'];
        $code = $data['code'];
        $msg = str_replace("\n", "<br>", $data['msg']);
        $ip = $connection->getRemoteIp();
        
		// Module interface statistics
        $this->collectStatistics($module, $interface, $cost_time, $success, $ip, $code, $msg);
		// global stats
        $this->collectStatistics('WorkerMan', 'Statistics', $cost_time, $success, $ip, $code, $msg);
        
		// failure log
        if(!$success)
        {
            $this->logBuffer .= date('Y-m-d H:i:s',$time)."\t$ip\t$module::$interface\tcode:$code\tmsg:$msg\n";
            if(strlen($this->logBuffer) >= self::MAX_LOG_BUFFER_SIZE)
            {
                $this->writeLogToDisk();
            }
        }
    }
    
    /**
	 * Collecting statistics
     * @param string $module
     * @param string $interface
     * @param float $cost_time
     * @param int $success
     * @param string $ip
     * @param int $code
     * @param string $msg
     * @return void
     */
   protected function collectStatistics($module, $interface , $cost_time, $success, $ip, $code, $msg)
   {
	   // Statistical related information
       if(!isset($this->statisticData[$ip]))
       {
           $this->statisticData[$ip] = array();
       }
       if(!isset($this->statisticData[$ip][$module]))
       {
           $this->statisticData[$ip][$module] = array();
       }
       if(!isset($this->statisticData[$ip][$module][$interface]))
       {
           $this->statisticData[$ip][$module][$interface] = array('code'=>array(), 'suc_cost_time'=>0, 'fail_cost_time'=>0, 'suc_count'=>0, 'fail_count'=>0);
       }
       if(!isset($this->statisticData[$ip][$module][$interface]['code'][$code]))
       {
           $this->statisticData[$ip][$module][$interface]['code'][$code] = 0;
       }
       $this->statisticData[$ip][$module][$interface]['code'][$code]++;
       if($success)
       {
           $this->statisticData[$ip][$module][$interface]['suc_cost_time'] += $cost_time;
           $this->statisticData[$ip][$module][$interface]['suc_count'] ++;
       }
       else
       {
           $this->statisticData[$ip][$module][$interface]['fail_cost_time'] += $cost_time;
           $this->statisticData[$ip][$module][$interface]['fail_count'] ++;
       }
   }
    
   /**
	* Write statistics to disk
    * @return void
    */
   public function writeStatisticsToDisk()
   {
       $time = time();
	   // Loop to write statistics for each ip to disk
       foreach($this->statisticData as $ip => $mod_if_data)
       {
           foreach($mod_if_data as $module=>$items)
           {
			   // Create a folder if it does not exist
               $file_dir = Config::$dataPath . $this->statisticDir.$module;
               if(!is_dir($file_dir))
               {
                   umask(0);
                   mkdir($file_dir, 0777, true);
               }
			   // Write to disk in sequence
               foreach($items as $interface=>$data)
               {
                   file_put_contents($file_dir. "/{$interface}.".date('Y-m-d'), "$ip\t$time\t{$data['suc_count']}\t{$data['suc_cost_time']}\t{$data['fail_count']}\t{$data['fail_cost_time']}\t".json_encode($data['code'])."\n", FILE_APPEND | LOCK_EX);
               }
           }
       }
	   // Clear statistics
       $this->statisticData = array();
   }
    
    /**
	 * Write Log data to disk
     * @return void
     */    
    public function writeLogToDisk()
    {
		// Return without statistics
        if(empty($this->logBuffer))
        {
            return;
        }
		// Write to disk
        file_put_contents(Config::$dataPath . $this->logDir . date('Y-m-d'), $this->logBuffer, FILE_APPEND | LOCK_EX);
        $this->logBuffer = '';
    }
    
    /**
	 * initialization
	 * Statistical catalog check
	 * initialization mission
     * @see Man\Core.SocketWorker::onStart()
     */
    protected function onStart()
    {
		// initialization bulletin
        umask(0);
        $statistic_dir = Config::$dataPath . $this->statisticDir;
        if(!is_dir($statistic_dir))
        {
            mkdir($statistic_dir, 0777, true);
        }
        $log_dir = Config::$dataPath . $this->logDir;
        if(!is_dir($log_dir))
        {
            mkdir($log_dir, 0777, true);
        }
		// Timed Save Statistics
        Timer::add(self::WRITE_PERIOD_LENGTH, array($this, 'writeStatisticsToDisk'));
        Timer::add(self::WRITE_PERIOD_LENGTH, array($this, 'writeLogToDisk'));
		// Regularly clean unused statistics
        Timer::add(self::CLEAR_PERIOD_LENGTH, array($this, 'clearDisk'), array(Config::$dataPath . $this->statisticDir, self::EXPIRED_TIME));
        Timer::add(self::CLEAR_PERIOD_LENGTH, array($this, 'clearDisk'), array(Config::$dataPath . $this->logDir, self::EXPIRED_TIME));
        
    }
    
    /**
	 * Need to write data to disk when the process stops
     * @see Man\Core.SocketWorker::onStop()
     */
    protected function onStop()
    {
        $this->writeLogToDisk();
        $this->writeStatisticsToDisk();
    }
    
    /**
	 * Clear disk data
     * @param string $file
     * @param int $exp_time
     */
    public function clearDisk($file = null, $exp_time = 86400)
    {
        $time_now = time();
        if(is_file($file))
        {
            $mtime = filemtime($file);
            if(!$mtime)
            {
                $this->notice("filemtime $file fail");
                return;
            }
            if($time_now - $mtime > $exp_time)
            {
                unlink($file);
            }
            return;
        }
        foreach (glob($file."/*") as $file_name) 
        {
            $this->clearDisk($file_name, $exp_time);
        }
    }
} 
