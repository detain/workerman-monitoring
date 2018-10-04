<?php
namespace Protocols;
/**
 *
 * struct statisticPortocol
 * {
 *     unsigned long ip;
 *     unsigned char module_name_len;
 *     unsigned char interface_name_len;
 *     float cost_time;
 *     unsigned char success;
 *     int code;
 *     unsigned short msg_len;
 *     unsigned int time;
 *     char[module_name_len] module_name;
 *     char[interface_name_len] interface_name;
 *     char[msg_len] msg;
 * }
 *
 */
class MonitorStatistic
{
	/**
	 * Head length
	 * @var integer
	 */
	const PACKAGE_FIXED_LENGTH = 21;

	/**
	 * Udp package maximum length
	 * @var integer
	 */
	const MAX_UDP_PACKGE_SIZE  = 65507;

	/**
	 * The maximum value that the char type can hold.
	 * @var integer
	 */
	const MAX_CHAR_VALUE = 255;

	/**
	 *  The maximum value that can be saved by usigned short
	 * @var integer
	 */
	const MAX_UNSIGNED_SHORT_VALUE = 65535;

	/**
	 * input
	 * @param string $recv_buffer
	 */
	public static function input($recv_buffer)
	{
		if(strlen($recv_buffer) < self::PACKAGE_FIXED_LENGTH)
		{
			return 0;
		}
		$data = unpack("Lip/Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime", $recv_buffer);
		return $data['module_name_len'] + $data['interface_name_len'] + $data['msg_len'] + self::PACKAGE_FIXED_LENGTH;
	}

	/**
	 * coding
	 * @param string $module
	 * @param string $interface
	 * @param float $cost_time
	 * @param int $success
	 * @param int $code
	 * @param string $msg
	 * @return string
	 */
	public static function encode($data)
	{
		$ip = $data['ip'];
		$module = $data['module'];
		$interface = $data['interface'];
		$cost_time = $data['cost_time'];
		$success = $data['success'];
		$code = isset($data['code']) ? $data['code'] : 0;
		$msg = isset($data['msg']) ? $data['msg'] : '';

		// Prevent module names from being too long
		if(strlen($module) > self::MAX_CHAR_VALUE)
		{
			$module = substr($module, 0, self::MAX_CHAR_VALUE);
		}

		// Prevent interface names from being too long
		if(strlen($interface) > self::MAX_CHAR_VALUE)
		{
			$interface = substr($interface, 0, self::MAX_CHAR_VALUE);
		}

		// Prevent msg from being too long
		$module_name_length = strlen($module);
		$interface_name_length = strlen($interface);
		$avalible_size = self::MAX_UDP_PACKGE_SIZE - self::PACKAGE_FIXED_LENGTH - $module_name_length - $interface_name_length;
		if(strlen($msg) > $avalible_size)
		{
			$msg = substr($msg, 0, $avalible_size);
		}

		// bale
		return pack('LCCfCNnN', $ip, $module_name_length, $interface_name_length, $cost_time, $success ? 1 : 0, $code, strlen($msg), time()).$module.$interface.$msg;
	}

	/**
	 * unpacking
	 * @param string $recv_buffer
	 * @return array
	 */
	public static function decode($recv_buffer)
	{
		// unpacking
		$data = unpack("Lip/Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime", $recv_buffer);
		$module = substr($recv_buffer, self::PACKAGE_FIXED_LENGTH, $data['module_name_len']);
		$interface = substr($recv_buffer, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'], $data['interface_name_len']);
		$msg = substr($recv_buffer, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'] + $data['interface_name_len']);
		return array(
				'ip'              => $ip,
				'module'          => $module,
				'interface'        => $interface,
				'cost_time' => $data['cost_time'],
				'success'           => $data['success'],
				'time'                => $data['time'],
				'code'               => $data['code'],
				'msg'                => $msg,
		);
	}
}
