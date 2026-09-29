<?php

namespace App\Services;

class PermissionService
{
    private $permissionRepository;

    public function __construct($permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function getRolePermissions($roleId)
    {
        if (!$roleId) return ['actions' => [], 'resources' => []];

        $permissions = $this->permissionRepository->getPermissionsByRoleId($roleId);
        
        $grantedActions = [];
        $grantedResources = [];
        
        foreach ($permissions as $p) {
            if (!empty($p['action_name'])) {
                $grantedActions[] = strtoupper($p['action_name']);
            }
            if (!empty($p['resource_name'])) {
                $grantedResources[] = strtolower(trim($p['resource_name']));
            }
        }

        return [
            'actions' => array_values(array_unique($grantedActions)),
            'resources' => array_values(array_unique($grantedResources))
        ];
    }
}
