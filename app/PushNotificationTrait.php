<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use PHPMailer\PHPMailer\Exception;
use ExpoSDK\ExpoMessage;
use ExpoSDK\Expo;
use Illuminate\Support\Facades\Http;

trait PushNotificationTrait
{

    public function pushNotification($title, $body, $token)
    {

        $serverKey = 'AIzaSyCZkJHtPrRq3qjf4N2e1MusP0MWunHjRyE';
        $deviceToken = $token;

        $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]);

        // You can then check the response as needed
        if ($response->successful()) {
            // Request was successful
            return $responseData = $response->json();
            // Handle the response data
        } else {
            // Request failed
            return $errorData = $response->json();
            // Handle the error data
        }
    }
}
