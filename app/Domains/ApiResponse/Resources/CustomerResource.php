<?php

namespace App\Domains\ApiResponse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): CustomerResource
    {
        self::$relations = $data;
        return parent::make($resource);
    }

    public static function collection($resource, $data = []): AnonymousResourceCollection
    {
        self::$relations = $data;
        return parent::collection($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {

        $data = [
            'id' => $this->id,
            'active' => $this->active,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => (bool)$this->email_verified_at,
            'backend' => false,
            'permissions' => [],
        ];

        $relations = self::$relations;
        if (in_array('view_backend', $relations)) {
            $data['backend'] = $relations['view_backend'];
        }
        if (in_array('permissions', $relations)) {
            $data['permissions'] = $this->getUserPermissions();
        }
        if (in_array('roles', $relations)) {
            $data['roles'] = $this->getUserRoles();
        }

        return $data;

    }

    public function getUserRoles()
    {
        $roles = [];
        try {
            $roles = $this->roles->pluck('name')->toArray();
            if (count($roles) > 0) {
                return $roles;
            }
        } catch (\Exception $exception) {
            return $roles;
        }
        return $roles;
    }

    public function getUserPermissions()
    {
        $permissions = [];
        try {
            $permissions = $this->permissions->pluck('name')->toArray();
            if (count($permissions) > 0) {
                return $permissions;
            }
        } catch (\Exception $exception) {
            return $permissions;
        }
        return $permissions;
    }


}
