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
            // 'paytype' => '111',
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
     * Get payment URL for redirect (GET method - deprecated, use getPaymentParams for POST)
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
     * Get payment parameters for POST form submission
     *
     * @param Order $order
     * @param string $paymentMethod
     * @param string $userIp
     * @return array
     */
    public function getPaymentParams(Order $order, string $paymentMethod, string $userIp): array
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

        return [
            'url' => $this->apiUrl . '/trade/pay',
            'params' => $params,
        ];
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
        // Remove empty values and sign field itself
        $params = array_filter($params, function ($value, $key) {
            return $value !== '' && $value !== null && $key !== 'sign';
        }, ARRAY_FILTER_USE_BOTH);

        // Sort by key
        ksort($params);

        // Build sign string - join with & separator
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . $value;
        }
        $signString = implode('&', $parts) . '&key=' . $this->apiKey;

        // Try different variations
        $variations = [
            'with_key_upper' => strtoupper(md5($signString)),
            'with_key_lower' => strtolower(md5($signString)),
            'direct_append_upper' => strtoupper(md5(implode('&', $parts) . $this->apiKey)),
            'direct_append_lower' => strtolower(md5(implode('&', $parts) . $this->apiKey)),
        ];

        // Log for debugging
        Log::info('Payment signature generation - all variations', [
            'params' => $params,
            'sign_string' => $signString,
            'variations' => $variations
        ]);

        // Generate MD5 - try with &key= and lowercase
        return strtolower(md5($signString));
    }

    /**
     * Get payment type code
     *
     * @param string $method
     * @return string
     */
    protected function getPayType(string $method): string
    {
        // Common payment type codes used by Chinese payment gateways
        $types = [
            'alipay' => '111',      // or 'ALIPAY', 'ALI', 'zfb'
            'wechat' => '111',       // or 'WECHAT', 'WX', 'weixin'
            // 'bank' => 'bank',          // or 'BANK'
        ];

        $paytype = $types[$method] ?? '111';

        Log::info('Payment type mapping', [
            'input_method' => $method,
            'output_paytype' => $paytype
        ]);

        return $paytype;
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
