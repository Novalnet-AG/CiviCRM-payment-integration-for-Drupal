
<div class="crm-contribution-page-id-{$contributionPageID}">
	{foreach from=$smarty.session.novalnet_iframe_params key=params item=val}
		<input type="hidden" id="{$params}" name="{$params}" value="{$val}" />
	{/foreach}
<iframe name="novalnet_iframe" id="novalnet_iframe" scrolling="no" width="750px"height="500px" frameborder="0"></iframe>
</div>
<script type="text/javascript">
    document.getElementById('Confirm').action = "https://payport.novalnet.de/pci_payport";
    document.getElementById("Confirm").target = "novalnet_iframe";
    document.getElementById("Confirm").submit();
</script>
