{if !$error}
<p>{l s='Your order on' mod='hipaypayshop'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='hipaypayshop'}
	<br /><br />
	{l s='Payshop payment details:' mod='hipaypayshop'}
	<br /><br />
	<p>
		<table border="1" cellpadding="5" cellspacing="5" style="border:1px solid #ccc;">
			<tr>
				<td>
					<img src="{$this_path}payshop.jpg" alt="{l s='Comprafacil' mod='hipaypayshop'}" border="0" />
				</td>
				<td align="left" valign="top">
						{l s='Reference:' mod='hipaypayshop'} <span>{$reference}</span>
						<br /><br />{l s='Limit Date:' mod='hipaypayshop'} <span>{$limitdate}</span>
						<br /><br />{l s='Value:' mod='hipaypayshop'} <span>{$value}</span>
				</td>
			</tr>
		</table>
	</p>

	<br /><br />{l s='An e-mail has been sent to you with this information.' mod='hipaypayshop'}
	<br /><br />{l s='Your order will be sent as soon as we receive your payment.' mod='hipaypayshop'}
	<br /><br />{l s='For any questions or for further information, please contact our' mod='hipaypayshop'} <a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='hipaypayshop'}</a>.
</p>
{else}
    <p class="warning">{$error}</p>
{/if}
