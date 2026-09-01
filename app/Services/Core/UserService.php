<?php

namespace App\Services\Core;

use App\Repositories\Core\UserRepository;
use Illuminate\Support\Facades\DB;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function syncRoles(string $id, array $roles)
    {
        try {
            DB::beginTransaction();
            $user = $this->userRepository->find($id);
            $this->userRepository->syncRoles($user, $roles);
            DB::commit();

            return $user->load('roles');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
