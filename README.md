# DropBazarBD → Blogger PHP Backend (Render)

Secure PHP proxy backend for a Blogger storefront. It keeps your DropBazarBD API token on the server and exposes selected catalog, location, order preview, order creation and order-status endpoints.

## Deploy on Render (GitHub flow)

1. Upload this project folder to a new GitHub repository (root files must include `Dockerfile`, `index.php`, `render.yaml`).
2. Render → New → Blueprint (or New Web Service) → connect the repository.
3. If using Blueprint, Render reads `render.yaml`. Confirm service creation.
4. Render service → Environment. Set:
   - `DROPBAZAR_API_TOKEN` = your private DropBazarBD token (never put it in Blogger)
   - `SHOP_NAME` = your shop name
   - `SHOP_PROFIT_PERCENT` = default markup percentage (frontend can use it; API order must send reseller_price)
   - `ALLOWED_ORIGINS` = `*` temporarily, or comma-separated exact Blogger origins such as `https://yourblog.blogspot.com,https://www.yourdomain.com`
5. Deploy. Test `https://YOUR-SERVICE.onrender.com/health` and `/`.
6. Keep the service URL for Blogger integration.

## Backend endpoints

- `GET /health`
- `GET /api/products?search=&category_id=&page=1&per_page=30&updated_since=`
- `GET /api/products/{id}`
- `GET /api/categories`
- `GET /api/districts`
- `GET /api/districts/{id}/thanas`
- `POST /api/order-preview`
- `POST /api/orders`
- `GET /api/orders/{id}`

The order body follows DropBazarBD Partner API. `cart_items` requires `product_id`, `quantity`, `reseller_price`; variants are required by DropBazarBD for variant products. IDs must be actual upstream IDs. `reseller_price` is in integer BDT and cannot be below the upstream cost price.

## Five-minute catalog sync

This backend intentionally reads the live catalog through DropBazarBD and does not pretend a cron runs inside Render's free web service. To cache/import catalog snapshots every 5 minutes, configure an external scheduler (e.g. GitHub Actions) to call a protected sync endpoint. This initial package does **not** include a public sync endpoint, since exposing one without authentication would be unsafe. For now, storefront calls retrieve current data from the upstream API. A later phase can add cache storage and a scheduler with a shared secret.

## Important

- API keys are server-side only.
- Set `ALLOWED_ORIGINS` to your Blogger URL before production.
- Render free services may sleep; first request after inactivity can take time.
- This proxy is the backend foundation; Blogger theme/cart UI is a separate next phase.
