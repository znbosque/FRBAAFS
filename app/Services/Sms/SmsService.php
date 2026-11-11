<?php

namespace App\Services\Sms;

use App\Models\Attendance;
use App\Models\SmsLog;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS to a parent's phone number and log the attempt.
     */
    public function send(string $phone, string $message, ?Student $student = null, ?Attendance $attendance = null): SmsLog
    {
        $driver = (string) config('services.sms.driver', 'log');

        $log = SmsLog::create([
            'student_id' => $student?->id,
            'attendance_id' => $attendance?->id,
            'phone' => $phone,
            'message' => $message,
            'driver' => $driver,
            'status' => 'pending',
        ]);

        try {
            $response = match ($driver) {
                'twilio' => $this->sendViaTwilio($phone, $message),
                'http' => $this->sendViaHttp($phone, $message),
                default => $this->sendViaLog($phone, $message),
            };

            $log->update([
                'status' => 'sent',
                'response' => is_string($response) ? mb_substr($response, 0, 2000) : json_encode($response),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'response' => mb_substr($e->getMessage(), 0, 2000),
            ]);
        }

        return $log->refresh();
    }

    /**
     * Log driver: writes the SMS to the application log. Useful for
     * development and for demoing the flow without provider credentials.
     */
    protected function sendViaLog(string $phone, string $message): string
    {
        Log::channel(config('logging.default'))
            ->info("[SMS] to={$phone} from={$this->from()} message={$message}");

        return 'logged';
    }

    /**
     * Twilio driver: sends a real SMS through the Twilio REST API.
     */
    protected function sendViaTwilio(string $phone, string $message): string
    {
        $config = config('services.sms.twilio');

        $response = Http::asForm()
            ->withBasicAuth((string) $config['sid'], (string) $config['token'])
            ->acceptJson()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages.json", [
                'To' => $phone,
                'From' => $config['from'] ?? $this->from(),
                'Body' => $message,
            ]);

        $response->throw();

        return $response->body();
    }

    /**
     * HTTP driver: posts {to, from, message} JSON to a custom gateway URL,
     * so any SMS provider exposing a simple endpoint can be plugged in.
     */
    protected function sendViaHttp(string $phone, string $message): string
    {
        $url = (string) config('services.sms.http.url');

        $response = Http::post($url, [
            'to' => $phone,
            'from' => $this->from(),
            'message' => $message,
        ]);

        $response->throw();

        return $response->body();
    }

    protected function from(): string
    {
        return (string) config('services.sms.from', 'FRBAAFS');
    }
}
