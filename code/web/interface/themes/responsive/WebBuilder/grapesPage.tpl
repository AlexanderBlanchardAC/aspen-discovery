<div class="col-xs-12">
	<h1>{$title}</h1>
	{* {$contents} *}
	{$templateContent|print_r}
	{* {assign var="templateContentArray" value=$templateContent|@json_decode:true}

	{if isset($templateContentArray.html)}
		{$templateContentArray.html nofilter}
	{/if}

	{if isset($templateContentArray.css)}
		<style>{$templateContentArray.css}</style>
	{/if} *}

	</div>