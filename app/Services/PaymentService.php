<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected string $apiUrl;
    protected string $partnerId;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('payment.api_url');
        $this->partnerId = config('payment.partner_id');
        $this->apiKey = config('payment.api_key');
    }

    /**
     * Create a payment request
     *
     * @param Order $order
     * @param string $paymentMethod 'alipay' or 'wechat'
     * @param string $userIp
     * @return array
     */
    public function createPayment(Order $order, string $paymentMethod, string $userIp): array
    {
        $params = [
            'version' => '1.0',
            'partnerid' => $this->partnerId,
            'orderid' => $order->order_number,
            'payamount' => (int)($order->amount * 100), // Convert to cents
            'payip' => $userIp,
            'notifyurl' => route('payment.callback'),
            'returnurl' => route('payment.return'),
            'paytype' => $this->getPayType($paymentMethod),
        ];

        // Generate signature
        $params['sign'] = $this->generateSignature($params);

        try {
            // POST request returns JSON
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl . '/trade/pay', $params);

            if ($response->successful()) {
                $result = $response->json();

                Log::info('Payment request created', [
                    'order_number' => $order->order_number,
                    'response' => $result
                ]);

                return [
                    'success' => true,
                    'data' => $result
                ];
            }

            Log::error('Payment request failed', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Payment request failed'
            ];
        } catch (\Exception $e) {
            Log::error('Payment request exception', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment URL for redirect
     *
     * @param Order $order
     * @param string $paymentMethod
     * @param string $userIp
     * @return string|null
     */
    public function getPaymentUrl(Order $order, string $paymentMethod, string $userIp): ?string
    {
        $params = [
            'version' => '1.0',
            'partnerid' => $this->partnerId,
            'orderid' => $order->order_number,
            'payamount' => (int)($order->amount * 100), // Convert to cents
            'payip' => $userIp,
            'notifyurl' => route('payment.callback'),
            'returnurl' => route('payment.return'),
            'paytype' => $this->getPayType($paymentMethod),
        ];

        // Generate signature
        $params['sign'] = $this->generateSignature($params);

        // Build GET URL
        return $this->apiUrl . '/trade/pay?' . http_build_query($params);
    }

    /**
     * Verify callback signature
     *
     * @param array $params
     * @return bool
     */
    public function verifyCallback(array $params): bool
    {
        if (!isset($params['sign'])) {
            return false;
        }

        $sign = $params['sign'];
        unset($params['sign']);

        $expectedSign = $this->generateSignature($params);

        return $sign === $expectedSign;
    }

    /**
     * Generate MD5 signature
     *
     * @param array $params
     * @return string
     */
    protected function generateSignature(array $params): string
    {
        // Remove empty values
        $params = array_filter($params, function ($value) {
            return $value !== '' && $value !== null;
        });

        // Sort by key
        ksort($params);

        // Build sign string
        $signString = '';
        foreach ($params as $key => $value) {
            $signString .= $key . '=' . $value . '&';
        }

        // Append API key
        $signString .= 'key=' . $this->apiKey;

        // Generate MD5
        return strtoupper(md5($signString));
    }

    /**
     * Get payment type code
     *
     * @param string $method
     * @return string
     */
    protected function getPayType(string $method): string
    {
        $types = [
            'alipay' => 'alipay',
            'wechat' => 'wechat',
            'bank' => 'bank',
        ];

        return $types[$method] ?? 'alipay';
    }

    /**
     * Query order status
     *
     * @param string $orderNumber
     * @return array
     */
    public function queryOrder(string $orderNumber): array
    {
        $params = [
            'version' => '1.0',
            'partnerid' => $this->partnerId,
            'orderid' => $orderNumber,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl . '/trade/query', $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Query failed'
            ];
        } catch (\Exception $e) {
            Log::error('Order query exception', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
