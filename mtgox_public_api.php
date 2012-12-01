<?php

class MtGox_Public_Api extends MtGox_Api_Base 
{

    const uri_ticker          = '1/BTC%s/ticker';
    const uri_depth           = '1/BTC%s/depth';
    const uri_trades          = '1/BTC%s/trades';
    const uri_cancelledtrades = '1/BTC%s/cancelledtrades';
    const uri_fulldepth       = '1/BTC%s/fulldepth';
    const uri_tx_details      = '1/BTC%s/tx_details';

    // returns the current ticker for the selected currency
    public function get_ticker($currency)
    {
        return $this->send_public_request(self::uri_ticker, $currency);
    }

    // Depth returns outstanding asks (selling) and bids (buying) orders
    //   Returns array('asks'=>array(...), 'bids'=>array(...));
    public function get_depth($currency)
    {
        return $this->send_public_request(self::uri_depth, $currency);
    }

    // To get only the trades since a given trade id
    public function get_trades($currency, $since_txn=null, $show_duplicates=false)
    {
        $params = array();
        if ($since_txn)
            $params['since'] = $since_txn;

        $params['primary'] = ($show_duplicates) ? 'N' : 'Y';

        return $this->send_public_request(self::uri_trades, $currency, $params);
    }

    // returns a list of all the cancelled trades this last month, list of trade ids in json format
    public function get_cancelledtrades($currency)
    {
        return $this->send_public_request(self::uri_cancelledtrades, $currency);
    }

    // WARNING : since this is a big download, there is a rate limit on how often you can get it, 
    // limit your requests to 5 / hour or you could be dropped / banned. 
    public function get_fulldepth($currency)
    {
        return $this->send_public_request(self::uri_fulldepth, $currency);
    }

    https://mtgox.com/api/1/generic/bitcoin/tx_details?hash=4462c88079cc51972f1bdcb8a4240ee8757b0bb69df828ade051c95ced540fa0

    private function send_public_request($uri, $currency, $params=array())
    {
        $uri = sprintf($uri, $this->sanitise_currency($currency));
        $uri .= '?' . $this->build_get_string($params);

        $result = $this->send_api_request($uri);

        if (!isset($result['result']) || $result['result'] != 'success')
            throw new Exception('Error from gateway');

        return $result['return'];
    }

}