<?php

namespace App\Controllers\User;

use App\Model\Traits\UserValidate;
use App\Model\User;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Profile extends Controller
{
    use UserValidate;

    protected $scope = 'profile';

    /** @var User $user */
    protected $user;

    public function get(): ResponseInterface
    {
        $data = $this->user->getProfile();

        $this->user->logged_at = date('Y-m-d H:i:m');
        $this->user->save();

        return $this->success(['user' => $data]);
    }

    public function patch(): ResponseInterface
    {
        $user = $this->user;

        $user->fill([
            'email' => trim($this->getProperty('email')),
            'dob' => $this->getProperty('dob'),
            'fullname' => trim($this->getProperty('fullname')),
            'instagram' => trim($this->getProperty('instagram'), ' @'),
            'phone' => preg_replace('#[^0-9]#', '', $this->getProperty('phone')),
            'company' => trim($this->getProperty('company')),
            'description' => trim($this->getProperty('description')),
        ]);

        $validate = $this->validate($user);
        if ($validate !== true) {
            return $this->failure($validate);
        }

        if ($password = trim($this->getProperty('password'))) {
            $user->password = $password;
        }

        if (!$user->referrer_id && $referrer_code = $this->getProperty('referrer_code')) {
            /** @var User $referrer */
            if ($referrer = User::query()->where('promo', $referrer_code)->first()) {
                if ($user->id === $referrer->id) {
                    return $this->failure('Нельзя указывать свой собственный реферальный код!');
                }
                $user->referrer_id = $referrer->id;
            }
        }

        $user->save();

        $children = $this->getProperty('children');
        $ids = [];
        foreach ($children as $child) {
            $ids[] = !empty($child['id'])
                ? $child['id']
                : ($user->children()->create($child))->getKey();
        }
        $user->children()->whereNotIn('id', $ids)->delete();

        return $this->get();
    }
}
