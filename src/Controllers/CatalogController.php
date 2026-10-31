<?php
namespace App\Controllers;
use App\DropBazarClient;
use App\ApiException;

class CatalogController {
    private static function call(callable $fn): void {
        try { \response_json($fn()); }
        catch (ApiException $e) { \response_json(['success'=>false,'message'=>$e->getMessage(),'upstream'=>$e->payload], $e->status >= 400 && $e->status < 600 ? $e->status : 502); }
        catch (\Throwable $e) { \response_json(['success'=>false,'message'=>'Backend error. Check Render logs.'], 502); }
    }
    public static function products(): void {
        self::call(function() {
            $q = [];
            foreach (['category_id','search','updated_since','page','per_page'] as $k)
                if (isset($_GET[$k]) && $_GET[$k] !== '') $q[$k] = $_GET[$k];
            $q['per_page'] = min(100, max(1, (int)($q['per_page'] ?? 30)));
            $q['page'] = max(1, (int)($q['page'] ?? 1));
            return (new DropBazarClient())->get('products', $q);
        });
    }
    public static function product(string $id): void {
        self::call(fn() => (new DropBazarClient())->get('products/' . rawurlencode($id)));
    }
    public static function categories(): void {
        self::call(fn() => (new DropBazarClient())->get('categories'));
    }
    public static function districts(): void {
        self::call(fn() => (new DropBazarClient())->get('districts'));
    }
    public static function thanas(string $id): void {
        self::call(fn() => (new DropBazarClient())->get('districts/' . rawurlencode($id) . '/thanas'));
    }
}
