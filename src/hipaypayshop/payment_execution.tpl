{capture name=path}{l s='Hipay Payshop payment' mod='hipaypayshop'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='hipaypayshop'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='Hipay Payshop payment' mod='hipaypayshop'}</h3>
<form action="{$this_path}validation.php" method="post">
    <p>
        <img src="{$this_path}cards.png" alt="{l s='Comprafacil' mod='hipaypayshop'}" style="float:left; margin: 0px 20px 45px 0px;" />
        {l s='You have choosen to pay with Hipay Payshop.' mod='hipaypayshop'}
    </p>
    <p style="margin-top:20px;">
        - {l s='The total amount of your order is' mod='hipaypayshop'}
        <span id="amount" class="price">{displayPrice price=$total}</span>
        {if $use_taxes == 1}
            {l s='(tax incl.)' mod='hipaypayshop'}
        {/if}
        <br><br>
    </p>
    <p>
        {l s='Payshop payment details will be displayed on the next page.' mod='hipaypayshop'}
        <br /><br />
        <b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='hipaypayshop'}.</b>
        <br><br>
    </p>
    <p class="cart_navigation">
        <a href="{$link->getPageLink('order.php', true)}?step=3" class="button_large hideOnSubmit">{l s='Other payment methods' mod='hipaypayshop'}</a>
        <input type="submit" name="submit" value="{l s='I confirm my order' mod='hipaypayshop'}" class="exclusive exclusive_large hideOnSubmit" />
    </p>
</form>
{/if}
