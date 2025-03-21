<?php

require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/services/MyAccount/AJAX.php';

class MyAccount_CheckedOut extends MyAccount {

	function launch() {
		global $interface;
		global $library;
		$user = UserAccount::getLoggedInUser();

		if (isset($_REQUEST['tab'])) {
			$tab = $_REQUEST['tab'];
		} else {
			$tab = 'all';
		}
		$interface->assign('tab', $tab);
		if ($library->showLibraryHoursNoticeOnAccountPages) {
			if ($user->homeLocationId != null) {
				$libraryHoursMessage = Location::getLibraryHoursMessage($user->homeLocationId);
			} else {
				$libraryHoursMessage = '';
			}
			$interface->assign('libraryHoursMessage', $libraryHoursMessage);
		}

		$readerName = new OverDriveDriver();
		$readerName = $readerName->getReaderName();
		$interface->assign('readerName', $readerName);

		if ($user->getHomeLibrary() != null) {
			$allowFilteringOfLinkedAccountsInCheckouts = $user->getHomeLibrary()->allowFilteringOfLinkedAccountsInCheckouts;
		} else {
			$allowFilteringOfLinkedAccountsInCheckouts = $library->allowFilteringOfLinkedAccountsInCheckouts;
		}

		$linkedUsers = $user->getLinkedUsers();
		$myAccountAjax = new MyAccount_AJAX();
		$selectedUser = $myAccountAjax->setFilterLinkedUser();

		$interface->assign('allowFilteringOfLinkedAccountsInCheckouts', $allowFilteringOfLinkedAccountsInCheckouts);
		$interface->assign('linkedUsers', $linkedUsers);
		$interface->assign('currentUserId', $user->id);
		$interface->assign('currentUserName', $user->displayName);
		$interface->assign('selectedUser', $selectedUser);

		$interface->assign('profile', $user);
		$this->display('checkedout.tpl', 'Checked Out Titles');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Your Checked Out Titles');
		return $breadcrumbs;
	}
}
