<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'first_surname' => ['nullable', 'string', 'max:255'],
            'identity_card' => ['required', 'integer'],
            'date_of_birth' => ['required', 'date'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $person = Person::create([
                'pension_fund_manager_id' => 1,
                'unique_affiliate_number' => $input['identity_card'],
                'identity_card' => $input['identity_card'],
                'first_name' => $input['name'],
                'first_surname' => $input['first_surname'] ?? null,
                'date_of_birth' => $input['date_of_birth'],
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);

            $user = User::create([
                'person_id' => $person->id,
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);

            $user->syncRoles(['query']);

            return $user;
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
