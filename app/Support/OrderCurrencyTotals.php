<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderCurrencyTotals
{
    /**
     * Soma valores de pedidos completed agrupados por moeda (sem misturar moedas).
     *
     * Agrega no SQL (1–2 queries) em vez de hidratar todos os pedidos completed em memória.
     *
     * @param  Builder<Order>  $statsQuery  Query já filtrada (tenant, período, etc.)
     * @return list<array{currency: string, total: float}>
     */
    public static function valorPorMoedaFromQuery(Builder $statsQuery): array
    {
        $hasCurrencyColumn = Schema::hasTable('orders') && Schema::hasColumn('orders', 'currency');
        $currencyExpr = $hasCurrencyColumn
            ? "COALESCE(NULLIF(UPPER(TRIM(orders.currency)), ''), 'BRL')"
            : "'BRL'";

        $perOrder = (clone $statsQuery)
            ->reorder()
            ->where('orders.status', 'completed');

        // Evita SELECT * / ORDER BY / LIMIT herdados do clone (conflitam com GROUP BY).
        $perOrder->getQuery()->columns = null;
        $perOrder->getQuery()->groups = null;
        $perOrder->getQuery()->orders = null;
        $perOrder->getQuery()->limit = null;
        $perOrder->getQuery()->offset = null;
        $perOrder->getQuery()->unionOrders = null;

        $perOrder
            ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw(
                "orders.id as order_agg_id, {$currencyExpr} as currency_code, ".
                'COALESCE(SUM(order_items.amount), MAX(orders.amount)) as order_total'
            )
            ->groupByRaw($hasCurrencyColumn ? 'orders.id, orders.currency' : 'orders.id');

        $rows = DB::query()
            ->fromSub($perOrder->toBase(), 'order_currency_totals')
            ->selectRaw('currency_code, SUM(order_total) as total')
            ->groupBy('currency_code')
            ->orderBy('currency_code')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'currency' => (string) ($row->currency_code ?: 'BRL'),
                'total' => round((float) $row->total, 2),
            ];
        }

        return $out;
    }
}
