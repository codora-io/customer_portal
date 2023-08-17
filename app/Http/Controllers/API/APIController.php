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

        public function registerAPI(Request $request)
        {
                $name = $request->name;
                $email = $request->email;
                $curl = curl_init();

                $data = array(
                        'query' => 'mutation {
                                        createContact(
                                                input: {
                                                        name: "'.$name.'",
                                                        contactable_id: 2,
                                                        contactable_type: Account,
                                                        email_address: "'.$email.'"
                                                }
                                        ) {
                                                id
                                                name
                                                email_address
                                        }
                                }'
                        );

                curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://bakerybroadband.sonar.software/api/graphql',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($data),
                        CURLOPT_HTTPHEADER => array(
                                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOGQzNjE1YmJiZjNkYTI2YjUxMjZhYWExMzdhMmYyY2Q4N2EwNjI5MDc3Yjk5ZjhmNmU1ZWIwMTZiNzAyZWVlOGZiOGUzODVlNzM5YTA4ZmEiLCJpYXQiOjE2>                                'Content-Type: application/json',
                                'Cookie: XSRF-TOKEN=eyJpdiI6InJEb3hHWnJpbmRVVU11Sk4rSVg4ZVE9PSIsInZhbHVlIjoiVWhWek5ZOWZnbW9lSkYvU0cvRUdzeWRRRTRrQ2ZXdGUvWDIyOGhlWUw2RkhOc3BYQkZvQjduZG8xRS9HR0NYSGVISzk3SjlEWDRESTFpTDIvZ0JUdS9BeGx5RDJROVpF>                        ),
                ));



                $response = curl_exec($curl);

                curl_close($curl);
                return response()->json($response);

    }
}
