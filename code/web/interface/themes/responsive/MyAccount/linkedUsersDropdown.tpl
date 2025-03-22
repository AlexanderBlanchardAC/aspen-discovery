<div class="row">
					<div class="col-tn-12">
						<div id="linkedUserOptions" class="form-group">
							<label class="control-label" for="linkedUsersDropdown">{translate text="Linked Users" isPublicFacing=true}&nbsp;</label>
							<select name="selectedUser" id="linkedUsersDropdown" class="form-control" onchange="AspenDiscovery.Account.filterOutLinkedUsers('{$filterType}');">
								<option value="" {if $selectedUser == ""}selected{/if}>All</option>
								<option value="{$currentUserId}" {if $selectedUser == $currentUserId} selected="selected"{/if}>
									{$currentUserName}
								</option>
								{foreach from=$linkedUsers item=user}
									<option value="{$user->id}"{if $selectedUser == $user->id} selected="selected"{/if}>{$user->displayName}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>