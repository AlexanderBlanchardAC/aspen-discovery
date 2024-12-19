{strip}
    <div id="main-content" class="col-sm-12">
        <h1>{translate text="Leaderboard" isPublicFacing=true}</h1>
        {*Filter Leaderboard by campaign*}
        <label for="campaignFilter">Filter by Campaign:</label>
        <select id="campaign_id" onchange="AspenDiscovery.CommunityEngagement.filterLeaderboard()">
            <option value="">
                All Campaigns
            </option>
            {foreach from=$campaigns item=$campaign}
                <option value="{$campaign->id}">{$campaign->name}</option>
            {/foreach}
        </select>
        <div id="leaderboard-table"></div>
    </div>
{/strip}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        AspenDiscovery.CommunityEngagement.filterLeaderboard();
    })
</script>