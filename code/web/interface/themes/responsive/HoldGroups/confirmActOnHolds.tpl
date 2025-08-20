{strip}
	<div class="hold-group-modal">
		<p>{translate text="The following holds are in this group and will be %1%:" 1=$actionText}</p>
		
		{if $holdsInGroup|@count > 0}
			<ul>
				{foreach from=$holdsInGroup item=hold}
					<li>
						{translate text="Title:"} {$hold->title|replace:"/":""|escape} <br>
					</li>
				{/foreach}
			</ul>
		{else}
			<p>{translate text="No holds found in this group."}</p>
		{/if}
	</div>
{/strip}