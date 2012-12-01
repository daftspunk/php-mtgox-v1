<?php

class MtGox_Private_Api extends MtGox_Api_Base 
{
    const uri_account_info = '1/generic/private/info';
    const uri_new_address = '1/generic/bitcoin/address';
    const uri_add_private_key = '1/generic/private/bitcoin/addpriv';
    const uri_add_wallet_dat = '1/generic/bitcoin/wallet_add';
    const uri_withdraw_coins = '1/generic/bitcoin/send_simple';
    const uri_id_key = '1/generic/private/idkey';
    const uri_trade_history = '1/BTC%s/private/trades';
    const uri_wallet_history = '1/generic/private/wallet/history';
    const uri_wallet_history_alt = '1/generic/wallet/history';
    const uri_submit_order = '1/BTC%s/private/order/add';
    const uri_get_orders = '1/generic/private/orders';
    const uri_order_result = '1/generic/private/order/result';

    const order_buy = 'bid';
    const order_sell = 'ask';

    // returns information about your account, funds, fees, API privileges, withdraw limits . . . 
    public function get_account_info()
    {

    }

    // get a bitcoin address linked to your account. the returned address can be used to deposit bitcoins in your mtgox account 
    public function get_new_address($description=null, $ipn_url=null)
    {

    }

    // allows you to add a private key to your account
    public function add_private_key($key, $key_type, $description=null)
    {

    }

    // allows you to add a wallet.dat file to your account
    public function add_wallet_dat($file_contents, $description=null)
    {

    }

    // Send bitcoins from your account to a bitcoin address. 
    public function withdraw_coins($address, $amount_int, $fee_int=null, $no_instant=null, $green=null)
    {

    }

    // Returns the idKey used to subscribe to a user's private updates channel in the websocket API. The key is valid for 24 hours. 
    public function get_id_key()
    {

    }

    // Returns all of your trades in this currency (BTCUSD, BTCEUR . . . ) . Does not include fees. 
    public function get_trade_history($use_alternate=false)
    {
        //alternate is self::uri_wallet_history_alt
    }

    public function get_wallet_history($currency=null, $type=null, $date_start=null, $date_end=null, $trade_id=null, $page=null )
    {
        if ($currency)
            $currency = $this->sanitise_currency($currency);
        else
            $currency = $this->active_currency;
    }

    // Buy bitcoins
    public function submit_buy_order(, $amount, $price)
    {
        $this->submit_order(self::order_buy);

    }

    // Sell bitcoins
    public function submit_sell_order(, $amount, $price)
    {
        $this->submit_order(self::order_sell);
    }

    // submits an order and returns the OID and info about success or error 
    public function submit_order($type, $amount, $price)
    {

    }

    // Get my open orders
    public function get_open_orders()
    {

    }
    
    public function get_sell_order($order_id)
    {
        return $this->get_order(self::order_sell, $order_id);
    }

    public function get_buy_order($order_id)
    {
        return $this->get_order(self::order_buy, $order_id);
    }

    // Get a particular order
    public function get_order($type, $order_id)
    {

    }
}
 