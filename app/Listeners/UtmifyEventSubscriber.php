<?php

namespace App\Listeners;

use App\Events\BoletoGenerated;
use App\Events\OrderCompleted;
use App\Events\OrderPending;
use App\Events\OrderRefunded;
use App\Events\OrderRejected;
use App\Events\PixGenerated;
use App\Jobs\UtmifySendOrderJob;
use App\Models\UtmifyIntegration;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class UtmifyEventSubscriber
{
    /**
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            PixGenerated::class => 'handlePixGenerated',
            BoletoGenerated::class => 'handleBoletoGenerated',
            OrderPending::class => 'handleOrderPending',
            OrderCompleted::class => 'handleOrderCompleted',
            OrderRefunded::class => 'handleOrderRefunded',
            OrderRejected::class => 'handleOrderRejected',
        ];
    }

    public function handlePixGenerated(PixGenerated $event): void
    {
        $this->dispatchForOrder($event->order, 'waiting_payment');
    }

    public function handleBoletoGenerated(BoletoGenerated $event): void
    {
        $this->dispatchForOrder($event->order, 'waiting_payment');
    }

    public function handleOrderPending(OrderPending $event): void
    {
        $this->dispatchForOrder($event->order, 'waiting_payment');
    }

    public function handleOrderCompleted(OrderCompleted $event): void
    {
        $approvedAt = $event->order->updated_at->utc()->format('Y-m-d H:i:s');
        $this->dispatchForOrder($event->order, 'paid', $approvedAt, null);
    }

    public function handleOrderRefunded(OrderRefunded $event): void
    {
        $refundedAt = $event->order->updated_at->utc()->format('Y-m-d H:i:s');
        $this->dispatchForOrder($event->order, 'refunded', null, $refundedAt);
    }

    public function handleOrderRejected(OrderRejected $event): void
    {
        $this->dispatchForOrder($event->order, 'refused');
    }

    private function dispatchForOrder(
        \App\Models\Order $order,
        string $utmifyStatus,
        ?string $approvedAt = null,
        ?string $refundedAt = null
    ): void {
        $tenantId = $order->tenant_id;
        $productId = $order->product_id !== null ? (string) $order->product_id : null;

        $integrations = UtmifyIntegration::forTenant($tenantId)
            ->where('is_active', true)
            ->with('products:id')
            ->get();

        foreach ($integrations as $integration) {
            if (! $integration->api_key) {
                continue;
            }
            if (! $integration->appliesToProduct($productId)) {
                continue;
            }

            if ($this->shouldDispatchSync()) {
                UtmifySendOrderJob::dispatchSync(
                    $integration->id,
                    $order->id,
                    $utmifyStatus,
                    $approvedAt,
                    $refundedAt
                );
            } else {
                UtmifySendOrderJob::dispatch(
                    $integration->id,
                    $order->id,
                    $utmifyStatus,
                    $approvedAt,
                    $refundedAt
                );
            }
        }
    }

    private function shouldDispatchSync(): bool
    {
        if (config('queue.default') === 'sync') {
            return true;
        }

        $v = (string) env('INTEGRATIONS_DISPATCH_SYNC', '');
        if ($v !== '' && in_array(Str::lower(trim($v)), ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        return false;
    }
}
