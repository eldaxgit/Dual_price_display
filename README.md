# WooCommerce Dual Price Display

Show prices in Euro alongside Bulgarian lev for WooCommerce shops — in line with the legal requirements introduced in Bulgaria ahead of joining the Eurozone on 01.01.2026.

## Features

- Displays prices in EUR alongside BGN
- Uses the official fixed exchange rate (1.95583 BGN/EUR)
- Applies on the frontend: product pages, cart, checkout, order confirmation emails, and account order overview
- Works with the [Speedy And Econt Shipping](https://wordpress.org/support/plugin/speedy-econt-shipping/) plugin.

## Installation

1. Copy the code from `functions.php` into your theme’s `functions.php` file or child theme
2. Clear cache if you’re using any caching plugins
3. Reload your site to verify that dual pricing is active

## Customization

To change the exchange rate or use a different currency, adjust the logic in `functions.php`:

```php
$exchange_rate = 1.95583;
```

## License

Open source under the MIT License — feel free to use, share, and improve it
Built with the help of AI to support shop owners and developers ahead of Bulgaria's Eurozone transition ✨
