{strip}
    <div id="main-content" class="col-sm-12">
        <h1>{translate text="Dashboard" isAdminFacing=true}</h1>
        {*Filtered Results*}
        <div>
            <label for="filterBy">Filter By:</label>
            <select id="filterBy" onchange="toggleFilterOptions()">
                <option value="">Select Filter</option>
                <option value="campaign">Campaign</option>
                <option value="user">User</option>
            </select>
            <div id="campaignDropdown" style="display:none;">
                <select id="campaign_id" onchange="AspenDiscovery.CommunityEngagement.filterDropdownOptions('campaign')">
                    <option value="">All Campaigns</option>
                    {foreach from=$campaigns item=$campaign}
                        <option value="{$campaign->id}">{$campaign->name}</option>
                    {/foreach}
                </select>
            </div>
            <div id="userDropdown" style="display:none;">
                    <select id="user_id" onchange="AspenDiscovery.CommunityEngagement.filterDropdownOptions('user')">
                        <option value="">All Users</option>
                        {foreach from=$users item=$user}
                                <option value="{$user->id}">{$user->username}</option>
                        {/foreach}
                    </select>
            </div>
        </div>
        <h2 class="dashboardCategoryLabel">{translate text="All Campaigns" isAdminFacing=true}&nbsp;
        <a href="/Community/UsageGraphs?stat=allCampaigns" >
            <i class="fas fa-chart-line"></i></h2>
        </a>
        <div class="row">
            <div class="dashboardCategory col-sm-6">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <h2 class="dashboardCategoryLabel">{translate text="Enrollments" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=userLogins&instance={$selectedInstance}" title="{translate text="Show User Logins Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a></h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-tn-6">
                        <div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
                        <div class="dashboardValue">{$totalEnrollmentsThisMonth}</div>
                    </div>
                    <div class="col-tn-6">
                        <div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
                        <div class="dashboardValue">{$totalEnrollmentsLastMonth}</div>
                    </div>
                    <div class="col-tn-6">
                        <div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
                        <div class="dashboardValue">{$totalEnrollmentsThisYear}</div>
                    </div>
                    <div class="col-tn-6">
                        <div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
                        <div class="dashboardValue">{$activeUsersAllTime.$profileId.totalUsers}</div>
                    </div>
                </div>
            </div>
        </div>
        <div id="campaignsList">
            <div class="dashboardCategory row" style="border: 1px solid #3174AF;padding:0 10px 10px 10px; margin-bottom: 10px;">

                <div class="col-sm-12">
                   
                    {foreach from=$campaigns item=campaign}
                        <div style="border-bottom: 2px solid #3174AF;padding: 10px; margin-bottom; 10px;">

                            <h5 style="font-weight:bold;">
                                <a href="/Community/CampaignTable?id={$campaign->id}">
                                    {translate text=$campaign->name isAdminFacing=true}
                                </a>
                                &nbsp;
                                <a href="/Community/UsageGraphs?stat={$campaign->id}">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                            </h5>

                            <div class="dashboardLabel">Number of Patrons Enrolled:</div>
                            <div class="dashboardValue">{translate text=$campaign->currentEnrollments isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Enrollments:</div>
                            <div class="dashboardValue">{translate text=$campaign->enrollmentCounter isAdminFacing=true}</div>

                            <div class="dashboardLabel">Total Number of Unenrollments:</div>
                            <div class="dashboardValue">{translate text=$campaign->unenrollmentCounter isAdminFacing=true}</div>

                            <div class="dashboardLabel">Number of Users Who Have Completed the Campaign</div>
                            <div class="dashboardValue">{translate text=$campaign->completedUsersCount isAdminFacing=true}</div>
                        </div>
                    {/foreach}
                </div>
            </div>  
        </div>

        {*Filtered Campaigns*}
        <div id="filteredCampaign">
         
        </div>
    </div>
{/strip}
<script type="text/javascript">
    function toggleFilterOptions() {
        var filterBy = document.getElementById("filterBy").value;
        var campaignDropdown = document.getElementById("campaignDropdown");
        var userDropdown = document.getElementById("userDropdown");

        if (filterBy === "campaign") {
            campaignDropdown.style.display = "block";
            userDropdown.style.display = "none";
        } else if (filterBy === "user") {
            userDropdown.style.display = "block";
            campaignDropdown.style.display = "none";
        } else {
            campaignDropdown.style.display = "none";
            userDropdown.style.display = "none";
        }
    }
</script>