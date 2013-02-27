php-mtgox-v1
============

MtGox V1 API library written in PHP

# Private API usage example
```
$api = new MtGox_Private_Api();
$api->set_currency('USD');
$api->set_authentication('ENTER-MTGOX-KEY-HERE', 'ENTER-MTGOX-SECRET-HERE');
$history = $api->get_wallet_history('USD');
print_r($history);
```

# Public API usage example
```
$api = new MtGox_Public_Api();
$api->set_currency('USD');
$depth = $this->pub_api->get_depth();
$buys = $depth['bids'];
$sells = $depth['asks']; 
print_r($buys);
```