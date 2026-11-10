# Simula la compra de la siguiente obra disponible para admin:
php artisan shop:simulate-purchase

# Simular la compra de una obra concreta por su ID:
php artisan shop:simulate-purchase --artwork=3

# Simular la compra con PayPal en lugar de Stripe:
php artisan shop:simulate-purchase --provider=paypal