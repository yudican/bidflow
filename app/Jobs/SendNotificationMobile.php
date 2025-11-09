<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationMobile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $notification_data;
    protected $devices_ids;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($notification_data = [], $devices_ids = null, $data = [])
    {
        $this->notification_data = $notification_data;
        $this->devices_ids = $devices_ids;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

        // Define the endpoint URL
        $url = 'https://notification-service-nine.vercel.app/send-notification';

        // Define the payload data
        $data = [
            'notification' => [
                'title' => $this->notification_data['title'],
                'body' => str_replace('&nbsp;', ' ', strip_tags($this->notification_data['body'])),
            ],
            "webpush" =>  [
                "notification" => [
                    "icon" => "https://dev.flimty.co/favicon.ico"
                ],
            ],
            "data" => $this->data,
            'token' => $this->devices_ids
        ];

        try {
            // Send POST request with the payload
            $response = $client->post($url, [
                'json' => $data
            ]);

            // Get the response body
            $body = $response->getBody();

            // Output the response body
            setSetting('send_notification', json_encode($body));
        } catch (RequestException $e) {
            // Handle request errors
            if ($e->hasResponse()) {
                // Get the response body if available
                $responseBody = $e->getResponse()->getBody();
                echo 'Error: ' . $responseBody;
            } else {
                echo 'Error: ' . $e->getMessage();
            }
        }
    }
}
