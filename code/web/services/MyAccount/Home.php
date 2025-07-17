<?php

require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/sys/Suggestions.php';
require_once ROOT_DIR . '/sys/CommunityEngagement/Campaign.php';

class MyAccount_Home extends MyAccount {
	function launch() : void {
		global $interface;

		// The script should only execute when a user is logged in, otherwise it calls Login.php
		if (UserAccount::isLoggedIn()) {
			$user = UserAccount::getLoggedInUser();
			$homeLibrary = $user->getHomeLibrary();
			$homeLibraryCommunityEngagementHighlight = $homeLibrary ? $homeLibrary->highlightCommunityEngagement : null;
			$homeLibraryHighlightOpenToEnrollCampaigns = $homeLibrary ? $homeLibrary->highlightCommunityEngagementOpenToEnroll : null;
			$campaignsToEnroll = [];

			if ($homeLibraryHighlightOpenToEnrollCampaigns) {
				$campaign = new Campaign();
				$allCampaigns = $campaign->getCampaigns();
				$userId  = $user->id;
				$campaignsToEnroll = Campaign::filterByCanEnroll($allCampaigns, $userId);
			}
			// Check to see if the user has rated any titles
			$interface->assign('hasRatings', $user->hasRatings());
			$interface->assign('highlightCommunityEngagement', $homeLibraryCommunityEngagementHighlight);
			$interface->assign('highlightOpenToEnrollCampaigns', $homeLibraryHighlightOpenToEnrollCampaigns);
			$interface->assign('campaignsToEnroll', $campaignsToEnroll);

			parent::display('home.tpl');
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('', 'Your Account');
		return $breadcrumbs;
	}
}