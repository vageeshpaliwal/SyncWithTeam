<?php

namespace App\Services;

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\PlanLimitService;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected PlanLimitService $planLimitService
    ) {}

    public function addUser(array $data, $organizationId, $invitedById)
    {
        if (!$this->planLimitService->canAddUser($organizationId)) {
            throw ValidationException::withMessages([
                'email' => 'Plan limit reached! Upgrade your subscription to add more users.',
            ]);
        }

        return DB::transaction(function () use ($data, $organizationId, $invitedById) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'designation' => $data['designation'] ?? null,
                'email' => $data['email'],
                'role_id' => $data['role_id'],
                'department_id' => $data['department_id'] ?? null,
                'username' => $data['username'],
                'joining_date' => $data['joining_date'] ?? null,
                'probation_months' => isset($data['probation_months']) ? (int)$data['probation_months'] : 3,
                'basic_salary' => $data['basic_salary'] ?? null,
                'pay_frequency' => $data['pay_frequency'] ?? 'monthly',
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'organization_id' => $organizationId,
                'status' => false,
            ]);

            return Invitation::create([
                'user_id' => $user->id,
                'organization_id' => $organizationId,
                'role_id' => $data['role_id'],
                'invited_by' => $invitedById,
                'email' => $data['email'],
                'token' => bin2hex(random_bytes(32)),
                'expires_at' => now()->addDays(7),
                'status' => 'PENDING',
            ]);
        });
    }

    public function getUsersForOrganization(int $organizationId): Collection
    {
        return User::with(['role', 'department'])
            ->where('organization_id', $organizationId)
            ->orderBy('first_name')
            ->get();
    }

    public function getPendingInvitations(int $organizationId): Collection
    {
        return Invitation::with(['role', 'invitedBy'])
            ->where('organization_id', $organizationId)
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get();
    }

    public function getAcceptedInvitations(int $organizationId): Collection
    {
        return Invitation::with(['role', 'invitedBy', 'user'])
            ->where('organization_id', $organizationId)
            ->where('status', 'ACCEPTED')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findValidInvitation(string $token): Invitation
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'PENDING') {
            throw ValidationException::withMessages([
                'token' => 'This invitation is no longer valid.',
            ]);
        }

        if ($invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'EXPIRED']);

            throw ValidationException::withMessages([
                'token' => 'This invitation has expired.',
            ]);
        }

        return $invitation;
    }

    public function activateInvitation(string $token, string $password): void
    {
        DB::transaction(function () use ($token, $password) {
            $invitation = $this->findValidInvitation($token);

            $invitation->user->update([
                'password' => Hash::make($password),
                'status' => true,
            ]);

            $invitation->update(['status' => 'ACCEPTED']);
        });
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'designation' => $data['designation'] ?? null,
            'email' => $data['email'],
            'username' => $data['username'],
            'role_id' => $data['role_id'],
            'department_id' => $data['department_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'joining_date' => $data['joining_date'] ?? null,
            'probation_months' => isset($data['probation_months']) ? (int)$data['probation_months'] : 3,
            'basic_salary' => $data['basic_salary'] ?? null,
            'pay_frequency' => $data['pay_frequency'] ?? 'monthly',
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'status' => filter_var($data['status'], FILTER_VALIDATE_BOOLEAN),
        ]);

        return $user;
    }

    public function toggleUserStatus(User $user): User
    {
        $user->update([
            'status' => !$user->status,
        ]);

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function resendInvitation(Invitation $invitation): void
    {
        $invitation->update([
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
            'status' => 'PENDING',
        ]);
    }

    public function cancelInvitation(Invitation $invitation): void
    {
        DB::transaction(function () use ($invitation) {
            $user = $invitation->user;
            if ($user) {
                $user->forceDelete();
            } else {
                $invitation->delete();
            }
        });
    }

    public function updateProfile(User $user, array $data, $request): User
    {
        return DB::transaction(function () use ($user, $data, $request) {
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return $user;
        });
    }
}