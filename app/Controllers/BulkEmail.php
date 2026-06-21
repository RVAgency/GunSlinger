<?php

namespace App\Controllers;

// Import the SendGridMailer library
use App\Libraries\SendGridMailer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\Airtable;

class BulkEmail extends BaseController
{
    /**
     * @var SendGridMailer
     */
    protected $sendGridMailer;

    public function __construct()
    {
        // Check session in constructor
        if (!session()->has('user')) {
            header('Location: ' . base_url('/login'));
            exit;
        }
        //get the sendgrid list of the sender emails first and then get the email templates dynamic here as per the need
        $this->sendGridMailer = new SendGridMailer();
    }

    public function index()
    {
        // Call the getTemplates() method from the library
        $data['templates'] = $this->sendGridMailer->getTemplates();
        return view('bulk_email', $data);
    }

    public function campaigns_list()
    {
        return view('campaigns_list');
    }

    /**
     * Handles the bulk email sending via an Excel file upload.
     * It receives an Excel file and a templateId from the client and sends them as a single batch.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function send_bulk_emails_old()
    {
        // Make sure the script doesn't timeout
        ini_set('max_execution_time', 0); // 0 = unlimited
        set_time_limit(0);
        // Bump memory in case many emails
        ini_set('memory_limit', '512M');
        try {
            // Get the uploaded file instance. Assuming the file input name is 'emails_file'.
            $templateId = $this->request->getPost('templateSelect');
            //Get the email subject line here
            $email_subject = $this->request->getPost('emailSubject');
            //Get the from email id here
            $from_email = $this->request->getPost('fromEmailSelect');
            //Get the from email name
            $from_email_name = $this->request->getPost('fromEmailName');
            $upload_group_file_name = $this->request->getPost('fromReceipeintGroup');
            $filePath = WRITEPATH . 'uploads/excels/' . $upload_group_file_name;
            // Validate that the file and templateId are present
            if (!file_exists($filePath)) {
                return $this->failValidationError('Invalid or missing file upload.');
            }
            if (!$templateId) {
                return $this->failValidationError('Missing email template in the request.');
            }           
            // Get the temporary path of the uploaded file
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray();
            // Check if there are any rows to process
            if (empty($rows)) {
                return $this->failValidationError('The uploaded Excel file is empty.');
            }
            // We'll skip the first row, assuming it's a header row
            $emails = [];
            foreach (array_slice($rows, 1) as $row) {
                $email = isset($row[1]) ? trim($row[1]) : null; // second column
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            $total_email_sent = (int) count($emails);
            $url = env('SENDGRID_API_URL');
            // $personalizations = [
            //     [
            //         "to" => array_map(fn($email) => ["email" => $email], $emails),
            //         "dynamic_template_data" => [
            //             "subject" => $email_subject,
            //             "name" => "Vishal"
            //         ]
            //     ]
            // ];
            //Was having issue with the receipient as the receipient was getting display all to emails
            $personalizations = [];
            foreach ($emails as $email) {
                $personalizations[] = [
                    "to" => [
                        ["email" => $email]
                    ],
                    "dynamic_template_data" => [
                        "subject" => $email_subject,
                        "name" => "Vishal"
                    ]
                ];
            }
            $unsubscribe_group_id = env('UNSUBSCRIBE_SENDGRID_GROUP_ID');
            // Build JSON payload
            $data = [
                "personalizations" => $personalizations,
                "from" => [
                    "email" => $from_email,
                    "name"  => $from_email_name  // optional sender name
                ],
                "template_id" => $templateId,
                "asm" => [
                    "group_id" => (int) $unsubscribe_group_id
                ]
            ];
            $sendgridApiKey = env('SENDGRID_API_KEY');
            // Init curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $sendgridApiKey,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
            }
            curl_close($ch);
            // Debug / return response
            if (isset($error_msg)) {
                return $this->response->setJSON(["error" => $error_msg]);
            } else {
                //save campaign into the airtable database
                $airtable = new Airtable();
                // Collect values from POST request
                $campaign_data = [
                    'Campaign Name'   => $this->request->getPost('campaignName'),
                    'Sender'          => $from_email,
                    'Recipient Group' => $this->request->getPost('ReceipeintGroupId'),
                    'Template ID'     => $templateId,
                    'Subject'         => $email_subject,
                    'Body'            => '',
                    'Status'          => 'Sent',
                    'emails_sent'     => $total_email_sent,
                    'recipient_group_name' => $this->request->getPost('ReceipeintGroupName')
                ];
                // Create record in Airtable "campaigns" table
                $airtableRecord = $airtable->createRecord('campaigns', $campaign_data);
                return $this->response->setJSON([
                    "status" => $httpCode,
                    "response" => $response,
                    "message" => 'Success! Your emails have been delivered to '.$total_email_sent.' recipients.'
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception and return a server error response
            log_message('error', 'SendGrid error: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred: ' . $e->getMessage());
        }
    }
    
    /**
     * Handles the bulk email sending via an Excel file upload.
     * It receives an Excel file and a templateId from the client and sends them as a single batch.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function send_bulk_emails()
    {
        // Make sure the script doesn't timeout
        ini_set('max_execution_time', 0); // 0 = unlimited
        set_time_limit(0);
        // Bump memory in case many emails
        ini_set('memory_limit', '512M');
        try {
            $batchSize = 1000;
            $total_email_sent = 0;
            $total_email_failed = 0;
            $responses = [];
            // Get the uploaded file instance. Assuming the file input name is 'emails_file'.
            $templateId = $this->request->getPost('templateSelect');
            //Get the email subject line here
            $email_subject = $this->request->getPost('emailSubject');
            //Get the from email id here
            $from_email = $this->request->getPost('fromEmailSelect');
            //Get the from email name
            $from_email_name = $this->request->getPost('fromEmailName');
            $upload_group_file_name = $this->request->getPost('fromReceipeintGroup');
            $filePath = WRITEPATH . 'uploads/excels/' . $upload_group_file_name;
            // Validate that the file and templateId are present
            if (!file_exists($filePath)) {
                return $this->failValidationError('Invalid or missing file upload.');
            }
            if (!$templateId) {
                return $this->failValidationError('Missing email template in the request.');
            }           
            // Get the temporary path of the uploaded file
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray();
            // Check if there are any rows to process
            if (empty($rows)) {
                return $this->failValidationError('The uploaded Excel file is empty.');
            }
            // We'll skip the first row, assuming it's a header row
            $emails = [];
            foreach (array_slice($rows, 1) as $row) {
                $email = isset($row[1]) ? trim($row[1]) : null; // second column
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            // $total_email_sent = (int) count($emails);
            $url = env('SENDGRID_API_URL');
            $unsubscribe_group_id = env('UNSUBSCRIBE_SENDGRID_GROUP_ID');
            $sendgridApiKey = env('SENDGRID_API_KEY');
            $chunks = array_chunk($emails, $batchSize);
            foreach ($chunks as $chunk) {
                //Was having issue with the receipient as the receipient was getting display all to emails
                $personalizations = [];
                foreach ($chunk as $email) {
                    $personalizations[] = [
                        "to" => [
                            ["email" => $email]
                        ],
                        "dynamic_template_data" => [
                            "subject" => $email_subject,
                            "name" => "Vishal"
                        ]
                    ];
                }
                $data = [
                    "personalizations" => $personalizations,
                    "from" => [
                        "email" => $from_email,
                        "name"  => $from_email_name
                    ],
                    "template_id" => $templateId,
                    "asm" => [
                        "group_id" => (int) $unsubscribe_group_id
                    ]
                ];
                // Init curl
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $sendgridApiKey,
                    "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch)) {
                    $responses[] = ["error" => curl_error($ch), 'status' => $httpCode];
                    $total_email_failed += count($chunk);
                } else {
                    $responses[] = ["status" => $httpCode, "response" => $response];
                    $total_email_sent += count($chunk);
                }
                curl_close($ch);
                // Add small delay only if total emails > 1000
                if (count($emails) > $batchSize) {
                    usleep(200000); // 0.2s pause
                }
            }
            // Debug / return response
            if ($total_email_sent == 0) {
                return $this->response->setJSON([
                    "error" => "No emails sent. All requests failed.",
                    "details" => $responses
                ]);
            } else {
                //save campaign into the airtable database
                $airtable = new Airtable();
                // Collect values from POST request
                $campaign_data = [
                    'Campaign Name'   => $this->request->getPost('campaignName'),
                    'Sender'          => $from_email,
                    'Recipient Group' => $this->request->getPost('ReceipeintGroupId'),
                    'Template ID'     => $templateId,
                    'Subject'         => $email_subject,
                    'Body'            => '',
                    'Status'          => 'Sent',
                    'emails_sent'     => $total_email_sent,
                    'emails_failed'     => $total_email_failed,
                    'recipient_group_name' => $this->request->getPost('ReceipeintGroupName')
                ];
                // Create record in Airtable "campaigns" table
                $airtableRecord = $airtable->createRecord('campaigns', $campaign_data);

                $message = "Finished! Sent {$total_email_sent} emails.";
                if ($total_email_failed > 0) {
                    $message .= " Failed {$total_email_failed}.";
                }
                return $this->response->setJSON([
                    "status" => $httpCode,
                    "response" => $response,
                    "message"  => $message,
                    "details"  => $responses
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception and return a server error response
            log_message('error', 'SendGrid error: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function get_recipient_groups()
    {
        //Fetch the records from the airtable as per the need
        $airtable = new Airtable();
        $receipient_groups = $airtable->getRecords('recipient_group');
        return $this->response->setJSON($receipient_groups);
    }

    public function get_senders_list()
    {
        //Fetch the records from the airtable as per the need
        $airtable = new Airtable();
        $filter = "OR({Send_From_Email}=\"service@mobilefuneralservice.com\",{Send_From_Email}=\"service@resthavenbatonrouge.com\")";
        $senders_list = $airtable->getRecords('email_domains',$filter);
        return $this->response->setJSON($senders_list);
    }

    public function get_campaigns_list($offset = null)
    {
        // Sorting
        $sort = 'pageSize=20&sort[0][field]=Created%20At&sort[0][direction]=desc';
        $endpoint = 'campaigns?' . $sort;

        // Add pagination offset if provided
        $offset = $this->request->getGet('offset'); // safe way
        if (!empty($offset)) {
            $endpoint .= '&offset=' . urlencode($offset);
        }

        $airtable = new Airtable();
        $campaigns_list = $airtable->getRecords($endpoint);

        $response = [];
        $campaign_data = [];

        if (isset($campaigns_list['records']) && !empty($campaigns_list['records'])) {
            foreach ($campaigns_list['records'] as $campaign) {
                // Format created time
                $dt_created = new \DateTime($campaign['createdTime'], new \DateTimeZone("UTC"));
                $campaign_created_time = $dt_created ? $dt_created->format('M d, y h:i A') : $campaign['createdTime'];
                $campaign_data[] = [
                    'id' => $campaign['id'] ?? 0,
                    'campaign_name' => isset($campaign['fields']['Campaign Name']) ? ucfirst($campaign['fields']['Campaign Name']) : 'Not available',
                    'sender' => $campaign['fields']['Sender'] ?? 'Not available',
                    'recipient_group_name' => isset($campaign['fields']['recipient_group_name']) ? ucfirst($campaign['fields']['recipient_group_name']) : 'Not available',
                    'emails_sent' => isset($campaign['fields']['emails_sent']) ? number_format($campaign['fields']['emails_sent']) : 0,
                    'created_datetime' => $campaign_created_time,
                    'email_subject' => $campaign['fields']['Subject'] ?? 'Not available',
                ];
            }
            $response['status'] = 'success';
            $response['data'] = $campaign_data;

            // Pass the offset back if more records exist
            if (isset($campaigns_list['offset'])) {
                $response['next_offset'] = $campaigns_list['offset'];
            } else {
                $response['next_offset'] = null; // No more records
            }
        } else {
            $response['status'] = 'error';
            $response['data'] = [];
            $response['next_offset'] = null;
        }
        return $this->response->setJSON($response);
    }

    public function excelUpload()
    {
        // increase limits before file handling
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('memory_limit', '256M'); // optional for large Excel parsing
        try {
            $recipient_group_name = $this->request->getPost('groupName');
            $airtable = new Airtable();
            // Get the uploaded file
            $file = $this->request->getFile('excelFile'); // "excelFile" must match FormData key
        
            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'No file uploaded or file is invalid'
                ]);
            }
            // Validate extension
            $validExt = ['xls', 'xlsx'];
            $ext = $file->getExtension();
        
            if (!in_array($ext, $validExt)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Invalid file type. Only Excel files allowed.'
                ]);
            }
            // Original filename without extension
            $originalName = pathinfo($file->getName(), PATHINFO_FILENAME);
            // Extension (ex: xlsx, csv, pdf)
            $extension = $file->getExtension();
            // Generate 5 random characters
            $randomString = substr(bin2hex(random_bytes(3)), 0, 5);
            // New name = original + _ + random + .ext
            $newName = $originalName . '_' . $randomString . '.' . $extension;
            // Move file to uploads
            $file->move(WRITEPATH . 'uploads/excels', $newName);
            
            //get the total number of records
            // Get the temporary path of the uploaded file
            $filePath = WRITEPATH . 'uploads/excels/' . $newName;
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray();
            // We'll skip the first row, assuming it's a header row
            $emails = [];
            foreach (array_slice($rows, 1) as $row) {
                $email = isset($row[1]) ? trim($row[1]) : null; // second column
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            // Create record directly into Airtable "recipient_group" table
            $airtableRecord = $airtable->createRecord('recipient_group', [
                'name'              => $recipient_group_name,
                'number_of_emails' => (int) count($emails),
                'filename'         => $newName,
                'status'           => 'Done',
            ]);
            if (!empty($airtableRecord['id'])) {
                return $this->response->setJSON([
                    'success'      => true,
                    'message'      => count($emails).' emails uploaded for '.$recipient_group_name,
                    'airtable_id'  => $airtableRecord['id'],
                    'airtable_rec' => $airtableRecord,
                ]);
            }else{
                // Failure (return error from Airtable if available)
                $errorMessage = $airtableRecord['error']['message'] ?? $airtableRecord['message'] ?? 'Unknown error creating record in Airtable';
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMessage,
                    'airtable_response' => $airtableRecord
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unexpected error: ' . $e->getMessage()
            ]);
        }
    }
}
