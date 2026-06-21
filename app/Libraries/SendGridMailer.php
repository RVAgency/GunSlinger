<?php namespace App\Libraries;

class SendGridMailer
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('SENDGRID_API_KEY');
        $this->baseUrl = "https://api.sendgrid.com/v3"; // Common base URL
    }

    /**
     * Send an email
     */
    public function sendEmail($to, $subject, $message, $from = null, $fromName = 'Your Name', $isHtml = false)
    {
        $from = $from ?? 'no-reply@example.com';

        $data = [
            "personalizations" => [
                [
                    "to" => [
                        ["email" => $to]
                    ],
                    "subject" => $subject
                ]
            ],
            "from" => [
                "email" => $from,
                "name" => $fromName
            ],
            "content" => [
                [
                    "type" => $isHtml ? "text/html" : "text/plain",
                    "value" => $message
                ]
            ]
        ];

        return $this->sendRequest("/mail/send", $data, "POST");
    }

    /**
     * Sends multiple emails in a single API call using a list of Mail objects.
     * @param Mail[] $messages An array of SendGrid Mail objects.
     * @return array
     */
    public function sendBulkEmail(array $messages)
    {
        // This is a special endpoint for a batch of messages.
        // It's still the /mail/send endpoint, but the payload is different.
        $data = [
            "personalizations" => [],
            "content"          => [],
            "from"             => [],
        ];

        if (empty($messages)) {
            throw new Exception("The messages array cannot be empty.");
        }

        // The first message is used to set the global 'from' and 'content' fields.
        // All messages must have the same sender and content type.
        $firstMessage = $messages[0];

        $data['from'] = $firstMessage->getfrom()->jsonSerialize();
        $data['content'] = $firstMessage->getContents()->jsonSerialize();

        // Loop through each message object to extract the 'personalization' data
        foreach ($messages as $message) {
            foreach ($message->getPersonalizations() as $personalization) {
                $data['personalizations'][] = $personalization->jsonSerialize();
            }
        }
        
        // This is where we make the request to SendGrid.
        return $this->sendRequest("/mail/send", $data, "POST");
    }

    /**
     * Get all verified sender identities
     */
    public function getSenders()
    {
        return $this->sendRequest("/senders", null, "GET");
    }

    /**
     * Get all email templates
     * This function has been updated to use the 'generations=dynamic' query parameter
     * and to map the response to only return the 'id' and 'name'.
     */
    public function getTemplates()
    {
        // Use the new endpoint with the 'generations' query parameter
        $response = $this->sendRequest("/templates?generations=dynamic", null, "GET");

        // Check for a successful response from the API
        if ($response['status'] === "success" && isset($response['response']['templates'])) {
            // Map the templates to the desired format (id and name)
            $mappedTemplates = array_map(function($template) {
                return [
                    'id' => $template['id'],
                    'name' => $template['name']
                ];
            }, $response['response']['templates']);

            // Return the simplified, mapped array
            return $mappedTemplates;
        }

        // Return the full error response on failure
        return $response;
    }

    /**
     * General cURL request (relative URL)
     */
    protected function sendRequest($endpoint, $data = null, $method = "GET")
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 300) {
            return ["status" => "success", "response" => json_decode($response, true)];
        } else {
            return ["status" => "error", "response" => json_decode($response, true), "http_code" => $httpcode];
        }
    }
}