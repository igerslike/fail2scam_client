<?php
/**
 * F2S Client example
 *
 */
include 'vendor/autoload.php';

use F2S\Client;
use F2S\Record;

function res($record)
{
    print sprintf(
        "%sDATA = %s: isValid? %s%s",
        "\n" . str_repeat('=', 50) . "\n",
        print_r($record->getData(), true),
        ($record->isValid() ? "YES" : "NO - " . $record->dumpMessages()),
        "\n" . str_repeat('=', 50) . "\n"
    );
}

// -- EXAMPLE

$client = new Client([
    'key' => '$2y$10$HwJL5UDaWaeSUsWf02yJJewFsLJUMS7VyJF4U/Di336Mt7b4SGnV.',
    'verbose' => true
]);

// Example 1
// In this example we'll try to validate an email address using the RISKY acceptance level

$record = new Record();
$record->filterByEmail('john.doe@mailinator.com');
try {
    $record = $client->single($record, Client::LEVEL_RISKY);
    res($record);
} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}

// which results in false, as this email is already on our system


// Example 2
// In this example we'll try to validate an email address using the STRICT acceptance level

try {
    $record = $client->single($record, Client::LEVEL_STRICT);
    res($record);
} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}

// which results in true, because this email didn't have any major incident and it was validated by our staff
// being a low level incident or wrongly reported



// Example 3
// Validate a batch of records
// NOTE: this will count as 3 API Key usages!
$records = [];
// this record will be validated against an email AND a phone number
$records[] = (new Record())->filterByEmail('john.doe@email.com')->filterByPhoneNumber('111111111');
$records[] = (new Record())->filterByIpAddress('0.0.0.0');
$records[] = (new Record())->filterByCreditCardNumber('0000000000000000');

try {
    $records = $client->batch($records, Client::LEVEL_STRICT);
    foreach ($records as $record) {
        res($record);
    }
} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}
