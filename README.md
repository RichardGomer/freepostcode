# Free Postcode

An experiment (and political statement) to see if it's possible to lookup address information for a postcode based on Open Data.

This library combines the OrdnanceSurvey Open-CodePoint data with data from OpenStreetMap to give road, city, etc. for UK postcodes.  It's imperfact (especially for postcodes on corners) but given that it's free, unlike the official Postcode Address Fil from Royal Mail, that's probably forgivable.  Improvements welcomed, though!

You need to download a copy of the Open-CodePoint data to use this library - Just get it from https://www.ordnancesurvey.co.uk/opendatadownload and extract it into ./codepoint/ (Docs and all).  The  library expacts to find the actual csv files in ./codepoint/Data/CSV/ but this can be overridden when the object is instantiated if the csv files are elsewhere.

This library uses PHPcoord (by J Stott, GPL, http://www.jstott.me.uk/phpcoord/) to convert between co-ordinate systems and the OpenStreetMap Nominatim API to look up address information.  

## Usage

### Scripts

``` php
<?php


require 'freepostcode.lib.php';
require 'phpcoord/phpcoord-2.3.php';

$fpc = new FreePostcode();

try
{
    var_dump($fpc->getAddress('SO17 1BJ'));
}
catch(Exception $e)
{
    echo "ERROR: ".$e->getMessage()."\n";
}

?>
```

### Command line

```
$ php postcode.php POSTCODE
$ php postcode.php so171bj
```