{if $cart->getOrderTotal(true) < 150}
<p class="payment_module hipay_payment_module">
    <a href="{$this_path}payment.php" title="{l s='Pay with Hipay Payshop' mod='hipaypayshop'}">
        <img src="{$this_path}cards.png" alt="{l s='Pay with Hipay Payshop' mod='hipaypayshop'}" />
        {l s='Pay with Hipay Payshop' mod='hipaypayshop'}
    </a>
</p>
<style type="text/css">p.hipay_payment_module a { padding-left: 10px; }</style>
{/if}