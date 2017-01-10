<?php
use twofox\blockchain;

$myWallet = new Blockchain("ID", "pass1", "pass2");

$genAddress = $myWallet->getWalletBalance();

print_r($genAddress);