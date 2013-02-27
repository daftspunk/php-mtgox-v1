<?php

/**
 * This is a silly example of a day trader script that will undercut the 
 * next person in line ("the hero") by a few cents. It will also sit idle 
 * when the BUY:SELL trade difference is not profitable.
 */

/* Usage example:

<?
	include('daytrader_class.php');
	$dt = DayTrader::create('mtgox-key', 'mtgox-secret');
	$currency = isset($_GET['currency'])?$_GET['currency']:'AUD';
	$currency = strtoupper($currency);

	switch ($currency)
	{
		case 'USD':
			$ri = rand(100,400); // Random interval
			$dt->set_play_money(11);
			$dt->set_hero(5);
		break;
		case 'AUD':
			$ri = rand(100,400); // 1 min to 7 mins or so
			$dt->set_play_money(16);
		break;
	}
?>
<html>
	<head>
		<meta http-equiv="refresh" content="<?=$ri?>">
	</head>
	<body>

		<pre>
			Currency: <?=$currency?>
			Last run <?=date('l jS \of F Y h:i:s A')?>

			Refresh interval <?=$ri/60?>mins
		<?
			$dt->execute($currency);
		?>
		</pre>
	</body>
</html>

*/

include '../mtgox_api_base.php';
include '../mtgox_private_api.php';
include '../mtgox_public_api.php';

class DayTrader 
{
	public $currency = "AUD";
	public $prune_btc_amount = 1.2; // Ignore buy/sell orders less than $10

	public $hero_count = 0;         // Which hero to grab (0 - first)
	public $hero_stupidity = 0.07;  // 70 cents -- If there is a difference of X between the next hero, ignore this dumbass
	public $notrade_diff = 0.17;    // $1.20 -- Do not trade if the buy/sell difference is greater than 

	protected $priv_api;
	protected $pub_api;

	public $play_money = 11;

	private $buys;
	private $sells;

	public static function create($key, $secret)
	{
		$self = new self();
		$self->priv_api->set_authentication($key, $secret);
		return $self;
	}

	public function __construct()
	{
		$this->pub_api = new MtGox_Public_Api();
		$this->priv_api = new MtGox_Private_Api();
	}

	public function execute($currency=nukk)
	{
		if ($currency)
			$this->set_currency($currency);

		$this->cancel_all_orders();
		sleep(5); // Give it a bit so we don't outbid ourselves
		$this->get_trades();
		
		$sell_price = $this->get_sell_price($this->hero_count);
		$buy_price = $this->get_buy_price($this->hero_count);

		echo PHP_EOL."---------------";
		echo PHP_EOL."Sell order at     ".$this->currency.$sell_price;
		echo PHP_EOL."---------------";
		echo PHP_EOL."Buy order at      ".$this->currency.$buy_price;
		echo PHP_EOL."---------------";

		if ($trade_diff = $this->calculate_trade_difference($sell_price, $buy_price))
		{
			echo PHP_EOL.'Abort... trade difference is '.$trade_diff;

			echo PHP_EOL.PHP_EOL.PHP_EOL;
			print_r($this->buys);
			echo PHP_EOL.PHP_EOL.PHP_EOL;
			print_r($this->sells);

			return;
		}
		
		$sell_money = $this->get_play_money();
		$buy_money = $this->get_play_money();

		$this->priv_api->submit_sell_order($sell_money, $sell_price);
		echo PHP_EOL;
		echo PHP_EOL.'Selling '.$sell_money . ' x BTC';

		$this->priv_api->submit_buy_order($buy_money, $buy_price);
		$this->priv_api->submit_buy_order($sell_money, $buy_price);
		$this->priv_api->submit_buy_order($buy_money, $buy_price);
		echo PHP_EOL;
		echo PHP_EOL.'Buying  '.$buy_money . ' x BTC';
		

		$sell_comm = $sell_price * 0.005;
		$buy_comm = $buy_price * 0.005;

		echo PHP_EOL;
		echo PHP_EOL."CURRENT PROFIT PER COIN TRADED: $";
		echo (($sell_price-$sell_comm) - ($buy_price+$buy_comm));
	}

	public function set_currency($currency)
	{
		$currency = strtoupper($currency);
		$this->currency = $currency;
		$this->pub_api->set_currency($currency);
		$this->priv_api->set_currency($currency);
	}

	public function set_hero($count)
	{
		$this->hero_count = $count;
	}

	// Creates a random number to look less like a bot
	// but includes a signature "1337" at the end
	public function get_play_money()
	{
		$rand = rand(1000,9999);
		$rand = $rand / 10000;
		$money = $this->play_money;
		$money += 0.00001337;
		$money += $rand;
		return $money;
	}

	public function set_play_money($money)
	{
		$this->play_money = $money;
	}

	public function get_trades()
	{
		// Lets go
		$depth = $this->pub_api->get_depth();
		$buys = $depth['bids'];
		$sells = $depth['asks']; 

		// Prune results
		foreach ($buys as $key=>$depth)
		{
			$d = (object)$depth;
			if (!isset($d->amount) || $d->amount < $this->prune_btc_amount)
				unset($buys[$key]);
		}
		foreach ($sells as $key=>$depth)
		{
			$d = (object)$depth;
			if (!isset($d->amount) || $d->amount < $this->prune_btc_amount)
				unset($sells[$key]);
		}

		// Sample highest 10
		$buys = array_reverse($buys); 
		$buys = array_slice($buys, 0, 10);
		$sells = array_slice($sells, 0, 10);

		$this->buys = $buys;
		$this->sells = $sells;
	}

	public function cancel_all_orders()
	{
		// Cancel all orders
		$all_orders = $this->priv_api->get_open_orders();
		foreach ($all_orders as $order)
		{
		    if (isset($order['oid']) && isset($order['type']))
		    {
		        if ($order['currency'] != $this->currency)
		        	continue;

		        $this->priv_api->cancel_order($order['type'], $order['oid']);
		    }
		}
	}

	public function calculate_trade_difference($sell_price, $buy_price)
	{
		// Calc importance
		$trade_diff = $sell_price - $buy_price;

		echo PHP_EOL."Buy price:        $" .$buy_price;
		echo PHP_EOL."Sell price:       $" .$sell_price;
		echo PHP_EOL."---------------";
		echo PHP_EOL."Trade difference: $" .$trade_diff;
		echo PHP_EOL."---------------";

		if ($trade_diff < $this->notrade_diff)
			return $trade_diff;
		else 
			return null;
	}

	// Sell
	//

	public function get_sell_price($hero_count)
	{
		$hero = $this->find_sell_hero($hero_count);

		// Undercut our hero
		$sell_price = $hero->price - 0.00001;

		return $sell_price;
	}

	private function find_sell_hero($count=null)
	{
		if ($count===null)
			$count = 0;

		$hero = (object)$this->sells[$count];
		$next = (object)$this->sells[$count+1];
		$diff = $next->price - $hero->price;

		if ($diff > $this->hero_stupidity)
			return $this->find_sell_hero($count+1);

		return $hero;
	} 

	// Buy
	//

	public function get_buy_price($hero_count=null) 
	{
		$hero = $this->find_buy_hero($hero_count);

		// Undercut our hero's price
		$buy_price = $hero->price + 0.00001;
		$buy_price2 = $buy_price + (rand(1,5) / 100000);
		$buy_price3 = $buy_price2 + (rand(1,5) / 100000);
		$buy_price4 = $buy_price3 + (rand(1,5) / 100000);

		return $buy_price;
	}

	private function find_buy_hero($count=null)
	{
		if ($count===null)
			$count = 0;

		$hero = (object)$this->buys[$count];
		$next = (object)$this->buys[$count+1];
		$diff = $hero->price - $next->price;

		if ($diff > $this->hero_stupidity) 
			return $this->find_buy_hero($count+1);

		return $hero;
	}
}