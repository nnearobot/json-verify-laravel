<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class VerificationController extends Controller
{
    const FILE_FIELD_NAME = 'json_file';
    const GOOGLE_DNS_API_URL = 'https://dns.google/resolve';

    /**
     * Handle the JSON verification
     */
    public function verify(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the uploaded file:
        // The maximum file size is 2MB (2048 kilobytes)
        $request->validate([
            self::FILE_FIELD_NAME => 'required|file|mimes:json|max:2048',
        ]);

        // Retrieve the file from the request:
        $file = $request->file(self::FILE_FIELD_NAME);

        // Read file contents:
        $jsonContents = file_get_contents($file->getRealPath());

        // Decode JSON data:
        $json = json_decode($jsonContents);

        // Check for JSON decode errors:
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['result' => 'invalid_json_format'], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        /*** Perform checks on conditions for “Verified” ***/
        $result = match (false) {
            $this->isRecipientValid($json) => 'invalid_recipient',
            $this->isIssuerValid($json) => 'invalid_issuer',
            $this->isSignatureValid($json) => 'invalid_signature',
            // ... add another verifications here if needed
            default => 'verified',
        };

        // Store the verification result in the database for analysis purposes
        $this->storeResultToDB($result);

        // The API should return a 200 status code even if the file is not verified
        return response()->json([
            'issuer' => $jsonData->issuer->name ?? '',
            'result' => $result,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * Verify JSON has a valid recipient:
     * recipient must have name and email.
     */
    protected function isRecipientValid(\stdClass $json): bool
    {
        return !empty($json->data->recipient->name) && !empty($json->data->recipient->email);
    }

    /**
     * Verify JSON has a valid issuer:
     * issuer must have name and identityProof,
     * the value of issuer.identityProof.key must be found in the DNS TXT record of the domain name specified by issuer.identityProof.location.
     */
    protected function isIssuerValid(\stdClass $json): bool
    {
        if (
            empty($json->data->issuer->name) ||
            empty($json->data->issuer->identityProof->key) ||
            empty($json->data->issuer->identityProof->location)
        ) {
            return false;
        }

        return $this->verifyDnsRecord($json->data->issuer->identityProof->location, $json->data->issuer->identityProof->key);
    }

    /**
     * Verify if the issuer identity proof key exists in the DNS TXT records.
     */
    protected function verifyDnsRecord(string $domain, string $key): bool
    {
        $response = Http::get(self::GOOGLE_DNS_API_URL, [
            'name' => $domain,
            'type' => 'TXT'
        ]);

        if (!$response->successful()) {
            return false;
        }

        $data = $response->json();

        if (empty($data['Answer'])) {
            return false;
        }

        // Check all the TXT records (type 16 corresponds to TXT records):
        foreach ($data['Answer'] as $record) {
            if ($record['type'] == 16 && str_contains($record['data'], $key)) {
                return true; // Key found in TXT record
            }
        }

        return false;
    }

    /**
     * Verify JSON has a valid signature.
     */
    protected function isSignatureValid(\stdClass $json): bool
    {
        if (empty($json->signature->targetHash)) {
            return false;
        }

        // List each property's path from the data object using a dot notation, and associate its value:
        $properties = $this->listProperties($json->data);

        // For each property's path, compute a hash using the property's key-value pair:
        $hashArr = [];
        foreach ($properties as $key => $value) {
            $hashArr[] = $this->computeHash($key, $value);
        }

        // Sort all the hashes from the previous step alphabetically and hash them all together using sha256:
        sort($hashArr);
        $hash = hash('sha256', json_encode($hashArr));

        // The file is considered “unverified” if the two hashes don’t match:
        return $hash === $json->signature->targetHash;
    }

    /**
     * List each property's path from the data object using a dot notation, and associate its value.
     */
    private function listProperties(\stdClass $object, string $prefix = '', array &$properties = []): array
    {
        foreach ($object as $key => $value) {
            $path = $prefix === '' ? $key : $prefix.'.'.$key;

            if (is_object($value)) {
                // Recursively traverse the object
                $this->listProperties($value, $path, $properties);
            } else {
                // Add the path and value to the properties array
                $properties[$path] = $value;
            }
        }

        return $properties;
    }

    /**
     * Compute an SHA-256 hash for a key-value pair.
     */
    private function computeHash(string $key, string $value): string
    {
        $pair = json_encode([$key => $value]);

        return hash('sha256', $pair);
    }

    /**
     * Store the verification result in the database for analysis purposes.
     */
    private function storeResultToDB(string $result): bool
    {
        // We use a database transaction to ensure data integrity (if we're going to make some other DB changes in the future)
        try {
            // Begin a transaction
            DB::beginTransaction();

            VerificationResult::create([
                'user_id' => auth()->id(),
                'file_type' => 'JSON', // only supports JSON for now
                'verification_result' => $result
            ], 2); // let's try one more time if something's gone wrong

            // ... some other DB changes

            // Commit the transaction
            DB::commit();

            return true;
        } catch (\Exception $e) {
            // An error occurred; rollback the transaction
            DB::rollback();
            Log::error($e->getMessage());
        }

        return false;
    }

    /**
     * Display a listing of all verification results (without pagination for simplicity purpose).
     */
    public function getResults(): \Illuminate\Http\JsonResponse
    {
        $verificationResults = VerificationResult::all();

        return response()->json($verificationResults);
    }
}
