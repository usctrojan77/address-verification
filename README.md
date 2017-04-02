## Address Verification
 This library can be used for address verification purposes. It uses the USPS endpoint to query for addresses.

## Usage
Example

```php
use ADR\AddressVerification

$addressVerification = new AddressVerification();

$address = ['address1' => '<Address1>', 'address2' => '<Address2>, 'city' => '<City>,
'state' => '<State>', 'zip' => '<Zip>', 'zipExt' => '<Zip extension>'];

$response = $addressVerification->verifyAddress($address);

/**

Return Response will be in the format below
['status' => 'True|False', 'message' => '']

**/

```
