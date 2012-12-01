<?php

class MtGox_Api_Base
{

    private $key;
    private $secret;
    private $active_currency;

    const api_url = 'https://mtgox.com/api/';

    protected $currency_codes = array(
        'USD' => 'USD',
        'AUD' => 'AUD',
        'CAD' => 'CAD',
        'CHF' => 'CHF',
        'CNY' => 'CNY',
        'DKK' => 'DKK',
        'EUR' => 'EUR',
        'GBP' => 'GBP',
        'HKD' => 'HKD',
        'JPY' => 'JPY',
        'NZD' => 'NZD',
        'PLN' => 'PLN',
        'RUB' => 'RUB',
        'SEK' => 'SEK',
        'SGD' => 'SGD',
        'THB' => 'THB'
    );

    public function __construct($key=null, $secret=null, $currency='USD')
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->set_currency($currency);
    }

    public function set_currency($currency)
    {
        $this->active_currency = $this->sanitise_currency($currency);
        return $this;
    }

    public function sanitise_currency($currency)
    {
        $currency = strtoupper($currency);

        if (!isset($this->currency_codes[$currency]))
            throw new Exception('Currency code '.$currency.' not available');

        return $currency;
    }

    public function send_api_request($path, $params = array())
    {
        $mt = explode(' ', microtime());
        $params['nonce'] = $mt[1] . substr($mt[0], 2, 6);
        $post_data = http_build_query($params, '', '&');
        $headers = array(
            'Rest-Key: ' . $this->key,
            'Rest-Sign: ' . base64_encode(
                hash_hmac('sha512', $post_data, base64_decode($this->secret), true)
             )
        );
        $user_agent = 'Mozilla/4.0 (compatible; MtGox PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')';
        static $ch = null;
        if (is_null($ch)) 
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        }
        curl_setopt($ch, CURLOPT_URL, self::api_url . $path);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $net = curl_exec($ch);
        
        if ($net === false) 
            throw new Exception('Could not get reply: ' . curl_error($ch));
        
        $result = json_decode($net, true);

        if (!$result) 
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');

        return $result;
    }

    private function build_get_string($params)
    {
        $get_array = array();
        foreach ($params as $key=>$val)
            $get_array[] = $key.'='.$val;

        return implode("&", $get_array);
    }    

}