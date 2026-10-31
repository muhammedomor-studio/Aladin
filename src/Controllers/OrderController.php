<?php
namespace App\Controllers;
use App\DropBazarClient;
use App\ApiException;

class OrderController {
    private static function guarded(callable $fn): void {
        try { \response_json($fn()); }
        catch (ApiException $e) { \response_json(['success'=>false,'message'=>$e->getMessage(),'upstream'=>$e->payload], $e->status >= 400 && $e->status < 600 ? $e->status : 502); }
        catch (\Throwable $e) { \response_json(['success'=>false,'message'=>'Backend error. Check Render logs.'], 502); }
    }
    private static function orderBody(array $input): array {
        $required = ['shipping_name','shipping_phone','shipping_address','district_id','thana_id','cart_items'];
        foreach ($required as $key) if (!isset($input[$key]) || $input[$key] === '' || $input[$key] === []) {
            \response_json(['success'=>false,'message'=>"Missing required field: {$key}"], 422);
        }
        if (!is_array($input['cart_items']) || count($input['cart_items']) < 1) {
            \response_json(['success'=>false,'message'=>'cart_items must contain at least one item.'], 422);
        }
        $items = [];
        foreach ($input['cart_items'] as $item) {
            if (!isset($item['product_id'], $item['quantity'], $item['reseller_price'])) {
                \response_json(['success'=>false,'message'=>'Each cart item requires product_id, quantity and reseller_price.'], 422);
            }
            $items[] = [
                'product_id'=>(int)$item['product_id'],
                'quantity'=>max(1,(int)$item['quantity']),
                'reseller_price'=>(int)$item['reseller_price'],
                ...(!empty($item['variant']) && is_array($item['variant']) ? ['variant'=>$item['variant']] : [])
            ];
        }
        return [
            'external_order_id'=>substr((string)($input['external_order_id'] ?? ('BLOGGER-' . bin2hex(random_bytes(8)))),0,64),
            'shipping_name'=>trim((string)$input['shipping_name']),
            'shipping_phone'=>trim((string)$input['shipping_phone']),
            'shipping_address'=>trim((string)$input['shipping_address']),
            'district_id'=>(int)$input['district_id'],
            'thana_id'=>(int)$input['thana_id'],
            'shipping_charge'=>max(0,(int)($input['shipping_charge'] ?? 0)),
            'adv_charge'=>max(0,(int)($input['adv_charge'] ?? 0)),
            'discount_amount'=>max(0,(int)($input['discount_amount'] ?? 0)),
            'reseller_note'=>substr(trim((string)($input['reseller_note'] ?? '')),0,1000),
            'cart_items'=>$items
        ];
    }
    public static function preview(): void {
        self::guarded(fn() => (new DropBazarClient())->post('orders/preview', self::orderBody(\request_json())));
    }
    public static function place(): void {
        self::guarded(function() {
            $body = self::orderBody(\request_json());
            return (new DropBazarClient())->post('orders', $body);
        });
    }
    public static function status(string $id): void {
        self::guarded(fn() => (new DropBazarClient())->get('orders/' . rawurlencode($id)));
    }
}
