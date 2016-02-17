<?php 
/**
 * 帐号用户
 * 
 * @author Lock
 *
 */
namespace App\Http\Controllers\Users;

//use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use Illuminate\Http\Request;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Response as IlluminateResponse;

use App\Http\Controllers\Controller;


class UserDeviceController extends Controller {

	/**
	 * PC 用户注册
	 * 
	 * @return string
	 * @author lock
	 */
	public static function pcRegister()
	{
		//self::appRegister();
		return 'pc register';
	}
	
	/**
	 * App用户注册
	 * 
	 * @return string
	 * @author lock
	 */
	public static function appRegister()
	{
		return 'app register';
	}	
	
	/**
	 * Wap用户注册
	 * 
	 * @return string
	 * @author lock
	 */
	public static function wapRegister()
	{
		return 'wap register';
	}

}