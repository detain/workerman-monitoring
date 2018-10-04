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
use Statistics\Config;
/**
 * 
* @author walkor <walkor@workerman.net>
 */
class StatisticProvider extends Worker
{
    /**
	 *  maximumLogbuffer，Greater than this OneValue is written to disk
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
	 * Directory for storing statistics Log
     * @var string
     */
    protected $logDir = 'statistic/log/';
    
    /**
	 * Udp socket for receiving broadcasts
     * @var resource
     */
    protected $broadcastSocket = null;
    
    /**
     * construt
     * @param string $socket_name
     */
    public function __construct($socket_name)
    {
        parent::__construct($socket_name);
        $this->onMessage = array($this, 'onMessage');
    }
    
    /**
	 * Processing request statistics
     * @param string $recv_buffer
     */
    public function onMessage($connection, $recv_buffer)
    {
        $req_data = json_decode(trim($recv_buffer), true);
        $module = $req_data['module'];
        $interface = $req_data['interface'];
        $cmd = $req_data['cmd'];
        $start_time = isset($req_data['start_time']) ? $req_data['start_time'] : '';
        $end_time = isset($req_data['end_time']) ? $req_data['end_time'] : '';
        $date = isset($req_data['date']) ? $req_data['date'] : '';
        $code = isset($req_data['code']) ? $req_data['code'] : '';
        $msg = isset($req_data['msg']) ? $req_data['msg'] : '';
        $offset = isset($req_data['offset']) ? $req_data['offset'] : '';
        $count = isset($req_data['count']) ? $req_data['count'] : 10;
        switch($cmd)
        {
            case 'get_statistic':
                $buffer = json_encode(array('modules'=>$this->getModules($module), 'statistic' => $this->getStatistic($date, $module, $interface)))."\n";
                $connection->send($buffer);
                break;
            case 'get_log':
                $buffer = json_encode($this->getStasticLog($module, $interface , $start_time , $end_time, $code, $msg, $offset, $count))."\n";
                $connection->send($buffer);
                break;
            default :
                $connection->send('pack err');
        }
    }
    
    /**
	 * Acquisition module
     * @return array
     */
    public function getModules($current_module = '')
    {
        $st_dir = Config::$dataPath . $this->statisticDir;
        $modules_name_array = array();
        foreach(glob($st_dir."/*", GLOB_ONLYDIR) as $module_file)
        {
            $tmp = explode("/", $module_file);
            $module = end($tmp);
            $modules_name_array[$module] = array();
            if($current_module == $module)
            {
                $st_dir = $st_dir.$current_module.'/';
                $all_interface = array();
                foreach(glob($st_dir."*") as $file)
                {
                    if(is_dir($file))
                    {
                        continue;
                    }
                    list($interface, $date) = explode(".", basename($file));
                    $all_interface[$interface] = $interface;
                }
                $modules_name_array[$module] = $all_interface;
            }
        }
        return $modules_name_array;
    }
    
    /**
	 * Get statistics
     * @param string $module
     * @param string $interface
     * @param int $date
     * @return bool/string
     */
    protected function getStatistic($date, $module, $interface)
    {
        if(empty($module) || empty($interface))
        {
            return '';
        }
		// Log file
        $log_file = Config::$dataPath . $this->statisticDir."{$module}/{$interface}.{$date}";
        
        $handle = @fopen($log_file, 'r');
        if(!$handle)
        {
            return '';
        }
        
		// Preprocess statistics, one line every 5 minutes
        // [time=>[ip=>['suc_count'=>xx, 'suc_cost_time'=>xx, 'fail_count'=>xx, 'fail_cost_time'=>xx, 'code_map'=>[code=>count, ..], ..], ..]
        $statistics_data = array();
        while(!feof($handle))
        {
            $line = fgets($handle, 4096);
            if($line)
            {
                $explode = explode("\t", $line);
                if(count($explode) < 7)
                {
                    continue;
                }
                list($ip, $time, $suc_count, $suc_cost_time, $fail_count, $fail_cost_time, $code_map) = $explode;
                $time = ceil($time/300)*300;
                if(!isset($statistics_data[$time]))
                {
                    $statistics_data[$time] = array();
                }
                if(!isset($statistics_data[$time][$ip]))
                {
                    $statistics_data[$time][$ip] = array(
                            'suc_count'       =>0,
                            'suc_cost_time' =>0,
                            'fail_count'       =>0,
                            'fail_cost_time' =>0,
                            'code_map'      =>array(),
                     );
                }
                $statistics_data[$time][$ip]['suc_count'] += $suc_count;
                $statistics_data[$time][$ip]['suc_cost_time'] += round($suc_cost_time, 5);
                $statistics_data[$time][$ip]['fail_count'] += $fail_count;
                $statistics_data[$time][$ip]['fail_cost_time'] += round($fail_cost_time, 5);
                $code_map = json_decode(trim($code_map), true);
                if($code_map && is_array($code_map))
                {
                    foreach($code_map as $code=>$count)
                    {
                        if(!isset($statistics_data[$time][$ip]['code_map'][$code]))
                        {
                            $statistics_data[$time][$ip]['code_map'][$code] = 0;
                        }
                        $statistics_data[$time][$ip]['code_map'][$code] +=$count;
                    }
                }
            } // end if
        } // end while
        
        fclose($handle);
        ksort($statistics_data);
        
		// Organize data
        $statistics_str = '';
        foreach($statistics_data as $time => $items)
        {
            foreach($items as $ip => $item)
            {
                $statistics_str .= "$ip\t$time\t{$item['suc_count']}\t{$item['suc_cost_time']}\t{$item['fail_count']}\t{$item['fail_cost_time']}\t".json_encode($item['code_map'])."\n";
            }
        }
        return $statistics_str;
    }
    
    
    /**
	 * Get the specified Log
     *
     */
    protected function getStasticLog($module, $interface , $start_time = '', $end_time = '', $code = '', $msg = '', $offset='', $count=100)
    {
		// Log file
        $log_file = Config::$dataPath . $this->logDir. (empty($start_time) ? date('Y-m-d') : date('Y-m-d', $start_time));
        if(!is_readable($log_file))
        {
            return array('offset'=>0, 'data'=>'');
        }
		// Reading file
        $h = fopen($log_file, 'r');
    
		// If there is time, perform a binary search to speed up the query.
        if($start_time && $offset == 0 && ($file_size = filesize($log_file)) > 1024000)
        {
            $offset = $this->binarySearch(0, $file_size, $start_time-1, $h);
            $offset = $offset < 100000 ? 0 : $offset - 100000;
        }
    
		// Regular expression
        $pattern = "/^([\d: \-]+)\t\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\t";
    
        if($module && $module != 'WorkerMan')
        {
            $pattern .= $module."::";
        }
        else
        {
            $pattern .= ".*::";
        }
    
        if($interface && $module != 'WorkerMan')
        {
            $pattern .= $interface."\t";
        }
        else
        {
            $pattern .= ".*\t";
        }
    
        if($code !== '')
        {
            $pattern .= "code:$code\t";
        }
        else
        {
            $pattern .= "code:\d+\t";
        }
    
        if($msg)
        {
            $pattern .= "msg:$msg";
        }
         
        $pattern .= '/';
    
		// Specify offset position
        if($offset > 0)
        {
            fseek($h, (int)$offset-1);
        }
    
		// Find eligible data
        $now_count = 0;
        $log_buffer = '';
    
        while(1)
        {
            if(feof($h))
            {
                break;
            }
			// Read 1 line
            $line = fgets($h);
            if(preg_match($pattern, $line, $match))
            {
				// Determine if the time meets the requirements
                $time = strtotime($match[1]);
                if($start_time)
                {
                    if($time<$start_time)
                    {
                        continue;
                    }
                }
                if($end_time)
                {
                    if($time>$end_time)
                    {
                        break;
                    }
                }
				// Collect eligible logs
                $log_buffer .= $line;
                if(++$now_count >= $count)
                {
                    break;
                }
            }
        }
		// Record offset position
        $offset = ftell($h);
        return array('offset'=>$offset, 'data'=>$log_buffer);
    }
    /**
	 * Log binary search
     * @param int $start_point
     * @param int $end_point
     * @param int $time
     * @param fd $fd
     * @return int
     */
    protected function binarySearch($start_point, $end_point, $time, $fd)
    {
        if($end_point - $start_point < 65535)
        {
            return $start_point;
        }
        
		// Calculation midpoint
        $mid_point = (int)(($end_point+$start_point)/2);
    
		// Position the file pointer at the midpoint
        fseek($fd, $mid_point - 1);
    
		// Read the first line
        $line = fgets($fd);
        if(feof($fd) || false === $line)
        {
            return $start_point;
        }
    
		// The first line may not be complete, read another line
        $line = fgets($fd);
        if(feof($fd) || false === $line || trim($line) == '')
        {
            return $start_point;
        }
    
		// Determine whether it is out of bounds
        $current_point = ftell($fd);
        if($current_point>=$end_point)
        {
            return $start_point;
        }
    
		// Get time
        $tmp = explode("\t", $line);
        $tmp_time = strtotime($tmp[0]);
    
		// Judge time, return pointer position
        if($tmp_time > $time)
        {
            return $this->binarySearch($start_point, $current_point, $time, $fd);
        }
        elseif($tmp_time < $time)
        {
            return $this->binarySearch($current_point, $end_point, $time, $fd);
        }
        else
        {
            return $current_point;
        }
    }
} 
