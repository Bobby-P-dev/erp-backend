<?php

namespace App\Services\Core;

use App\Repositories\Core\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function getAll($search = null, array $filter = [])
    {
        $search = strtolower($search);

        return $this->userRepository->getAll($search, $filter);
    }

    public function syncRoles(int $id, array $roles)
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

    public function updatePassword(int $id, string $newPassword)
    {
        $user = $this->userRepository->find($id);
        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }
}
