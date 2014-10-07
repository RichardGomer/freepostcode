<?php

/**
 * Get street information by postcode (Similar to the PAF file) using Ordnance Survey Code-Point Open + OpenStreetMap
 *
 * Because the PAF should absolutely be open data and it is outrageous that our politicians gave it away to a private company
 *
 * Feedback to richardgomer on github.  Lawsuits can be placed up your bottom.
 
 * Requires:
 *   - a copy of the Ordnance Survey's Open Codepoint data, to convert postcodes into Easting/Northing
 *   - the rather wonderful PHPcoord library by J.M.Stott http://www.jstott.me.uk/phpcoord/ to convert Easting/Northing into latlong
 *   - access to the Nominatim API of OpenStreetmap, to convert latlong into a street
 */

class FreePostcode 
{
    /**
     * Construct with the filename of the codepointOpen data directory
     */
    public function __construct($codePointDir=false)
    {
        /**
         * CONFIG - Things that might sometimes change, but probably not often enough to be configurable at runtime
         */
        $this->codepointfields = array('PC', 'PQ', 'EA', 'NO', 'CY', 'RH', 'LH', 'CC', 'DC', 'WC'); // Map of codepoint CSV fields - see Doc/Code-Point_Open_Column_Headers.csv for info
        
        /**
         * END CONFIG
         */
        
        if($codePointDir === false)
            $codePointDir = dirname(__FILE__).'/codepoint/Data/CSV/';
        
        // Ensure the dirname has a trailing slash
        if(!preg_match('@/$@', $codePointDir))
            $codePointDir .= '/';
        
        $this->data = $codePointDir;
    }
    
    public function getAddress($postcode)
    {
        // Look up the postcode
        $pcdata = $this->lookupPostcode($postcode);
        
        // Convert easting/northing to latlong using PHPcoord
        $pos = new OSRef($pcdata['EA'], $pcdata['NO']);
        $ll = $pos->toLatLng();
        $ll->OSGB36ToWGS84(); // Most applications use WGS84, presumably including OSM
        $lat = $ll->lat;
        $lon = $ll->lng;
        
        // Lookup address from Nominatim
        return $this->getNominatim($lat, $lon);
        
    }
    
    // Get the raw record from the codepoint file for the given postcode
    // Column headings are given in the $conf section up top
    public function lookupPostcode($postcode)
    {
        $postcode = strtoupper($postcode);
        $postcode = str_replace(' ', '', $postcode); // Remove spaces, since CodePoint doesn't include them
        
        $city = substr($postcode, 0, 2);
        
        $filename = $this->getCityFile($city);
        
        $fh = fopen($filename, 'r');
        // Now search through the file to find the postcode
        // TODO: This could be a lot more efficient - binary search or something....
        $found = false;
        while(($line = fgetcsv($fh)) !== false)
        {
            if($line[0] == $postcode)
            {
                $found = true;
                break;
            }
        }
        
        if(!$found)
        {
            throw new InvalidPostcodeException("$postcode was not found in $filename");
        }
        
        return array_combine($this->codepointfields, $line);
    }
    
    protected function getCityFile($city)
    {
        $filename = $this->data.strtolower($city).'.csv';
        
        if(!file_exists($filename))
        {
            throw new InvalidPostcodeException("$city does not seem to be a valid postcode area ($filename was not found)");
        }
        
        return $filename;
    }
    
    public function getNominatim($lat, $lon)
    {
        $url = 'http://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=18&addressdetails=1';
        $data = file_get_contents($url);
        
        return json_decode($data);
    }
    
}

class InvalidPostcodeException extends Exception {}




?>