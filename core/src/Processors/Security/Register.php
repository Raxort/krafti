<?php

namespace App\Processors\Security;

use App\Model\Traits\UserValidate;
use App\Model\User;

class Register extends \App\Processor
{

    use UserValidate;


    public function post()
    {
        if (!$password = trim($this->getProperty('password'))) {
            return $this->failure('Вы должны указать свой пароль');
        } elseif (strlen($password) < 6) {
            return $this->failure('Пароль должен быть не менее 6 символов');
        }

        /** @var User $user */
        $user = new User([
            'email' => trim($this->getProperty('email')),
            'fullname' => trim($this->getProperty('fullname')),
            'password' => trim($this->getProperty('password')),
            'instagram' => trim($this->getProperty('instagram'), ' @'),
            'active' => true,
            'role_id' => 3, // Regular user
        ]);

        if ($promo = trim($this->getProperty('promo'))) {
            /** @var User $referrer */
            if (!$referrer = User::query()->where(['promo' => $promo, 'active' => true])->first()) {
                return $this->failure('Указан недействительный промокод');
            } else {
                $user->referrer_id = $referrer->id;
            }
        }

        $validate = $this->validate($user);
        if ($validate !== true) {
            return $this->failure($validate);
        }

        if ($user->save()) {
            if ($user->email) {
                $secret = getenv('EMAIL_SECRET');
                $encrypted = base64_encode(openssl_encrypt($user->email, 'AES-256-CBC', $secret));
                $this->sendMail($user, $encrypted);
            }

            return $this->success([
                'id' => $user->id,
            ]);
        }

        return $this->failure('Неизвестная ошибка');
    }


    /**
     * @param User $user
     * @param $secret
     *
     * @return bool
     */
    protected function sendMail($user, $secret)
    {
        $url = getenv('SITE_URL');
        $mail = $this->container->mail;
        try {
            $data = $user->toArray();
            $data['link'] = "{$url}service/email/confirm?user_id={$user->id}&secret={$secret}";

            $subject = 'Вы успешно зарегистрировались на Krafti.ru';
            $body = $this->container->view->fetch(
                $mail->tpls['register'],
                $data
            );
        } catch (\Exception $e) {
            $this->container->logger->error('Could not fetch email template: ' . $e->getMessage());

            return false;
        }

        return $mail->send($user->email, $subject, $body);
    }
}