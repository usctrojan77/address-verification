<?php

namespace ADR;

/**
 * This class check the address passed to it against USPS API to see if it is valid
 *
 * Class AddressVerification
 * @package ADR
 *
 */
class AddressVerification
{
    /**
     * @var string
     */
    private $uspsURL;

    /**
     * @var array
     */
    private $error;

    /**
     * @var array
     */
    private $addressFields;

    /**
     * AddressVerification constructor.
     */
    public function __construct()
    {
        $this->uspsURL = 'http://production.shippingapis.com/ShippingAPI.dll';
        $this->error = ['status' => '', 'message' => ''];
        $this->addressFields = ['address1', 'address2', 'city', 'state', 'zip', 'zipExt'];
    }

    /**
     * Main method to be called for checking the address. It expects the address in the format
     *  ["address1" => "<required>", "address2"=> "<optional>", "city" => "<required>", "state" => "<required>",
     *  "zip" => "<required>", "zipExt" => "<optional>"]
     *
     * @param array $address
     * @return array
     */
    public function verifyAddress($address = [])
    {
        if (!$this->validArrayStructure($address))
        {
            return ['status' => false, 'message' => 'Invalid array structure. Array should be of the format 
            ["address1" => "<required>", "address2"=> "<optional>", "city" => "<required>", "state" => "<required>", 
            "zip" => "<required>", "zipExt" => "<optional>"'];
        }
        else
        {
            $xml = $this->createXML($address);
            $response = $this->callUSPSEndpoint($xml);
            if ($response['error'])
            {
                return ['status' => false, "message" => $response['message']];
            }
            else
            {
                $parsedResponse = $this->parseResponse($response['message']);
                return $parsedResponse;
            }
        }

    }

    /**
     * Check if the address array passed is a valid array or not
     *
     * @param $address
     * @return bool
     */
    private function validArrayStructure($address)
    {
        $addrFields = array_keys($address);
        $result = array_diff($this->addressFields, $addrFields);
        if (count($result) > 0)
        {
            return false;
        }

        return true;
    }

    /**
     * creates the xml to be sent to the USPS API
     *
     * @param array $address
     * @return string
     */
    private function createXML($address = [])
    {
        $xml = ' <AddressValidateRequest USERID="863ALLDI3666"><Address ID="1">';
        $xml .= '<Address1>' . $address['address1'] . '</Address1>';
        $xml .= '<Address2>' . $address['address2'] . '</Address2>';
        $xml .= '<City>' . $address['city'] . '</City>';
        $xml .= '<State>' . $address['state'] . '</State>';
        $xml .= '<Zip5>' . $address['zip'] . '</Zip5>';
        if (empty( $address['zipExt']))
        {
            $xml .= '<Zip4></Zip4></Address>';
        }
        else{
            $xml .= '<Zip4>'.$address['zipExt'].'</Zip4></Address>';
        }
        $xml .= '</AddressValidateRequest>';

        return $xml;
    }

    /**
     * Responsible for making the curl call to the USPS Endpoint and sending
     * back response
     *
     * @param string $xml
     * @return array
     */
    private function callUSPSEndpoint($xml = '')
    {
        $ch = curl_init($this->uspsURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "API=Verify&XML=" . $xml);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        if (!empty($error))
        {
            return ['error' => true, 'message' => $error];
        }

        return ['error' => false, 'message' => $response];
    }

    /**
     * Parses the response from the USPS Endpoint and returns its for
     * user consumption
     *
     * @param string $response
     * @return array
     */
    private function parseResponse($response = '')
    {
        try {
            $xml = new \SimpleXMLElement($response);
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
        if(!empty($xml->Address->ReturnText)) {
            return ['message' => "The address you entered was found but more information is needed 
            (such as an apartment, suite, or box number) to match to a specific address.",
                'status' => false];
        }
        if(empty($xml->Address->Error)) {
            return ['message' => "verified", 'status' => true];
        }

        return ['message' => "Address not found.", 'status' => false];
    }
}