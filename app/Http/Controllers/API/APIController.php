<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use SonarSoftware\CustomerPortalFramework\Controllers\AccountAuthenticationController;
use App\Http\Controllers\Controller;
use App\UsernameLanguage;
use SonarSoftware\CustomerPortalFramework\Exceptions\AuthenticationException;
use App\Http\Requests\AuthenticationRequest;
use SonarSoftware\CustomerPortalFramework\Helpers\HttpHelper;


class APIController extends Controller
{

        public function loginAPI(AuthenticationRequest $request)
        {
                $httpHelper = new HttpHelper();
                $result = $httpHelper->post("customer_portal/auth", ['username' => $request->input('username'), 'password' => $request->input('password')]);
                if($result)
                {
                        return response()->json([
                                'status' => true,
                                'user' => base64_encode(json_encode($result))
                        ]);
                }
                return response()->json([
                        'status' => false
                ]);
        }

}
t
