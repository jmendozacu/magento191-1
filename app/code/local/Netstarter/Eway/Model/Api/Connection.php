<?php

class Netstarter_Eway_Model_Api_Connection
{
    
    public function setUrl($url)
    {
        $this->_url = $url;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    public function GetAccessCodeResult($request)
    {
        $url = "AccessCode/".$request->AccessCode;
        
        $request = json_encode($request);
        $response = $this->PostToRapidAPI($url, $request, false);
        return json_decode($response);
    }
    
    public function CreateAccessCode($request)
    {
        $request = json_encode($request);
        $response = $this->PostToRapidAPI("AccessCodes", $request);
        return json_decode($response);
    }
    
    public function ChargeCustomer($request)
    {
        $request = json_encode($request);
        $response = $this->PostToRapidAPI("Transaction", $request);
        return json_decode($response);
    }
    
    public function PostCreditCardData($url, $paramsPostCreditCardData)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($paramsPostCreditCardData));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        return $server_output;
    }
    
    private function PostToRapidAPI($url, $request, $IsPost = true)
    {
        $url = $this->_url . $url;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        
        if ($IsPost)
        {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        else
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        $response = curl_exec($ch);

        if (curl_errno($ch) != CURLE_OK) {
            return "<h2>POST Error: " . curl_error($ch) . " URL: $url</h2><pre>";
        }
        else
        {
            $info = curl_getinfo($ch);
            if ($info['http_code'] == 401 || $info['http_code'] == 404)
            {
                return "<h2>Please check the API Key and Password</h2><pre>";
            }

            curl_close($ch);
            return $response;
        }
    }
    
}