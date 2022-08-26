<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UserProvider;
use App\Support\Client\DateTimer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array $input
     * @return User
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return with(
            (new UserProvider())->createWithAttributes(array_filter([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'email_verified_at' => class_implements(User::class, MustVerifyEmail::class) ? DateTimer::databaseNow() : null
            ])),
            static function (User $user) use ($input) {
                $user->setRawPassword($input['password']);
                return $user;
            }
        );
    }
}
