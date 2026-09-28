<?php
namespace Drupal\uwec_reports\Access;

use Drupal\Core\Session\AccessPolicyBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\CalculatedPermissionsItem;
use Drupal\Core\Session\RefinableCalculatedPermissionsInterface;

class IndexPolicy extends AccessPolicyBase{
	public function calculatePermissions(AccountInterface $account, string $scope): RefinableCalculatedPermissionsInterface{
		$calculated_permissions = parent::calculatePermissions($account, $scope);

		// add the "view uwec reports index" permission if this user can access any uwec report
		if($this->userCanAccessAnyReport($account)) {
			$calculated_permissions->addItem(new CalculatedPermissionsItem(
				permissions: ['view uwec reports index'],
				scope: $scope
			));
		}

		return $calculated_permissions;
	}

	public function getPersistentCacheContexts(): array{
		return ['user.roles'];
	}

	// returns true if the given account has access to any of the uwec reports.
	// false means they don't.
	protected function userCanAccessAnyReport(AccountInterface $account): bool{
		$roles = \Drupal::entityTypeManager()
			->getStorage('user_role')
			->loadMultiple($account->getRoles());

		$report_permissions = $this->getUwecReportsPermissions();

		foreach($roles as $role){
			foreach($report_permissions as $permission){
				if($role->hasPermission($permission)){
					return true;
				}
			}
		}

		return false;
	}

	// returns an array of all permissions provided by the uwec_reports module
	protected function getUwecReportsPermissions(){
		$all_permissions = \Drupal::service('user.permissions')->getPermissions();

		// gather all permissions from uwec_reports
		$report_permissions = [];
		foreach($all_permissions as $permission_name => $permission_info){
			if(isset($permission_info['provider']) && $permission_info['provider'] === 'uwec_reports'){
				$report_permissions[] = $permission_name;
			}
		}

		return $report_permissions;
	}
}