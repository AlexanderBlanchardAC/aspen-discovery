<?php

require_once ROOT_DIR . '/sys/Community/Campaign.php';


class Community_Leaderboard extends Action {
    function launch() {
        global $interface;

        $campaign = new Campaign();
        $campaigns = $campaign->getAllCampaigns();
        $interface->assign('campaigns', $campaigns);
        
        $this->display('leaderboard.tpl', 'Leaderboard');
    }

    //TODO:: Insert breadcrumbs
    function getBreadcrumbs(): array {
        $breadcrumbs = [];
        return $breadcrumbs;
    }
}