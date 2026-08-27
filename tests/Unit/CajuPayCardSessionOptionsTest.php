<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\CajuPayCardSessionOptions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CajuPayCardSessionOptionsTest extends TestCase
{
    #[Test]
    public function returns_empty_for_non_card_methods(): void
    {
        $config = array_replace_recursive(Product::defaultCheckoutConfig(), [
            'card_installments' => ['enabled' => true, 'max' => 6],
            'cajupay_card' => ['require_threeds' => true],
        ]);

        $this->assertSame([], CajuPayCardSessionOptions::fromCheckoutConfig($config, 'apple_pay'));
        $this->assertSame([], CajuPayCardSessionOptions::fromCheckoutConfig($config, 'google_pay'));
    }

    #[Test]
    public function builds_installments_and_threeds_for_card(): void
    {
        $config = array_replace_recursive(Product::defaultCheckoutConfig(), [
            'card_installments' => ['enabled' => true, 'max' => 6],
            'cajupay_card' => ['require_threeds' => true],
        ]);

        $options = CajuPayCardSessionOptions::fromCheckoutConfig($config, 'card');

        $this->assertTrue($options['allow_card_installments']);
        $this->assertSame(6, $options['card_max_installments']);
        $this->assertTrue($options['require_card_threeds']);
    }

    #[Test]
    public function omits_installments_when_max_is_one(): void
    {
        $config = array_replace_recursive(Product::defaultCheckoutConfig(), [
            'card_installments' => ['enabled' => true, 'max' => 1],
            'cajupay_card' => ['require_threeds' => false],
        ]);

        $options = CajuPayCardSessionOptions::fromCheckoutConfig($config, 'card');

        $this->assertArrayNotHasKey('allow_card_installments', $options);
        $this->assertArrayNotHasKey('require_card_threeds', $options);
    }

    #[Test]
    public function draft_snapshot_normalizes_flags(): void
    {
        $snapshot = CajuPayCardSessionOptions::draftSnapshot([
            'allow_card_installments' => true,
            'card_max_installments' => 12,
            'require_card_threeds' => true,
        ]);

        $this->assertSame([
            'allow_card_installments' => true,
            'card_max_installments' => 12,
            'require_card_threeds' => true,
        ], $snapshot);
    }
}
