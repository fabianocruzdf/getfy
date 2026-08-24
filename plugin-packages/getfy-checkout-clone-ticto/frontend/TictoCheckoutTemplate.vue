<script setup>
import { computed } from 'vue';

const props = defineProps({
    product: { type: Object, required: true },
    subscriptionPlan: { type: Object, default: null },
    config: { type: Object, default: () => ({}) },
    appliedCoupon: { type: Object, default: null },
    selectedOrderBumps: { type: Array, default: () => [] },
    orderBumpsTotalBrl: { type: Number, default: 0 },
    t: { type: Function, default: (k) => k },
    displayCurrency: { type: [String, Object], default: 'BRL' },
    priceInCurrency: { type: Function, default: (v) => v },
    formatPrice: { type: Function, default: (v) => String(v) },
    primaryColor: { type: String, default: '#00A868' },
    productId: { type: [Number, String], required: true },
    productOfferId: { type: [Number, String], default: null },
    subscriptionPlanId: { type: [Number, String], default: null },
    checkoutSessionToken: { type: String, default: '' },
    affiliateRef: { type: String, default: '' },
    orderBumps: { type: Array, default: () => [] },
    availablePaymentMethods: { type: Array, default: () => [] },
    prefillCoupon: { type: String, default: '' },
    checkoutLocale: { type: [String, Object], default: 'pt_BR' },
    suggestedCountryCode: { type: String, default: null },
    localeStorageKey: { type: String, default: '' },
    cardPayeeCode: { type: String, default: '' },
    cardEfiSandbox: { type: Boolean, default: false },
    cardStripePublishableKey: { type: String, default: '' },
    cardStripeSandbox: { type: Boolean, default: false },
    cardStripeLinkEnabled: { type: Boolean, default: true },
    cardInstallmentsEnabled: { type: Boolean, default: false },
    cardMaxInstallments: { type: Number, default: 1 },
    cardMercadopagoPublicKey: { type: String, default: '' },
    cardMercadopagoSandbox: { type: Boolean, default: false },
    cardPaypalClientId: { type: String, default: '' },
    cardPaypalSandbox: { type: Boolean, default: false },
    cardPaypalCheckoutMode: { type: String, default: 'auto' },
    cardGatewayKeys: { type: Object, default: () => ({}) },
    checkoutTotalBrl: { type: Number, default: 0 },
    checkoutTotalInCurrency: { type: Number, default: 0 },
    mainLinePriceBrl: { type: Number, default: 0 },
    currencyList: { type: Array, default: () => [] },
    featuredCurrencies: { type: Array, default: () => [] },
    otherCurrencies: { type: Array, default: () => [] },
    pluginCheckoutExtensions: { type: Array, default: () => [] },
    productName: { type: String, default: '' },
    cajupayPayAccountId: { type: String, default: '' },
    parceladoSdkOptions: { type: Object, default: () => ({}) },
    locale: { type: [String, Object], default: 'pt_BR' },
    supportedLocales: { type: Array, default: () => [] },
    localeLabels: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'couponApplied',
    'couponCleared',
    'paymentApproved',
    'setCurrency',
    'setLocale',
    'update:orderBumpIds',
]);

const orderBumpIds = defineModel('orderBumpIds', { type: Array, default: () => [] });

function shared() {
    return (typeof window !== 'undefined' && window.__GETFY_CHECKOUT_SHARED__) || {};
}

const CheckoutFormCmp = computed(() => shared().CheckoutForm || 'div');

const accent = computed(() => {
    const fromConfig = props.config?.appearance?.primary_color;
    return fromConfig || props.primaryColor || '#00A868';
});

const reviews = computed(() => {
    const list = props.config?.reviews;
    return Array.isArray(list) ? list : [];
});

const productImage = computed(
    () => props.product?.image_url || props.product?.image || '',
);

const listPriceBrl = computed(() => Number(props.product?.price_brl ?? props.product?.price ?? 0));
const salePriceBrl = computed(() => {
    if (props.appliedCoupon?.final_price != null) {
        return Number(props.appliedCoupon.final_price);
    }
    return listPriceBrl.value;
});

const showStrike = computed(() => {
    const original = Number(props.config?.summary?.original_price ?? 0);
    return original > salePriceBrl.value;
});

const originalPriceDisplay = computed(() => {
    const original = Number(props.config?.summary?.original_price ?? 0);
    if (original > 0) {
        return props.formatPrice(props.priceInCurrency(original), props.displayCurrency);
    }
    return props.formatPrice(props.priceInCurrency(listPriceBrl.value), props.displayCurrency);
});

const salePriceDisplay = computed(() =>
    props.formatPrice(props.priceInCurrency(salePriceBrl.value), props.displayCurrency),
);

const installmentHint = computed(() => {
    if (!props.cardInstallmentsEnabled || (props.cardMaxInstallments || 1) < 2) {
        return '';
    }
    const n = props.cardMaxInstallments;
    const each = props.priceInCurrency(salePriceBrl.value) / n;
    return `${n}x de ${props.formatPrice(each, props.displayCurrency)}`;
});
</script>

<template>
    <div class="ticto-skin" :style="{ '--ticto-accent': accent }" data-checkout="ticto-clone">
        <div class="ticto-grid">
            <div class="ticto-main">
                <div class="ticto-secure">
                    <svg class="ticto-secure-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3 5 6v6c0 5 3.4 7.6 7 9 3.6-1.4 7-4 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.8" />
                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>COMPRA 100% SEGURA</span>
                </div>

                <div class="ticto-card">
                    <div v-if="product" class="ticto-mini">
                        <img
                            v-if="productImage"
                            class="ticto-mini-img"
                            :src="productImage"
                            :alt="product.name"
                        />
                        <div v-else class="ticto-mini-img ticto-mini-img--ph" />
                        <div class="ticto-mini-body">
                            <p class="ticto-mini-name">{{ product.name }}</p>
                            <p v-if="product.description" class="ticto-mini-desc">{{ product.description }}</p>
                            <p class="ticto-mini-price">
                                <span v-if="showStrike" class="ticto-strike">{{ originalPriceDisplay }}</span>
                                <strong>{{ salePriceDisplay }}</strong>
                            </p>
                            <p v-if="installmentHint" class="ticto-installments">{{ installmentHint }}</p>
                        </div>
                    </div>

                    <component
                        :is="CheckoutFormCmp"
                        :product-id="productId"
                        :product-offer-id="productOfferId"
                        :subscription-plan-id="subscriptionPlanId"
                        :checkout-session-token="checkoutSessionToken"
                        :affiliate-ref="affiliateRef"
                        :order-bumps="orderBumps"
                        v-model:order-bump-ids="orderBumpIds"
                        :primary-color="accent"
                        :config="config"
                        :available-payment-methods="availablePaymentMethods"
                        :prefill-coupon="prefillCoupon"
                        :t="t"
                        :display-currency="displayCurrency"
                        :checkout-locale="checkoutLocale"
                        :format-price="formatPrice"
                        :suggested-country-code="suggestedCountryCode"
                        :locale-storage-key="localeStorageKey"
                        :card-payee-code="cardPayeeCode"
                        :card-efi-sandbox="cardEfiSandbox"
                        :card-stripe-publishable-key="cardStripePublishableKey"
                        :card-stripe-sandbox="cardStripeSandbox"
                        :card-stripe-link-enabled="cardStripeLinkEnabled"
                        :card-installments-enabled="cardInstallmentsEnabled"
                        :card-max-installments="cardMaxInstallments"
                        :card-mercadopago-public-key="cardMercadopagoPublicKey"
                        :card-mercadopago-sandbox="cardMercadopagoSandbox"
                        :card-paypal-client-id="cardPaypalClientId"
                        :card-paypal-sandbox="cardPaypalSandbox"
                        :card-paypal-checkout-mode="cardPaypalCheckoutMode"
                        :card-gateway-keys="cardGatewayKeys"
                        :checkout-total-brl="checkoutTotalBrl"
                        :checkout-total-in-currency="checkoutTotalInCurrency"
                        :main-line-price-brl="mainLinePriceBrl"
                        :currency-list="currencyList"
                        :featured-currencies="featuredCurrencies"
                        :other-currencies="otherCurrencies"
                        :plugin-checkout-extensions="pluginCheckoutExtensions"
                        :product-name="productName"
                        :cajupay-pay-account-id="cajupayPayAccountId"
                        :parcelado-sdk-options="parceladoSdkOptions"
                        :price-in-currency="priceInCurrency"
                        @coupon-applied="emit('couponApplied', $event)"
                        @coupon-cleared="emit('couponCleared', $event)"
                        @payment-approved="emit('paymentApproved', $event)"
                        @set-currency="emit('setCurrency', $event)"
                    />
                </div>
            </div>

            <aside v-if="reviews.length" class="ticto-aside">
                <article v-for="(review, index) in reviews" :key="index" class="ticto-review">
                    <div class="ticto-review-head">
                        <img
                            v-if="review.photo"
                            class="ticto-avatar"
                            :src="review.photo"
                            :alt="review.author"
                        />
                        <div v-else class="ticto-avatar ticto-avatar--ph">
                            {{ (review.author || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="ticto-review-name">{{ review.author || 'Cliente' }}</p>
                            <div class="ticto-stars" :aria-label="`${review.stars || 5} de 5`">
                                <svg
                                    v-for="s in 5"
                                    :key="s"
                                    class="ticto-star"
                                    :class="s <= (review.stars || 5) ? 'is-on' : ''"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M12 2.5 14.9 9l6.6.9-4.8 4.6 1.2 6.6L12 17.8 6.1 21.1l1.2-6.6L2.5 9.9 9.1 9 12 2.5Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <p v-if="review.description" class="ticto-review-text">“{{ review.description }}”</p>
                </article>
            </aside>
        </div>
    </div>
</template>

<style>
.ticto-skin {
    --ticto-accent: #00a868;
    --ticto-bg: #eef2f6;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
}

.ticto-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 1024px) {
    .ticto-grid {
        flex-direction: row;
        align-items: flex-start;
        gap: 1.75rem;
    }
    .ticto-main {
        width: 66.666%;
    }
    .ticto-aside {
        width: 33.333%;
        position: sticky;
        top: 1.5rem;
    }
}

.ticto-secure {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: var(--ticto-accent);
    color: #fff;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    border-radius: 10px 10px 0 0;
    padding: 0.7rem 1rem;
}

.ticto-secure-icon {
    width: 1.1rem;
    height: 1.1rem;
}

.ticto-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-top: 0;
    border-radius: 0 0 10px 10px;
    padding: 1.25rem 1.1rem 1.5rem;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

@media (min-width: 640px) {
    .ticto-card {
        padding: 1.5rem 1.5rem 1.75rem;
    }
}

.ticto-mini {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
    padding: 0.75rem;
    margin-bottom: 1.25rem;
    border: 1px solid #eceff3;
    border-radius: 10px;
    background: #fafbfc;
}

.ticto-mini-img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}

.ticto-mini-img--ph {
    background: #e5e7eb;
}

.ticto-mini-name {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
}

.ticto-mini-desc {
    margin: 0.2rem 0 0;
    font-size: 0.75rem;
    color: #6b7280;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ticto-mini-price {
    margin: 0.35rem 0 0;
    display: flex;
    gap: 0.5rem;
    align-items: baseline;
}

.ticto-strike {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: 0.8rem;
}

.ticto-mini-price strong {
    color: var(--ticto-accent);
    font-size: 1.05rem;
}

.ticto-installments {
    margin: 0.15rem 0 0;
    color: var(--ticto-accent);
    font-size: 0.8rem;
    font-weight: 700;
}

.ticto-aside {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.ticto-review {
    background: #fff;
    border-radius: 10px;
    padding: 1rem 1.1rem;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    border: 1px solid #eef0f3;
}

.ticto-review-head {
    display: flex;
    gap: 0.7rem;
    align-items: center;
}

.ticto-avatar {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    object-fit: cover;
}

.ticto-avatar--ph {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e5e7eb;
    color: #4b5563;
    font-weight: 700;
}

.ticto-review-name {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 700;
    color: #111827;
}

.ticto-stars {
    display: flex;
    gap: 1px;
    margin-top: 0.15rem;
}

.ticto-star {
    width: 0.95rem;
    height: 0.95rem;
    color: #e5e7eb;
    fill: currentColor;
}

.ticto-star.is-on {
    color: #f5c518;
    fill: #f5c518;
}

.ticto-review-text {
    margin: 0.7rem 0 0;
    font-size: 0.875rem;
    line-height: 1.45;
    color: #374151;
}

.ticto-skin [data-checkout='form-section-dados-header'] {
    margin-bottom: 0.75rem;
}

.ticto-skin [data-checkout='form'] input[type='text'],
.ticto-skin [data-checkout='form'] input[type='email'],
.ticto-skin [data-checkout='form'] input[type='tel'],
.ticto-skin [data-checkout='form'] select {
    border-radius: 6px !important;
    border-color: #d1d5db !important;
    min-height: 44px;
}

.ticto-skin [data-checkout='form-submit'] {
    border-radius: 8px !important;
    font-weight: 800 !important;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    background: var(--ticto-accent) !important;
}

.ticto-skin [data-checkout='form-footer-desktop'] {
    display: none !important;
}
</style>
