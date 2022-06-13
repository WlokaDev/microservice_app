<?php

namespace App\Services\v1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersService
{
    /**
     * @var User
     */

    protected User $user;

    /**
     * @param User $user
     */

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $email
     * @param string $name
     * @param string $password
     * @return $this
     */

    public function assignAttributes(
        string $email,
        string $name,
        string $password
    ) : self
    {
        $this->user->email = $email;
        $this->user->name = $name;
        $this->user->password = Hash::make($password);
        $this->user->save();

        return $this;
    }

    /**
     * @return User
     */

    public function getUser(): User
    {
        return $this->user;
    }
}
