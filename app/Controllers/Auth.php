<?php

namespace App\Controllers;
use App\Libraries\Airtable;

class Auth extends BaseController{

    public function login(): string{
        // Check session in constructor
        if (session()->has('user')) {
            header('Location: ' . base_url('/campaigns'));
            exit;
        }
        return view('/auth/login');
    }

    public function authenticate_user(){
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        //Fetch the records from the airtable as per the need
        $airtable = new Airtable();
        // Escape backslashes and quotes so the email cannot alter the Airtable formula
        $safeEmail = str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $email);
        $filter = "{email}=\"{$safeEmail}\"";
        $records = $airtable->getRecords('auth', $filter);

        // $airtableBaseId = env('AIRTABLE_BASE_ID');
        // $airtableAccessToken = env('AIRTABLE_ACCESS_TOKEN');
        // $url = "https://api.airtable.com/v0/{$airtableBaseId}/auth?filterByFormula={email}=\"{$email}\"";

        // $headers = [
        //     "Authorization: Bearer {$airtableAccessToken}"
        // ];

        // $ch = curl_init();
        // curl_setopt_array($ch, [
        //     CURLOPT_URL => $url,
        //     CURLOPT_HTTPHEADER => $headers,
        //     CURLOPT_RETURNTRANSFER => true,
        // ]);

        // $response = curl_exec($ch);

        // if (curl_errno($ch)) {
        //     throw new HTTPException(curl_error($ch));
        // }

        // curl_close($ch);
        // $data = json_decode($response, true);
        $users = isset($records['records']) && !empty($records['records']) ? $records['records'][0] : [];
        if (!empty($users)) {
            $user = $users['fields'];
            // Assuming password is stored in Airtable under "password"
            if (isset($user['status']) && $user['status'] == 1 && password_verify($password, $user['password'])){
                // Store session
                session()->set('user', [
                    'id' => $users['id'],
                    'email' => $user['email'],
                    'name' => $user['name'] ?? ''
                ]);
                echo json_encode([
                    'status'   => 'success',
                    'message'  => 'Login successful',
                    'redirect' => base_url('/campaigns')
                ]);
                return;
            } else {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Username or Password is invalid'
                ]);
                return;
            }
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Username or Password is invalid'
            ]);
            return;
        }
    }

    public function logout(){
        // Destroy session
        session()->destroy();
        // If not AJAX → normal redirect
        return redirect()->to(base_url('login'));
    }
}