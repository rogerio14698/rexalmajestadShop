<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\InvoiceReportController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\PaypalWebhookController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PrivacyRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShippingInquiryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');
Route::redirect('/inicio', '/')->name('home.legacy');
Route::view('/sobre-mi', 'public.about')->name('about');
Route::get('/exposiciones', [ExhibitionController::class, 'index'])->name('exhibitions.index');
Route::get('/exposiciones/{slug}', [ExhibitionController::class, 'show'])->name('exhibitions.show');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/contacto', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contacto', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/tienda', [ShopController::class, 'index'])->name('shop.index');

// Portfolio: la historia de la artista (secciones editables desde el admin).
Route::get('/portfolio', PortfolioController::class)->name('portfolio');

// Catálogo de obras por categoría (las categorías se gestionan desde el admin).
Route::get('/obras/{category}', [ArtworkController::class, 'index'])->name('artworks.category');
Route::get('/obras/{category}/{slug}', [ArtworkController::class, 'show'])->name('artworks.show');

// Redirecciones de las URLs antiguas /portfolio/{categoria}[/{obra}].
Route::get('/portfolio/{category}/{slug}', fn (string $category, string $slug) => redirect()->route('artworks.show', compact('category', 'slug'), 301));
Route::get('/portfolio/{category}', fn (string $category) => redirect()->route('artworks.category', compact('category'), 301));
Route::get('/carrito', [CartController::class, 'show'])->name('cart.show');
Route::post('/carrito/obras/{artwork}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrito/articulos/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrito/articulos/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/checkout/completado', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/pagos/paypal/retorno/{number}', [CheckoutController::class, 'capturePaypal'])->name('payments.paypal.return');
Route::post('/pagos/stripe/webhook', StripeWebhookController::class)->name('payments.stripe.webhook');
Route::post('/pagos/paypal/webhook', PaypalWebhookController::class)->name('payments.paypal.webhook');
Route::get('/obras/{category}/{slug}/consulta-envio', [ShippingInquiryController::class, 'create'])
    ->name('shipping-inquiry.create');
Route::post('/obras/{category}/{slug}/consulta-envio', [ShippingInquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('shipping-inquiry.store');
Route::middleware(['auth', 'verified'])->prefix('cuenta')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/pedidos', [AccountController::class, 'orders'])->name('orders');
    Route::get('/pedidos/{order}', [AccountController::class, 'order'])->name('orders.show');
    Route::get('/favoritos', [AccountController::class, 'favorites'])->name('favorites');
    Route::get('/facturas', [AccountController::class, 'invoices'])->name('invoices');
    Route::get('/direcciones', [AddressController::class, 'index'])->name('addresses');
    Route::post('/direcciones', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/direcciones/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/direcciones/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/datos-personales', [AccountController::class, 'export'])->name('export');
    Route::post('/solicitudes-privacidad', [PrivacyRequestController::class, 'store'])->name('privacy.store');
});
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware('auth')->prefix('admin/exports')->name('admin.exports.')->group(function () {
    Route::get('/pedidos.csv', [AdminExportController::class, 'orders'])->name('orders');
    Route::get('/obras.csv', [AdminExportController::class, 'artworks'])->name('artworks');
    Route::get('/iva.csv', InvoiceReportController::class)->name('vat');
});
Route::get('/admin/pedidos/{order}', [AdminOrderController::class, 'show'])
    ->middleware('auth')
    ->name('admin.orders.show');
Route::get('/cuenta/facturas/{invoice}/pdf', InvoicePdfController::class)
    ->middleware(['auth', 'verified'])
    ->name('invoices.download');
require __DIR__ . '/auth.php';
Route::get('/legal/{slug}', [LegalDocumentController::class, 'show'])->name('legal.page');
Route::post('/obras/{artwork}/favorito', [FavoriteController::class, 'toggle'])
    ->middleware('auth')
    ->name('favorites.toggle');
