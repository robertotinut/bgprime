<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $revenueToday = (int) Order::where('payment_status', Order::PAYMENT_PAID)
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $ordersToday = Order::whereDate('created_at', $today)->count();

        $needFulfillment = Order::where('payment_status', Order::PAYMENT_PAID)
            ->whereIn('fulfillment_status', [Order::FULFILLMENT_WAITING, Order::FULFILLMENT_PROCESSING])
            ->count();

        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->count();

        return [
            Stat::make('Revenue Today', 'Rp' . number_format($revenueToday, 0, ',', '.'))
                ->description('Total pendapatan terbayar hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Orders Today', (string) $ordersToday)
                ->description('Total pesanan masuk hari ini')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Need Fulfillment', (string) $needFulfillment)
                ->description('Pesanan lunas menunggu kredensial')
                ->descriptionIcon('heroicon-m-clock')
                ->color($needFulfillment > 0 ? 'warning' : 'gray'),

            Stat::make('Low Stock Products', (string) $lowStockCount)
                ->description('Produk mendekati atau habis stok')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
