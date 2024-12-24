<!DOCTYPE html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/css/grapes.min.css" integrity="sha512-F+EUNfBQvAXDvJcKgbm5DgtsOcy+5uhbGuH8VtK0ru/N6S3VYM9OHkn9ACgDlkwoxesxgeaX/6BdrQItwbBQNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/grapes.min.js" integrity="sha512-TavCuu5P1hn5roGNJSursS0xC7ex1qhRcbAG90OJYf5QEc4C/gQfFH/0MKSzkAFil/UBCTJCe/zmW5Ei091zvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://unpkg.com/grapesjs-preset-webpage@1.0.2"></script>

</head>
<body>
{strip}
    <button onclick="AspenDiscovery.CommunityEngagement.customizeLeaderboard()">{translate text="Customize Leaderboard" isPublicFacing=true}</button><br><br>
    <div id="gjs" display="none"></div>
   
    <label for="campaignFilter">Filter by Campaign:</label>
    <select id="campaign_id" onchange="AspenDiscovery.CommunityEngagement.filterLeaderboard()">
        <option value="">
            All Campaigns
        </option>
        {foreach from=$campaigns item=$campaign}
            <option value="{$campaign->id}">{$campaign->name}</option>
        {/foreach}
    </select>
    <div id="loading-placeholder" class="loading-spinner">
        <div class="spinner"></div>
        <p>Loading leaderboard...</p>
    </div><br>
    <div id="leaderboard-main-content" class="col-sm-12" display="none">
        <h1>{translate text="Leaderboard" isPublicFacing=true}</h1>
        {*Filter Leaderboard by campaign*}
        {* <label for="campaignFilter">Filter by Campaign:</label>
        <select id="campaign_id" onchange="AspenDiscovery.CommunityEngagement.filterLeaderboard()">
            <option value="">
                All Campaigns
            </option>
            {foreach from=$campaigns item=$campaign}
                <option value="{$campaign->id}">{$campaign->name}</option>
            {/foreach}
        </select> *}
        <div id="leaderboard-table"></div>
    </div>
{/strip}
</body>
<script>
    // document.addEventListener("DOMContentLoaded", function() {
    //     AspenDiscovery.CommunityEngagement.filterLeaderboard();
    // })
    $(document).ready(function() {
        $('#loading-placeholder').show();
        $('#leaderboard-main-content').hide();
        AspenDiscovery.CommunityEngagement.filterLeaderboard();

        var url = Globals.path + "/Community/AJAX?method=getUpdatedLeaderboardPage";
        $.get(url, function(data) {
            if (data.success) {
                $('#leaderboard-main-content').html(data.html);

                var style = document.createElement('style');
                style.innerHTML = data.css;
                document.head.appendChild(style);
                $('#loading-placeholder').hide();
                $('#leaderboard-main-content').fadeIn();
            } else {
                $('#loading-placeholder').hide();
                $('#leaderboard-main-content').show();
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Failed to fetch updated content", textStatus, errorThrown);
        })
    })
</script>
</html>