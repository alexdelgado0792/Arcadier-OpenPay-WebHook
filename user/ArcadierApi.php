<?php

class ArcadierApi
{
    protected $baseUrl;
    protected $packageId;
    protected $adminToken;
    private $clientId;
    private $clientSecret;
    protected $adminId;

    function __construct($clientId, $clientSecret)
    {
        $this->baseUrl = $this->GetBaseUrl();
        $this->packageId = $this->GetPackageId();

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $adminInfo = $this->GetAdminToken();
        $this->adminToken = $adminInfo['token'];
        $this->adminId = $adminInfo['adminId'];

    }

    function GetBaseUrl()
    {
        $marketplace = $_COOKIE['marketplace'];
        $protocol = $_COOKIE['protocol'];
        return $protocol . '://' . $marketplace;
    }

    function GetPackageId()
    {
        $requestUri = "$_SERVER[REQUEST_URI]";
        preg_match('/([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/', $requestUri, $matches, 0);
        return $matches[0];
    }

    function GetAdminToken()
    {
        $token = array();

        $url = $this->baseUrl . '/token';

        $body = 'grant_type=client_credentials&client_id=' . $this->clientId . '&client_secret=' .
            $this->clientSecret . '&scope=admin';

        $response = $this->callAPI('POST', null, $url, $body);

        if ($response != null && array_key_exists('access_token', $response) && array_key_exists('UserId', $response)) {
            $token['token'] = $response['access_token'];
            $token['adminId'] = $response['UserId'];
        }

        return $token;
    }

    function SendEmail($from, $to, $body, $subject)
    {
        $result = null;

        $url = $this->baseUrl . '/api/v2/admins/' . $this->adminId . '/emails';

        $request = [
            "From" => $from,
            "To" => $to,
            "Body" => $body,
            "Subject" => $subject
        ];

        $response = $this->callAPI('POST', $this->adminToken, $url, json_encode($request));
        if ($response != null) {
            $result = $response;
        }

        return $result;
    }

    function callAPI($method, $access_token, $url, $data = false)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    $jsonDataEncoded = $data;
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonDataEncoded);
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    $jsonDataEncoded = $data;
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonDataEncoded);
                }
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                // if ($data) {
                //     $jsonDataEncoded = json_encode($data);
                //     curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonDataEncoded);
                // }
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        $headers = ['Content-Type: application/json'];
        if ($access_token != null && $access_token != '') {
            array_push($headers, sprintf('Authorization: Bearer %s', $access_token));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

}
?>