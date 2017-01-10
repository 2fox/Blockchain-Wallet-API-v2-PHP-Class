<?php
 
namespace twofox\blockchain;

/**
 *  Blockchain Wallet API v2 Class "Blockchain"
 *  https://blockchain.info/api/blockchain_wallet_api
 *
 *  @author     Dmytro Gural
 *  @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 *  @link       https://github.com/2fox/Blockchain-Wallet-API-v2-PHP-Class
 */
 
class Blockchain
{
    private $bc_identifier;     ## ($guid) Your identifier for logging in
    private $password_one;      ## Your first password
    private $password_two = false;      ## Your second password (optional)
    private $port = 3000;      ## Your second password (optional)
    
    public function __construct($blockchainid, $pw1, $pw2=false){
        $this->bc_identifier = $blockchainid;
        $this->password_one  = $pw1;
        $this->password_two  = $pw2;
    }
    
    public function changeWallet($blockchainid, $pw1, $pw2=false )
    {
        $this->bc_identifier = $blockchainid;
        $this->password_one  = $pw1;
        $this->password_two  = $pw2;
    }
    
   /**
    *   generateAddress
    **********************
    *   Generates a new receiving address for the current wallet.   
    *
    *   https://blockchain.info/merchant/$guid/new_address?password=$main_password&second_password=$second_password&label=$label
    */
    public function generateAddress($label='')
    {        
        # Default arguments
        $arg = [
        	'password' => $this->password_one,
        	'label'	   => $label
        ];
        
		if($this->password_two)
        $arg["second_password"] = $this->password_two;
		
        return $this->urlPost('new_address', $arg);
    }
    
   /**
    *   getAddressBalance
    ************************
    *   Get the balance of a bitcoin address. (Querying the balance of an address by label is depreciated.)
    *
    *   https://blockchain.info/merchant/$guid/address_balance?password=$main_password&address=$address&confirmations=$confirmations
    */
    public function getAddressBalance($address){
        # Default arguments
        $arg = [
        	'password' => $this->password_one,
        	'address'  => $address
        ];
		
        return $this->urlPost('address_balance', $arg);
    }
    
   /**
    *   getWalletBalance
    ***********************
    *   Gets the balance of the whole wallet
    *
    *   From (https://blockchain.info/api/blockchain_wallet_api):
    *   "Fetch the balance of a wallet. This should be used as an estimate only and will include unconfirmed transactions and possibly double spends."
    *
    *   https://blockchain.info/merchant/$guid/balance?password=$main_password
    */
    public function getWalletBalance()
    {
        return $this->urlPost('balance', ['password'=>$this->password_one]);
    }
    
   /**
    *   sendCoins
    ****************
    *   For sending a payment request to the blockchain api
    *
    *   $options in the form: array( "from"   => "Your preferred from address",
    *                                "shared" => "true/false",
    *                                "fee"    => "Fee greater than default of 50000 satoshi (0.0005 btc)",
    *                                "note"   => "Optional public note to include with transaction" )
    *
    *   https://blockchain.info/merchant/$guid/payment?password=$main_password&second_password=$second_password&to=$address&amount=$amount&from=$from&shared=$shared&fee=$fee¬e=$note
    */
    public function sendCoins($to, $amount, $opt=array()){
        	
        # Default arguments
        $arg = [];
        $arg["password"] = $this->password_one;
        $arg["to"]       = $to;
        $arg["amount"]   = $amount;
        
		if($this->password_two)
        $arg["second_password"] = $this->password_two;
        
        # Check if each optional argument is set and in appropriate format
        if(isset($opt["from"]) && !empty($opt["from"]) && preg_match("/^([13]{1})([A-Za-z0-9]{26,33})$/",$opt["from"])){
        	$arg["from"] = $opt["from"]; 
		}
        
        if(isset($opt["fee"]) && !empty($opt["fee"]) && is_numeric($opt["fee"])){
        	$arg["fee"] = $opt["fee"]; 
		}
		
        # Make the post request and return response        
        return $this->urlPost('payment', $arg);
    }
    
   /**
    *   sendCoinsMulti
    *********************
    *   For sending multiple payment requests to the blockchain api
    *
    *   $options in the form: array( "from"   => "Your preferred from address",
    *                                "shared" => "true/false",
    *                                "fee"    => "Fee greater than default of 50000 satoshi (0.0005 btc)",
    *                                "note"   => "Optional public note to include with transaction" )
    *
    *   $payments Is a JSON Object using Bitcoin Addresses as keys and the amounts to send as values
    *
    *   https://blockchain.info/merchant/$guid/sendmany?password=$main_password&second_password=$second_password&recipients=$recipients&shared=$shared&fee=$fee
    */
    public function sendCoinsMulti($payments, $opt=array() )    {        
        # Default arguments
        $arg = [];
        $arg["password"]   = $this->password_one;
        $arg["recipients"] = json_encode($payments);
        
        if($this->password_two)
        $arg["second_password"] = $this->password_two;
        
        # Check if each optional argument is set and in appropriate format
        if(isset($opt["from"]) && !empty($opt["from"]) && preg_match("/^([13]{1})([A-Za-z0-9]{26,33})$/", $opt["from"])){
        	$arg["from"] = $opt["from"]; 
		}
        
        if(isset($opt["fee"]) && !empty($opt["fee"]) && is_numeric($opt["fee"])){
        	$arg["fee"] = $opt["fee"]; 
		}
        
        # Make the post request and return response     
        return $this->urlPost('sendmany', $arg);
    }
    
   /**
    *   listAddresses
    ********************
    *   "List all active addresses in a wallet. Also includes a 0 confirmation balance which 
    *   should be used as an estimate only and will include unconfirmed transactions and possibly double spends."
    *
    *   https://blockchain.info/merchant/$guid/list?password=$main_password
    */
    public function listAddresses()
    {
        return $this->urlPost('list', ['password'=>$this->password_one]);
    }
    
   /**
    *   archiveAddress
    *********************
    *   Archives the provided address
    *
    *   To improve wallet performance addresses which have not been used recently should 
    *   be moved to an archived state. They will still be held in the wallet but will 
    *   no longer be included in the "list" or "list-transactions" calls. If a unique 
    *   bitcoin address is generated for each user, users who have not logged in 
    *   recently (~30 days) their addresses should be archived.
    *
    *   https://blockchain.info/merchant/$guid/archive_address?password=$main_password&second_password=$second_password&address=$address
    */
    public function archiveAddress( $address )
    {        
        # Arguments
        $arg=[];
        $arg["password"]        = $this->password_one;
        $arg["address"]         = $address;
        
        if($this->password_two)
        $arg["second_password"] = $this->password_two;
		
        return $this->urlPost('archive_address', $arg);
    }
    
    /**
    *   unarchiveAddress
    ***********************
    *   Unarchive the provided address. Also restores consolidated addresses.
    *
    *   https://blockchain.info/merchant/$guid/unarchive_address?password=$main_password&second_password=$second_password&address=$address
    */
    public function unarchiveAddress( $address )
    {        
        # Arguments
        $arg=[];
        $arg["password"]        = $this->password_one;
        $arg["address"]         = $address;
        
        if($this->password_two)
        $arg["second_password"] = $this->password_two;        
        
        return $this->urlPost('unarchive_address', $arg);
    }
    
   /**
    *   urlPost
    **************
    *   For making the requests to blockchain API and returning decoded response
    */
    private function urlPost($action, $arg=[])
    {
        $api_url = "http://localhost:".($this->port)."/merchant/".($this->bc_identifier).'/'.$action;
		if(count($arg) > 0)
			$api_url.= '?'.http_build_query($arg);
		
        return json_decode(file_get_contents($api_url));
    }
}

?>
