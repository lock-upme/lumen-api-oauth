<?php 
namespace App\Http\Controllers\Users;

//use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\SESSION;
//use Illuminate\Support\Facades\Cookie;
//use Illuminate\Support\Facades\Crypt;


use Illuminate\Http\Request;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Response as IlluminateResponse;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Projects\ProjectController;
//use Illuminate\Auth\Authenticatable;


class UserController extends Controller {

	//来源设置
	public $device = 'app';
	
	public function __construct(Request $request)
	{
		$this->device = $request->input('device') ? $request->input('device') : 'app';
	}

	/**
	 * 测试方法 ------ 用户注册 
	 * 
	 * @param Request $request
	 * @return array
	 * @author lock
	 */
	public function register(Request $request)
	{
		switch ($this->device) {
			case 'wap':
				$result = UserDeviceController::wapRegister();
				break;
			case 'app':
				$result = UserDeviceController::appRegister();
				break;
			case 'pc':
				$result = UserDeviceController::pcRegister();
				break;
		}
		return $result;
	}
    

}