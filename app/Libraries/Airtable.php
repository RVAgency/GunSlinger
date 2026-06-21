<?php namespace App\Libraries;

use CodeIgniter\HTTP\Exceptions\HTTPException;

class Airtable
{
    protected $baseId;
    protected $apiKey;
    protected $apiUrl = 'https://api.airtable.com/v0';

    public function __construct()
    {
        $this->baseId = env('AIRTABLE_BASE_ID');
        $this->apiKey = env('AIRTABLE_ACCESS_TOKEN');
    }

    /**
     * Make HTTP request to Airtable
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = "{$this->apiUrl}/{$this->baseId}/{$endpoint}";

        $headers = [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        ]);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new HTTPException(curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    /**
     * Get records (with optional filter)
     */
    public function getRecords(string $table, string $filterFormula = ''): array
    {
        $endpoint = $table;
        if ($filterFormula) {
            $endpoint .= '?filterByFormula=' . urlencode($filterFormula);
        }
        return $this->request('GET', $endpoint);
    }

    /**
     * Get record by ID
     */
    public function getRecord(string $table, string $recordId): array
    {
        return $this->request('GET', "{$table}/{$recordId}");
    }

    /**
     * Create record
     */
    public function createRecord(string $table, array $fields): array
    {
        return $this->request('POST', $table, ['fields' => $fields]);
    }

    /**
     * Update record (partial update)
     */
    public function updateRecord(string $table, string $recordId, array $fields): array
    {
        return $this->request('PATCH', "{$table}/{$recordId}", ['fields' => $fields]);
    }

    /**
     * Replace record (overwrite fields)
     */
    public function replaceRecord(string $table, string $recordId, array $fields): array
    {
        return $this->request('PUT', "{$table}/{$recordId}", ['fields' => $fields]);
    }

    /**
     * Delete record
     */
    public function deleteRecord(string $table, string $recordId): array
    {
        return $this->request('DELETE', "{$table}/{$recordId}");
    }
}