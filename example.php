<?php

include_once __DIR__.'/src/Blockchain.php';

$myWallet = new Blockchain("0c497311-7871-4279-b668-adb167862191", "sikEon,6PN'riGh<f~>8");

$recipients = [
				'1JzSZFs2DQke2B3S4pBxaNaMzzVZaG4Cqh' => '100000000'
			  ];

$genAddress = $myWallet->sendCoinsMulti($recipients);

print_r($genAddress);