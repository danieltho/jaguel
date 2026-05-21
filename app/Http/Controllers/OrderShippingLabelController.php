<?php

namespace App\Http\Controllers;

use App\Filament\Pages\ShippingSenderSettings;
use App\Models\Order;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderShippingLabelController extends Controller
{
    public function show(Order $order, SettingsService $settings): View
    {
        $sender = $settings->group(ShippingSenderSettings::GROUP);

        return view('shipping-label', [
            'orders' => collect([$order->load('items')]),
            'sender' => $sender,
        ]);
    }

    public function bulk(Request $request, SettingsService $settings): View
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        abort_if($ids->isEmpty(), 404);

        $orders = Order::with('items')
            ->whereIn('id', $ids)
            ->orderBy('order_number')
            ->get();

        abort_if($orders->isEmpty(), 404);

        return view('shipping-label', [
            'orders' => $orders,
            'sender' => $settings->group(ShippingSenderSettings::GROUP),
        ]);
    }
}
