<?php

class ArvandPaymentSystem_Zarinpal extends ArvandPaymentSystem {

    protected string $gateway_title = 'زرین‌پال';

    private string $api_url = "https://api.zarinpal.com/pg/v4/payment";

    public function process_payment(float $amount, int $order_id): array {
        $merchant_id = $this->config['merchant_id'] ?? '';
        $description = "پرداخت سفارش #{$order_id}";

        $payload = [
            "merchant_id"  => $merchant_id,
            "amount"       => intval($amount * 10), // ریال
            "callback_url" => $this->callback_url,
            "description"  => $description,
        ];

        $response = wp_remote_post("{$this->api_url}/request.json", [
            'body'    => json_encode($payload),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            $this->log('خطا در اتصال به زرین‌پال', ['error' => $response->get_error_message()]);
            return ['result' => 'failure', 'message' => 'خطا در اتصال به درگاه'];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['data']['authority']) && $body['data']['code'] == 100) {
            $transaction_id = $body['data']['authority'];
            return [
                'result'          => 'success',
                'transaction_id'  => $transaction_id,
                'redirect_url'    => "{$this->api_url}/start/{$transaction_id}"
            ];
        }

        $this->log('خطا در دریافت کد تراکنش از زرین‌پال', $body);
        return ['result' => 'failure', 'message' => 'تراکنش ناموفق'];
    }

    public function redirect_to_gateway(string $transaction_id) {
        wp_redirect("{$this->api_url}/start/{$transaction_id}");
        exit;
    }

    public function handle_callback(array $request): array {
        $authority = sanitize_text_field($request['Authority'] ?? '');
        $status    = sanitize_text_field($request['Status'] ?? '');
        $amount    = intval($request['amount'] ?? 0);

        if ($status !== 'OK') {
            return ['result' => 'failure', 'message' => 'پرداخت لغو شد توسط کاربر'];
        }

        $payload = [
            "merchant_id" => $this->config['merchant_id'],
            "amount"      => $amount,
            "authority"   => $authority
        ];

        $response = wp_remote_post("{$this->api_url}/verify.json", [
            'body'    => json_encode($payload),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return ['result' => 'failure', 'message' => 'خطا در ارتباط با زرین‌پال'];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['data']['ref_id'])) {
            return [
                'result'   => 'success',
                'ref_id'   => $body['data']['ref_id'],
                'message'  => 'پرداخت با موفقیت انجام شد'
            ];
        }

        return ['result' => 'failure', 'message' => 'پرداخت تایید نشد'];
    }
}
