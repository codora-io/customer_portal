<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use SonarSoftware\CustomerPortalFramework\Controllers\AccountAuthenticationController;
use App\Http\Controllers\Controller;
use App\UsernameLanguage;
use SonarSoftware\CustomerPortalFramework\Exceptions\AuthenticationException;
use App\Http\Requests\AuthenticationRequest;
use SonarSoftware\CustomerPortalFramework\Helpers\HttpHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use App\CreationToken;
use Carbon\Carbon;
use App\PasswordReset;
use App\Http\Requests\SendPasswordResetRequest;
use App\Http\Requests\PasswordUpdateRequest;

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
                                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOGQzNjE1YmJiZjNkYTI2YjUxMjZhYWExMzdhMmYyY2Q4N2EwNjI5MDc3Yjk5ZjhmNmU1ZWIwMTZiNzAyZWVlOGZiOGUzODVlNzM5YTA4ZmEiLCJpYXQiOjE2OTIyNjA0NzkuNDY3MiwibmJmIjoxNjkyMjYwNDc5LjQ2NzIsImV4cCI6MjE0NTkxNjgwMC4wODI0LCJzdWIiOiI3Iiwic2NvcGVzIjpbXX0.D7f9tFkbHRowtS8RUZtcwCQ9KNOMOw0iQpiz35BiwrthHz83tEBVQG6BhJ4XnWZifodM-d2zb8sR-3lupVsSE3kxt5ADd04bNwYfBNu2TwOoeGoGmLV_G9qbnLMt2NWJHWInhNffNez-QvfOl6-O5jqrkJ668UdyOBtv3BQrpC8iAQ45FSZd2hX3EVP8AR0g1mjmyj_EuIKJbr_fsSn8he2LcKiasRYT2PF1rLAImRURzfKBbJjzjwOrjTyox6pEFPpODCA4AcWh6ZITu7_mZMBdtQ_6yUyzAp-FXmHLDpkYFhzmTtfPDj_OqHGjuWA0_D4rAYZrDx19Rp_rRczpVBas0DEBN2RKYoPVQnGyHguRZhZ2hAKGPc05ZDh_imYn0mxDBmdUdMu1u3mvYz6ldfI-KvbeZ3fOQjOKCos52qWBg88LIrKYU_E5EI9KyFbJKeZz1nDZz4ueL0rKd4tVH8DqaAX8aDub54tq_hQFW8D_rus1ky38oozGfWKZUPsbkPU_iB76qI4kzJ4W9mqUaGxGtZHcZXilbalhHLbOtIzGCLvI2WQmzsOFYvSx16kR6Zuk-HqU2Uo_fjhQcXiV9caDtsgHxJOFzvcPPzwn94fu6eyEHNSClQi-aMt9zDBTTbAAFPg-W7eesY15DiRNbEyzQGQqSZ8dMRP1Qy1i6aA',
                                'Content-Type: application/json',
                                'Cookie: XSRF-TOKEN=eyJpdiI6InJEb3hHWnJpbmRVVU11Sk4rSVg4ZVE9PSIsInZhbHVlIjoiVWhWek5ZOWZnbW9lSkYvU0cvRUdzeWRRRTRrQ2ZXdGUvWDIyOGhlWUw2RkhOc3BYQkZvQjduZG8xRS9HR0NYSGVISzk3SjlEWDRESTFpTDIvZ0JUdS9BeGx5RDJROVpFSDRZV09DMWViMFJJMGZwK1BNNUVRbjhJVFVEcG43R04iLCJtYWMiOiI2YjU4YTMwZjQ5NzQ4NTA0OWZhNzY4YzNjOGNhNjZhNzIwZGQzMTk5YzdmMzI5Y2M0MWI1OWFlOTY2NDc4ODM2IiwidGFnIjoiIn0'
                        ),
                ));



                $response = curl_exec($curl);

                curl_close($curl);

                $httpHelper = new HttpHelper();
                $result = $httpHelper->post("customer_portal/email_lookup", ['email_address' => trim($email), 'check_if_available' => (boolean)true]);
                $creationToken = CreationToken::where('account_id', '=', $result->account_id)
                    ->where('contact_id', '=', $result->contact_id)
                    ->first();

                if ($creationToken === null) {
                    $creationToken = new CreationToken([
                        'token' => uniqid(),
                        'email' => strtolower($result->email_address),
                        'account_id' => $result->account_id,
                        'contact_id' => $result->contact_id,
                    ]);
                } else {
                    $creationToken->token = uniqid();
                }

                $creationToken->save();

                $language = 'US';
                Mail::send('emails.basic', [
                    'greeting' => trans("emails.greeting",[],$language),
                    'body' => trans("emails.accountCreateBody", [
                        'isp_name' => config("app.name"),
                        'portal_url' => config("app.url"),
                        'creation_link' => config("app.url") . "/create/" . $creationToken->token,
                    ],$language),
                    'deleteIfNotYou' => trans("emails.deleteIfNotYou",[],$language),
                ], function ($m) use ($result, $request) {
                    $m->from(config("customer_portal.from_address"), config("customer_portal.from_name"));
                    $m->to($result->email_address, $result->email_address)
                        ->subject(utrans("emails.createAccount", ['companyName' => config("customer_portal.company_name")],$request));
                });
                return response()->json(json_decode($response));

        }

        public function resendEmail(Request $request){
                $email = $request->email;
                $httpHelper = new HttpHelper();
                $result = $httpHelper->post("customer_portal/email_lookup", ['email_address' => trim($email), 'check_if_available' => (boolean)true]);
                $creationToken = CreationToken::where('account_id', '=', $result->account_id)
                    ->where('contact_id', '=', $result->contact_id)
                    ->first();

                if ($creationToken === null) {
                    $creationToken = new CreationToken([
                        'token' => uniqid(),
                        'email' => strtolower($result->email_address),
                        'account_id' => $result->account_id,
                        'contact_id' => $result->contact_id,
                    ]);
                } else {
                    $creationToken->token = uniqid();
                }

                $creationToken->save();

                $language = 'US';
                Mail::send('emails.basic', [
                    'greeting' => trans("emails.greeting",[],$language),
                    'body' => trans("emails.accountCreateBody", [
                        'isp_name' => config("app.name"),
                        'portal_url' => config("app.url"),
                        'creation_link' => config("app.url") . "/create/" . $creationToken->token,
                    ],$language),
                    'deleteIfNotYou' => trans("emails.deleteIfNotYou",[],$language),
                ], function ($m) use ($result, $request) {
                    $m->from(config("customer_portal.from_address"), config("customer_portal.from_name"));
                    $m->to($result->email_address, $result->email_address)
                        ->subject(utrans("emails.createAccount", ['companyName' => config("customer_portal.company_name")],$request));
                });
                return response()->json(['status' => true]);
        }

        public function registerUser(Request $request)
        {
                $token = $request->token;
                $password = $request->password;
                $creationToken = CreationToken::where('token', '=', trim($token))
                    ->where('updated_at', '>=', Carbon::now("UTC")->subHours(24)->toDateTimeString())
                    ->first();
                if ($creationToken === null) {
                    return response()->json([
                        'error' => 'Token Invalid'
                    ]);
                }
                $httpHelper = new HttpHelper();
                $httpHelper->patch("accounts/" . intval($creationToken->account_id) . "/contacts/" . intval($creationToken->contact_id),[
                    'username' => $creationToken->email,
                    'password' => $password
                ]);
                $creationToken->delete();
                return response()->json(['status' => true]);
        }

        public function resetPasswordEmail(Request $request)
        {
        $email = $request->email;
        $httpHelper = new HttpHelper();
        $result = $httpHelper->post("customer_portal/email_lookup", ['email_address' => trim($email), 'check_if_available' => (boolean)true]);
        $passwordReset = PasswordReset::where('account_id', '=', $result->account_id)
            ->where('contact_id', '=', $result->contact_id)
            ->first();
        if ($passwordReset === null) {
            $passwordReset = new PasswordReset([
                'token' => uniqid(),
                'email' => $result->email_address,
                'contact_id' => $result->contact_id,
                'account_id' => $result->account_id,
            ]);
        } else {
            $passwordReset->token = uniqid();
        }

        $passwordReset->save();
        $languageService = App::make(LanguageService::class);
        $language = $languageService->getUserLanguage($request);
        Mail::send('emails.basic', [
            'greeting' => trans("emails.greeting",[],$language),
            'body' => trans("emails.passwordResetBody", [
                'isp_name' => config("app.name"),
                'portal_url' => config("app.url"),
                'reset_link' => config("app.url") . "/reset/" . $passwordReset->token,
                'username' => $result->username,
            ],$language),
            'deleteIfNotYou' => trans("emails.deleteIfNotYou",[],$language),
        ], function ($m) use ($result, $request) {
            $m->from(config("customer_portal.from_address"), config("customer_portal.from_name"));
            $m->to($result->email_address, $result->email_address);
            $m->subject(utrans("emails.passwordReset", ['companyName' => config("customer_portal.company_name")],$request));
        });
        return response()->json('reset email has been sent!');
        }
        public function updatePassword(Request $request, $token){
                $passwordReset = PasswordReset::where('token', '=', trim($token))->where('updated_at', '>=', Carbon::now("UTC")->subHours(24)->toDateTimeString())->first();
                $newPassword = $request->new_password;
                $contactController = new ContactController();
                $contact = $contactController->getContact($passwordReset->contact_id, $passwordReset->account_id);
                $contactController->updateContactPassword($contact, $newPassword);
                $passwordReset->delete();
                
                return response()->json([
                    'status' => 'Password changed'
                ]);
        }

        

}
