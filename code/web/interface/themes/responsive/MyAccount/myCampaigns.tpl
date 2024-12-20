{strip}
    <h1>{translate text="Campaigns" isPublicFacing=true}</h1>
    <h3><a href="/Community/Leaderboard">See the Leaderboard</a></h3>
    {if empty($campaignList)}
        <div class="alert alert-info">
            {translate text="There are no available campaigns at the moment" isPublicFacing=true}
        </div>
    {else}
        <h2>Your Campaigns</h2>
        <table id="yourCampaignsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>{translate text="Campaign Name" isPublicFacing=true}</th>
                    <th>{translate text="Start Date" isPublicFacing=true}</th>
                    <th>{translate text="End Date" isPublicFacing=true}</th>
                    <th>{translate text="Campaign Reward" isPublicFacing=true}</th>
                    <th>{translate text="Milestones Completed" isPublicFacing=true}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$campaignList item="campaign" key="resultIndex"}
                {if $campaign->enrolled && ($campaign->isActive || $campaign->isUpcoming)}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>{$campaign->startDate}</td>
                        <td>{$campaign->endDate}</td>
                        <td>
                            {$campaign->rewardName}
                            {if $campaign->rewardType === 1}
                                <img src="{$campaign->badgeImage}" alt="{$campaign->rewardName}" width="100" height="100"/>
                            {/if}
                        </td>
                        <td>{$campaign->numCompletedMilestones} / {$campaign->numCampaignMilestones}</td>
                        <td>
                        {if $campaign->leaderboardOptIn}
                            <a onclick="AspenDiscovery.Account.optOutOfCampaignLeaderboard({$campaign->id});">{translate text="Opt out of leaderboard" isPublicFacing=true}</a>
                        {else}
                            <a onclick="AspenDiscovery.Account.optIntoCampaignLeaderboard({$campaign->id});">{translate text="Opt into leaderboard" isPublicFacing=true}</a>
                        {/if}
                        </td>
                        <td>
                            <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                        </td>
                        <td>
                            <button onclick="toggleYourCampaignInfo({$resultIndex});">{translate text="Campaign Information" isPublicFacing=true}</button>
                        </td>
                    </tr>
                        {* <tr id="campaignInfo_{$resultIndex}" style="display:none;"> *}
                        <tr id="yourCampaigns_{$resultIndex}" class="campaign-dropdown" style="display:none;">
                            <td colspan="4">
                                {* <h4>{translate text="Milestones"}</h4> *}
                                <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{translate text="Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone Reward" isPublicFacing=true}</th>
                                        <th>{translate text="Progress Towards Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Progess Percentage" isPublicFacing=true}</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                    {foreach from=$campaign->milestones item="milestone"}
                                        <tr>
                                            <td>{$milestone->name}</td>
                                            <td>
                                                {$milestone->rewardName}
                                                {if $milestone->rewardType === 1}
                                                    <img src="{$milestone->badgeImage}" alt="{$milestone->rewardName}" width="100" height="100"/>
                                                {/if}
                                            </td>
                                            <td>
                                                {$milestone->completedGoals}/ {$milestone->totalGoals}
                                                {foreach from=$milestone->progressData item="progressData"}
                                                <div style="padding:10px;">
                                                    {$progressData['title']}
                                                </div>
                                                {/foreach}
                                            </td>
                                            <td>
                                                <div class="progress" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="{$milestone->progress}" aria-valuemin="0"
                                                     aria-valuemax="100" style="width: {$milestone->progress}%; line-height: 20px; text-align: center; color: #fff;">
                                                        {$milestone->progress}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>                                 
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        {if $hasLinkedUsers}
            <h2>{translate text="Linked Account Campaigns" isPublicFacing=true}</h2>
            {foreach from=$linkedCampaigns item="linkedUser"}
                <h3>{$linkedUser.linkedUserName}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>{translate text="Campaign Name" isPublicFacing=true}</th>
                            <th>{translate text="Enrollment Status" isPublicFacing=true}</th>
                            <th>{translate text="Action" isPublicFacing=true}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$linkedUser.campaigns item="campaign"}
                            <tr>
                                <td>{$campaign.campaignName}</td>
                                <td>
                                    {if $campaign.isEnrolled}
                                        {translate text="Enrolled" isPublicFacing=true}
                                    {else}
                                        {translate text="Not Enrolled" isPublicFacing=true}
                                    {/if}</td>
                                <td>
                                    {if $campaign.isEnrolled}
                                        <button onclick="AspenDiscovery.Account.unenroll({$campaign.campaignId}, {$linkedUser.linkedUserId});">{translate text="Unenroll" isPublicFacing=true}</button>
                                    {else}
                                        <button onclick="AspenDiscovery.Account.enroll({$campaign.campaignId}, {$linkedUser.linkedUserId});">{translate text="Enroll" isPublicFacing=true}</button>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/foreach}
        {/if}
        <h2>Active Campaigns</h2>
        <table id="activeCampaignsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>{translate text="Campaign Name" isPublicFacing=true}</th>
                    <th>{translate text="Campaign Reward" isPublicFacing=true}</th>
                    <th>{translate text="End Date" isPublicFacing=true}</th>
                    <th>{translate text="Enrollment" isPublicFacing=true}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$campaignList item="campaign" key="resultIndex"}
                {if $campaign->isActive}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>{$campaign->rewardName}</td>
                        <td>{$campaign->endDate}</td>
                        {if $campaign->enrolled}
                            <td>{translate text="Enrolled" isPublicFacing=true}</td>
                        {else}
                            <td>{translate text="Not Enrolled" isPublicFacing=true}</td>
                        {/if}
                        {if $campaign->enrolled}
                        <td>
                            <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                        </td>
                        {else}
                            <td>
                                <button onclick="AspenDiscovery.Account.enroll({$campaign->id}, {$userId});">{translate text="Enroll" isPublicFacing=true}</button>
                            </td>
                        {/if}
                        <td>
                            <button onclick="toggleActiveCampaignInfo({$resultIndex});">{translate text="Campaign Information" isPublicFacing=true}</button>
                        </td>
                    </tr>
                        {* <tr id="campaignInfo_{$resultIndex}" style="display:none;"> *}
                        <tr id="activeCampaigns_{$resultIndex}" class="campaign-dropdown" style="display:none;">

                            <td colspan="4">
                                {* <h4>{translate text="Milestones"}</h4> *}
                                <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{translate text="Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone Reward" isPublicFacing=true}</th>
                                        <th>{translate text="Progress Towards Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Progess Percentage" isPublicFacing=true}</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                    {foreach from=$campaign->milestones item="milestone"}
                                        <tr>
                                            <td>{$milestone->name}</td>
                                            <td>{$milestone->rewardName}</td>
                                            <td>
                                                {$milestone->completedGoals}/ {$milestone->totalGoals}
                                                {foreach from=$milestone->progressData item="progressData"}
                                                <div style="padding:10px;">
                                                    {$progressData['title']}
                                                </div>
                                                {/foreach}
                                            </td>
                                            <td>
                                                <div class="progress" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="{$milestone->progress}" aria-valuemin="0"
                                                     aria-valuemax="100" style="width: {$milestone->progress}%; line-height: 20px; text-align: center; color: #fff;">
                                                        {$milestone->progress}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>                                 
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        <h2>Upcoming Campaigns</h2>
        <table id ="upcomingCampaignsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>{translate text="Campaign Name" isPublicFacing=true}</th>
                    <th>{translate text="Campaign Reward" isPublicFacing=true}</th>
                    <th>{translate text="Start Date" isPublicFacing=true}</th>
                    <th>{translate text="Enrollment" isPublicFacing=true}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            {foreach from=$campaignList item="campaign" key="resultIndex"}
                {if $campaign->isUpcoming}
                    <tr>
                        <td>{$campaign->name}</td>
                        <td>{$campaign->rewardName}</td>
                        <td>{$campaign->startDate}</td>
                        {if $campaign->enrolled}
                            <td>{translate text="Enrolled" isPublicFacing=true}</td>
                        {else}
                            <td>{translate text="Not Enrolled" isPublicFacing=true}</td>
                        {/if}
                        {if $campaign->enrolled}
                            <td>
                                <button onclick="AspenDiscovery.Account.unenroll({$campaign->id}, {$userId});">{translate text="Unenroll" isPublicFacing=true}</button>
                            </td>
                            {else}
                                <td>
                                    <button onclick="AspenDiscovery.Account.enroll({$campaign->id}, {$userId});">{translate text="Enroll" isPublicFacing=true}</button>
                                </td>
                            {/if}
                            <td>
                                <button onclick="toggleUpcomingCampaignInfo({$resultIndex});">{translate text="Campaign Information" isPublicFacing=true}</button>
                            </td>
                    </tr>
                    <tr id="upcomingCampaigns_{$resultIndex}" class="campaign-dropdown" style="display:none;">
                            <td colspan="4">
                                {* <h4>{translate text="Milestones"}</h4> *}
                                <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{translate text="Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone Reward" isPublicFacing=true}</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                    {foreach from=$campaign->milestones item="milestone"}
                                        <tr>
                                            <td>{$milestone->name}</td>
                                            <td>{$milestone->rewardName}</td>
                                        </tr>                                 
                                    {/foreach}
                                    </tbody>
                                </table>
                            </td>
                    </tr>
                {/if}
            {/foreach}
        </table>
        <h2>Past Campaigns</h2>
        <table id="pastCampaignsTable" class="table table-striped">
            <thead>
                <tr>
                    <th>{translate text="Campaign Name" isPublicFacing=true}</th>
                    <th>{translate text="Start Date" isPublicFacing=true}</th>
                    <th>{translate text="End Date" isPublicFacing=true}</th>
                    <th>{translate text="Campaign Reward" isPublicFacing=true}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$pastCampaigns item="campaign" key="resultIndex"}
                <tr>
                    <td>{$campaign->name}</td>
                    <td>{$campaign->startDate}</td>
                    <td>{$campaign->endDate}</td>
                    <td>{$campaign->rewardName}</td>
                    <td>
                        <button onclick="togglePastCampaignInfo({$resultIndex});">{translate text="Campaign Information" isPublicFacing=true}</button>
                    </td>
                </tr>
                <tr id="pastCampaigns_{$resultIndex}" class="campaign-dropdown" style="display:none;">
                    <td col="4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{translate text="Milestone" isPublicFacing=true}</th>
                                    <th>{translate text="Milestone Reward" isPublicFacing=true}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$campaign->milestones item="milestone"}
                                    <tr>
                                        <td>{$milestone->name}</td>
                                        <td>{$milestone->rewardName}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <h2>Your Past Campaigns</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{translate text="Campaign Name" isPublicFacing=true}</th>
                    <th>{translate text="Start Date" isPublicFacing=true}</th>
                    <th>{translate text="End Date" isPublicFacing=true}</th>
                    <th>{translate text="Campaign Reward" isPublicFacing=true}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$pastCampaigns item="campaign" key="resultIndex"}
                {if $campaign->enrolled}
                        <tr>
                            <td>{$campaign->name}</td>
                            <td>{$campaign->startDate}</td>
                            <td>{$campaign->endDate}</td>
                            <td>
                            {$campaign->rewardName}<br>
                            {if $campaign->rewardType === 1}
                                <img src="{$campaign->badgeImage}" alt="{$campaign->rewardName}" width="100" height="100"/>
                            {/if}<br>
                             {if $campaign->campaignRewardGiven && $campaign->rewardType ===1}
                                <strong>{translate text="Reward Received"}</strong><br>
                                <a 
                                    href="https://www.facebook.com/sharer/sharer.php?u=http://localhost/{$campaign->shareImage}&quote={$campaign->rewardName}"
                                    target="_blank"
                                    title="{translate text="Share on Facebook" inAttribute=true isPublicFacing=true}"
                                    aria-label="{translate text="Share on Facebook" isPublicFacing=true inAttribute=true}"
                                >
                                    <i class="fab fa-facebook-square fa-2x fa-fw"></i>
                                </a>
                                <a 
                                    href="https://twitter.com/intent/tweet?url={$campaign->badgeImage}&text={translate text="My Badge!"}"
                                    target="_blank"
                                    title="{translate text="Share on Twitter" inAttribute=true isPublicFacing=true}"
                                    aria-label="{translate text="Share on Twitter" isPublicFacing=true inAttribute=true}"
                                >
                                    <i class="fab fa-twitter-square fa-2x fa-fw"></i>
                                </a>
                                <a href="http://www.pinterest.com/pin/create/button/?url={$campaign->shareImage}&media={$campaign->shareImage}&description=Pin%20on%20Pinterest" target="_blank" title="{translate text="Pin on Pinterest" inAttribute=true isPublicFacing=true}"  aria-label="{translate text="Pin on Pinterest" isPublicFacing=true inAttribute=true}">
				                    <i class="fab fa-pinterest-square fa-2x fa-fw"></i>
			                    </a>
                            {elseif $campaignRewardGiven}
                                <strong>{translate text="Reward Received"}</strong>
                             {/if}
                             </td>
                             <td>
                                <button onclick="toggleYourPastCampaignInfo({$resultIndex});">{translate text="Campaign Information" isPublicFacing=true}</button>
                            </td>
                        </tr>
                        <tr id="yourPastCampaigns_{$resultIndex}" style="display:none;">
                             <td colspan="4">
                                <table class="table table-bordered">
                                    <thead>
                                        <th>{translate text="Milestone" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone Progress" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone Reward" isPublicFacing=true}</th>
                                        <th>{translate text="Milestone Reward Status" isPublicFacing=true}</th>
                                    </thead>
                                    <tbody>
                                    {foreach from=$campaign->milestones item="milestone"}
                                        <tr>
                                            <td>{$milestone->name}</td>
                                            <td>
                                            <div class="progress" style="width:100%; border:1px solid black; border-radius:4px;height:20px;">
                                                <div class="progress-bar" role="progressbar" aria-valuenow="{$milestone->progress}" aria-valuemin="0"
                                                aria-valuemax="100" style="width: {$milestone->progress}%; line-height: 20px; text-align: center; color: #fff;">
                                                    {$milestone->progress}%
                                                </div>
                                            </td>
                                            <td>
                                                {$milestone->rewardName}
                                                {if $milestone->rewardType === 1}
                                                    <img src="{$milestone->badgeImage}" alt="{$milestone->rewardName}" width="100" height="100"/>
                                                {/if}
                                            </td>
                                            <td>
                                                {if $milestone->rewardGiven}
                                                    {translate text="Reward Given" isPublicFacing=true}
                                                {else}
                                                    {translate text="Not Yet Given" isPublicFacing=true}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                             </td>
                        </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    {/if}
{/strip}
{literal}
    <script type="text/javascript">
           function toggleYourCampaignInfo(index) {
            var campaignInfoDiv = document.getElementById('yourCampaigns_' + index);
            if (campaignInfoDiv.style.display === 'none') {
                campaignInfoDiv.style.display = 'block';
            } else {
                campaignInfoDiv.style.display = 'none';
            }
        }       

        function toggleActiveCampaignInfo(index) {
            var campaignInfoDiv = document.getElementById('activeCampaigns_' + index);
            if (campaignInfoDiv.style.display === 'none') {
                campaignInfoDiv.style.display = 'block';
            } else {
                campaignInfoDiv.style.display = 'none';
            }
        }    

        function toggleUpcomingCampaignInfo(index) {
            var campaignInfoDiv = document.getElementById('upcomingCampaigns_' + index);
            if (campaignInfoDiv.style.display === 'none') {
                campaignInfoDiv.style.display = 'block';
            } else {
                campaignInfoDiv.style.display = 'none';
            }
        }    

        function togglePastCampaignInfo(index) {
            var campaignInfoDiv = document.getElementById('pastCampaigns_' + index);
            if (campaignInfoDiv.style.display === 'none') {
                campaignInfoDiv.style.display = 'block';
            } else {
                campaignInfoDiv.style.display = 'none';
            }
        }    

        function toggleYourPastCampaignInfo(index) {
            var campaignInfoDiv = document.getElementById('yourPastCampaigns_' + index);
            if (campaignInfoDiv.style.display === 'none') {
                campaignInfoDiv.style.display = 'block';
            } else {
                campaignInfoDiv.style.display = 'none';
            }
        }    
    </script>
{/literal}