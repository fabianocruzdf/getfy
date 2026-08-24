import { useModel as N, computed as l, openBlock as o, createElementBlock as n, normalizeStyle as w, createElementVNode as r, toDisplayString as d, createCommentVNode as s, createBlock as M, resolveDynamicComponent as T, Fragment as S, renderList as v, normalizeClass as O, mergeModels as I } from "vue";
const j = { class: "ticto-grid" }, U = { class: "ticto-main" }, F = { class: "ticto-card" }, G = {
  key: 0,
  class: "ticto-mini"
}, K = ["src", "alt"], R = {
  key: 1,
  class: "ticto-mini-img ticto-mini-img--ph"
}, D = { class: "ticto-mini-body" }, Y = { class: "ticto-mini-name" }, q = {
  key: 0,
  class: "ticto-mini-desc"
}, H = { class: "ticto-mini-price" }, V = {
  key: 0,
  class: "ticto-strike"
}, $ = {
  key: 1,
  class: "ticto-installments"
}, z = {
  key: 0,
  class: "ticto-aside"
}, Z = { class: "ticto-review-head" }, J = ["src", "alt"], Q = {
  key: 1,
  class: "ticto-avatar ticto-avatar--ph"
}, W = { class: "ticto-review-name" }, X = ["aria-label"], _ = {
  key: 0,
  class: "ticto-review-text"
}, ee = {
  __name: "TictoCheckoutTemplate",
  props: /* @__PURE__ */ I({
    product: { type: Object, required: !0 },
    subscriptionPlan: { type: Object, default: null },
    config: { type: Object, default: () => ({}) },
    appliedCoupon: { type: Object, default: null },
    selectedOrderBumps: { type: Array, default: () => [] },
    orderBumpsTotalBrl: { type: Number, default: 0 },
    t: { type: Function, default: (e) => e },
    displayCurrency: { type: [String, Object], default: "BRL" },
    priceInCurrency: { type: Function, default: (e) => e },
    formatPrice: { type: Function, default: (e) => String(e) },
    primaryColor: { type: String, default: "#00A868" },
    productId: { type: [Number, String], required: !0 },
    productOfferId: { type: [Number, String], default: null },
    subscriptionPlanId: { type: [Number, String], default: null },
    checkoutSessionToken: { type: String, default: "" },
    affiliateRef: { type: String, default: "" },
    orderBumps: { type: Array, default: () => [] },
    availablePaymentMethods: { type: Array, default: () => [] },
    prefillCoupon: { type: String, default: "" },
    checkoutLocale: { type: [String, Object], default: "pt_BR" },
    suggestedCountryCode: { type: String, default: null },
    localeStorageKey: { type: String, default: "" },
    cardPayeeCode: { type: String, default: "" },
    cardEfiSandbox: { type: Boolean, default: !1 },
    cardStripePublishableKey: { type: String, default: "" },
    cardStripeSandbox: { type: Boolean, default: !1 },
    cardStripeLinkEnabled: { type: Boolean, default: !0 },
    cardInstallmentsEnabled: { type: Boolean, default: !1 },
    cardMaxInstallments: { type: Number, default: 1 },
    cardMercadopagoPublicKey: { type: String, default: "" },
    cardMercadopagoSandbox: { type: Boolean, default: !1 },
    cardPaypalClientId: { type: String, default: "" },
    cardPaypalSandbox: { type: Boolean, default: !1 },
    cardPaypalCheckoutMode: { type: String, default: "auto" },
    cardGatewayKeys: { type: Object, default: () => ({}) },
    checkoutTotalBrl: { type: Number, default: 0 },
    checkoutTotalInCurrency: { type: Number, default: 0 },
    mainLinePriceBrl: { type: Number, default: 0 },
    currencyList: { type: Array, default: () => [] },
    featuredCurrencies: { type: Array, default: () => [] },
    otherCurrencies: { type: Array, default: () => [] },
    pluginCheckoutExtensions: { type: Array, default: () => [] },
    productName: { type: String, default: "" },
    cajupayPayAccountId: { type: String, default: "" },
    parceladoSdkOptions: { type: Object, default: () => ({}) },
    locale: { type: [String, Object], default: "pt_BR" },
    supportedLocales: { type: Array, default: () => [] },
    localeLabels: { type: Object, default: () => ({}) }
  }, {
    orderBumpIds: { type: Array, default: () => [] },
    orderBumpIdsModifiers: {}
  }),
  emits: /* @__PURE__ */ I([
    "couponApplied",
    "couponCleared",
    "paymentApproved",
    "setCurrency",
    "setLocale",
    "update:orderBumpIds"
  ], ["update:orderBumpIds"]),
  setup(e, { emit: u }) {
    const t = e, p = u, m = N(e, "orderBumpIds");
    function P() {
      return typeof window < "u" && window.__GETFY_CHECKOUT_SHARED__ || {};
    }
    const x = l(() => P().CheckoutForm || "div"), f = l(() => t.config?.appearance?.primary_color || t.primaryColor || "#00A868"), g = l(() => {
      const i = t.config?.reviews;
      return Array.isArray(i) ? i : [];
    }), b = l(
      () => t.product?.image_url || t.product?.image || ""
    ), k = l(() => Number(t.product?.price_brl ?? t.product?.price ?? 0)), y = l(() => t.appliedCoupon?.final_price != null ? Number(t.appliedCoupon.final_price) : k.value), B = l(() => Number(t.config?.summary?.original_price ?? 0) > y.value), A = l(() => {
      const i = Number(t.config?.summary?.original_price ?? 0);
      return i > 0 ? t.formatPrice(t.priceInCurrency(i), t.displayCurrency) : t.formatPrice(t.priceInCurrency(k.value), t.displayCurrency);
    }), E = l(
      () => t.formatPrice(t.priceInCurrency(y.value), t.displayCurrency)
    ), h = l(() => {
      if (!t.cardInstallmentsEnabled || (t.cardMaxInstallments || 1) < 2)
        return "";
      const i = t.cardMaxInstallments, c = t.priceInCurrency(y.value) / i;
      return `${i}x de ${t.formatPrice(c, t.displayCurrency)}`;
    });
    return (i, c) => (o(), n("div", {
      class: "ticto-skin",
      style: w({ "--ticto-accent": f.value }),
      "data-checkout": "ticto-clone"
    }, [
      r("div", j, [
        r("div", U, [
          c[5] || (c[5] = r("div", { class: "ticto-secure" }, [
            r("svg", {
              class: "ticto-secure-icon",
              viewBox: "0 0 24 24",
              fill: "none",
              "aria-hidden": "true"
            }, [
              r("path", {
                d: "M12 3 5 6v6c0 5 3.4 7.6 7 9 3.6-1.4 7-4 7-9V6l-7-3Z",
                stroke: "currentColor",
                "stroke-width": "1.8"
              }),
              r("path", {
                d: "m9 12 2 2 4-4",
                stroke: "currentColor",
                "stroke-width": "1.8",
                "stroke-linecap": "round",
                "stroke-linejoin": "round"
              })
            ]),
            r("span", null, "COMPRA 100% SEGURA")
          ], -1)),
          r("div", F, [
            e.product ? (o(), n("div", G, [
              b.value ? (o(), n("img", {
                key: 0,
                class: "ticto-mini-img",
                src: b.value,
                alt: e.product.name
              }, null, 8, K)) : (o(), n("div", R)),
              r("div", D, [
                r("p", Y, d(e.product.name), 1),
                e.product.description ? (o(), n("p", q, d(e.product.description), 1)) : s("", !0),
                r("p", H, [
                  B.value ? (o(), n("span", V, d(A.value), 1)) : s("", !0),
                  r("strong", null, d(E.value), 1)
                ]),
                h.value ? (o(), n("p", $, d(h.value), 1)) : s("", !0)
              ])
            ])) : s("", !0),
            (o(), M(T(x.value), {
              "product-id": e.productId,
              "product-offer-id": e.productOfferId,
              "subscription-plan-id": e.subscriptionPlanId,
              "checkout-session-token": e.checkoutSessionToken,
              "affiliate-ref": e.affiliateRef,
              "order-bumps": e.orderBumps,
              "order-bump-ids": m.value,
              "onUpdate:orderBumpIds": c[0] || (c[0] = (a) => m.value = a),
              "primary-color": f.value,
              config: e.config,
              "available-payment-methods": e.availablePaymentMethods,
              "prefill-coupon": e.prefillCoupon,
              t: e.t,
              "display-currency": e.displayCurrency,
              "checkout-locale": e.checkoutLocale,
              "format-price": e.formatPrice,
              "suggested-country-code": e.suggestedCountryCode,
              "locale-storage-key": e.localeStorageKey,
              "card-payee-code": e.cardPayeeCode,
              "card-efi-sandbox": e.cardEfiSandbox,
              "card-stripe-publishable-key": e.cardStripePublishableKey,
              "card-stripe-sandbox": e.cardStripeSandbox,
              "card-stripe-link-enabled": e.cardStripeLinkEnabled,
              "card-installments-enabled": e.cardInstallmentsEnabled,
              "card-max-installments": e.cardMaxInstallments,
              "card-mercadopago-public-key": e.cardMercadopagoPublicKey,
              "card-mercadopago-sandbox": e.cardMercadopagoSandbox,
              "card-paypal-client-id": e.cardPaypalClientId,
              "card-paypal-sandbox": e.cardPaypalSandbox,
              "card-paypal-checkout-mode": e.cardPaypalCheckoutMode,
              "card-gateway-keys": e.cardGatewayKeys,
              "checkout-total-brl": e.checkoutTotalBrl,
              "checkout-total-in-currency": e.checkoutTotalInCurrency,
              "main-line-price-brl": e.mainLinePriceBrl,
              "currency-list": e.currencyList,
              "featured-currencies": e.featuredCurrencies,
              "other-currencies": e.otherCurrencies,
              "plugin-checkout-extensions": e.pluginCheckoutExtensions,
              "product-name": e.productName,
              "cajupay-pay-account-id": e.cajupayPayAccountId,
              "parcelado-sdk-options": e.parceladoSdkOptions,
              "price-in-currency": e.priceInCurrency,
              onCouponApplied: c[1] || (c[1] = (a) => p("couponApplied", a)),
              onCouponCleared: c[2] || (c[2] = (a) => p("couponCleared", a)),
              onPaymentApproved: c[3] || (c[3] = (a) => p("paymentApproved", a)),
              onSetCurrency: c[4] || (c[4] = (a) => p("setCurrency", a))
            }, null, 40, ["product-id", "product-offer-id", "subscription-plan-id", "checkout-session-token", "affiliate-ref", "order-bumps", "order-bump-ids", "primary-color", "config", "available-payment-methods", "prefill-coupon", "t", "display-currency", "checkout-locale", "format-price", "suggested-country-code", "locale-storage-key", "card-payee-code", "card-efi-sandbox", "card-stripe-publishable-key", "card-stripe-sandbox", "card-stripe-link-enabled", "card-installments-enabled", "card-max-installments", "card-mercadopago-public-key", "card-mercadopago-sandbox", "card-paypal-client-id", "card-paypal-sandbox", "card-paypal-checkout-mode", "card-gateway-keys", "checkout-total-brl", "checkout-total-in-currency", "main-line-price-brl", "currency-list", "featured-currencies", "other-currencies", "plugin-checkout-extensions", "product-name", "cajupay-pay-account-id", "parcelado-sdk-options", "price-in-currency"]))
          ])
        ]),
        g.value.length ? (o(), n("aside", z, [
          (o(!0), n(S, null, v(g.value, (a, L) => (o(), n("article", {
            key: L,
            class: "ticto-review"
          }, [
            r("div", Z, [
              a.photo ? (o(), n("img", {
                key: 0,
                class: "ticto-avatar",
                src: a.photo,
                alt: a.author
              }, null, 8, J)) : (o(), n("div", Q, d((a.author || "?").charAt(0).toUpperCase()), 1)),
              r("div", null, [
                r("p", W, d(a.author || "Cliente"), 1),
                r("div", {
                  class: "ticto-stars",
                  "aria-label": `${a.stars || 5} de 5`
                }, [
                  (o(), n(S, null, v(5, (C) => r("svg", {
                    key: C,
                    class: O(["ticto-star", C <= (a.stars || 5) ? "is-on" : ""]),
                    viewBox: "0 0 24 24"
                  }, [...c[6] || (c[6] = [
                    r("path", { d: "M12 2.5 14.9 9l6.6.9-4.8 4.6 1.2 6.6L12 17.8 6.1 21.1l1.2-6.6L2.5 9.9 9.1 9 12 2.5Z" }, null, -1)
                  ])], 2)), 64))
                ], 8, X)
              ])
            ]),
            a.description ? (o(), n("p", _, "“" + d(a.description) + "”", 1)) : s("", !0)
          ]))), 128))
        ])) : s("", !0)
      ])
    ], 4));
  }
};
function te() {
  if (!(typeof document > "u") && !document.querySelector('link[data-getfy-plugin-css="getfy-checkout-clone-ticto"]'))
    try {
      const e = new URL(
        /* @vite-ignore */
        "./plugin-ui.css",
        import.meta.url
      ).href, u = document.createElement("link");
      u.rel = "stylesheet", u.href = e, u.dataset.getfyPluginCss = "getfy-checkout-clone-ticto", document.head.appendChild(u);
    } catch {
    }
}
te();
window.__GETFY_PLUGIN_UI__ = window.__GETFY_PLUGIN_UI__ || {};
window.__GETFY_PLUGIN_UI__["getfy-checkout-clone-ticto"] = {
  TictoCheckoutTemplate: ee
};
export {
  ee as TictoCheckoutTemplate
};
