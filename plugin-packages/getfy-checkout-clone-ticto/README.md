# Checkout Clone Ticto

Plugin comercial que **libera o template Ticto Clone** na aba Template do editor de checkout.

O layout visual é renderizado pelo **core do Getfy** (`core_layout: "ticto"` → `TictoCheckoutLayout`) quando:

1. Este plugin está **instalado e ativo**
2. O checkout do produto usa `template: "ticto-clone"`

## Instalação

1. Compacte o conteúdo desta pasta (`plugin.json` + `bootstrap.php` + `README.md`) em um `.zip`.
2. No Getfy: **Plugins → Instalar ZIP**.
3. Ative **Checkout Clone Ticto**.
4. Abra o produto → **Checkout** → aba **Template**.
5. Selecione **Ticto Clone** e salve.
6. Abra `/c/{slug}` (ou o preview do Builder).

Se o plugin for desativado, o checkout volta automaticamente para o template Original.

## Manifesto (`plugin.json`)

```json
{
  "checkout_builder_templates": [
    {
      "id": "ticto-clone",
      "name": "Ticto Clone",
      "core_layout": "ticto",
      "ui_variant": "ticto",
      "features": ["order_bump_band_color", "order_bump_inner_color"]
    }
  ]
}
```

| Campo | Função |
|--------|--------|
| `id` | Valor salvo em `config.template` |
| `core_layout` | Chave no registro core (`CHECKOUT_CORE_LAYOUTS`) — plugin **thin** |
| `ui_variant` | Skin do `CheckoutForm` / pagamento / bumps |
| `features` | Flags do Builder (campos extras de aparência) |

Não é necessário bundle frontend (`dist/` / `frontend.exports`). O Getfy resolve pelo manifesto.

## Contrato para futuros plugins

### Thin (como este)

- Declara `checkout_builder_templates` com `core_layout` apontando para um layout **já existente** no core.
- Opcional: `ui_variant` + `features`.
- Sem `frontend.exports.checkout_template`.

### Full (só no ZIP do plugin)

- Declara o mesmo array de templates **e**
- `frontend.exports.checkout_template` + `frontend.entry` + `dist/ui.manifest.json`.
- O Vue do plugin recebe as mesmas props que o layout core (`pluginTemplateProps` em `Show.vue`).
- Bundle tem **prioridade** sobre `core_layout` se ambos existirem.

Documentação do registro core: `resources/js/components/checkout/templates/coreLayouts.js`.

## Campos

Os campos de cliente e pagamento são os mesmos do checkout Getfy (sem CEP/endereço extra e sem confirmar e-mail). Depoimentos vêm da aba Social do Builder, quando configurados.
