<?php

abstract class ArvandPaymentSystem {
    protected string $gateway_title;
    protected string $callback_url;
    protected array $config = [];

    public function __construct(array $config = []) {
        $this->config = $config;
        $this->callback_url = home_url('/arvand-payment-callback'); // یا route اختصاصی
    }

    abstract public function process_payment(float $amount, int $order_id): array;

    abstract public function redirect_to_gateway(string $transaction_id);

    abstract public function handle_callback(array $request): array;

	protected function log(string $message, array $context = []): void {
        // اینجا می‌تونی به فایل، دیتابیس یا WC_Logger لاگ کنی
        error_log("[{$this->gateway_title}] $message " . json_encode($context));
    }
}
