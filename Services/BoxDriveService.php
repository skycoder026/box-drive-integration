<?php 


class BoxDriveService
{
    public  $authToken          = '';
    private $boxDriveUrl        = 'https://api.box.com/oauth2/token/';
    private $root_folder_id     = "171076831083";

    private $boxDriveSecretPath = "secrets/box_drive_client_secret.json";
    
    private $boxDriveTokenArr = [
        'client_id'     => '7d1cx3othjfay44brv8gr5jo3d7qnfs5',
        'client_secret' => 'peogB7153oH7luICxEB6gVXVi06Dh0cZ',
        'grant_type'    => 'refresh_token'
    ];

    // https://account.box.com/api/oauth2/authorize?response_type=code&client_id=7d1cx3othjfay44brv8gr5jo3d7qnfs5&redirect_uri=http://localhost/core-php/box-drive/index.php

    public function __construct()
    {
        $this->generateRefreshToken();
    }

    private function generateRefreshToken()
    {

        if (file_exists($this->boxDriveSecretPath)) {
            $auth_data = json_decode(file_get_contents($this->boxDriveSecretPath), true);

            
            $currentTime      = strtotime(date('Y-m-d H:i:s', time()));
            $secretCreateTime = date('Y-m-d H:i:s', strtotime("+1 minutes", strtotime($auth_data['create_time'])));
            $secretCreateTime = strtotime($secretCreateTime);
            
            if ($secretCreateTime < $currentTime) {
                $this->boxDriveTokenArr['refresh_token'] = $auth_data['refresh_token'];
                $this->updateBoxDriveRefreshToken();

            } else {
                $this->authToken = "Bearer " . $auth_data['access_token'];
            }

        } else {

            if (!file_exists(dirname($this->boxDriveSecretPath))) {
                mkdir(dirname($this->boxDriveSecretPath), 0700, true);
            }

            $this->updateBoxDriveRefreshToken();
        }
    }

    
    private function updateBoxDriveRefreshToken()
    {
        $token_data = $this->refreshAuthToken();
        $data       = json_decode($token_data);

        if(isset($data->access_token)) {
            $data->create_time = date('Y-m-d H:i:s');
            file_put_contents($this->boxDriveSecretPath, json_encode($data));

            $this->authToken = "Bearer $data->access_token";
        }
    }

    
	private function refreshAuthToken()
    {
        $data    = $this->boxDriveTokenArr;
        $options = [
            CURLOPT_URL            => $this->boxDriveUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => 'grant_type=refresh_token&client_id=' . $data['client_id'] . '&client_secret=' . $data['client_secret'] . '&refresh_token=' . $data['refresh_token'],
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded']
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return $err;
		}

        return $response;
	}

    public function getFiles()
    {
        try {
			$url = $this->boxDriveUrl . "folders/" . $this->root_folder_id . "/items";

			$data = $this->processRequest($url);

            dd('dksfdjf');
            dd($data);
				
		} catch(Exception $e) {
			print_r( $e->getMessage() );
		}
    }


    private function processRequest($url)
    {
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => ['Authorization: ' . $this->authToken]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        
        $err = curl_error($curl);

        curl_close($curl);
        dd($response);
        dd($options);
        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }
}