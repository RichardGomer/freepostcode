<?php

require 'freepostcode.lib.php';
require 'phpcoord/phpcoord-2.3.php';

if(count($argv) < 2)
{
    echo "USAGE: php test.php POSTCODE\n";
    return;
}

$fpc = new FreePostcode();

try
{
    var_dump($fpc->getAddress($argv[1]));
}
catch(Exception $e)
{
    echo "ERROR: ".$e->getMessage()."\n";
}

?>