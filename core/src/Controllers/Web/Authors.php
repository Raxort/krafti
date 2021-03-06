<?php

namespace App\Controllers\Web;

use App\Model\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vesp\Controllers\ModelGetController;

class Authors extends ModelGetController
{
    protected $model = User::class;

    /**
     * Add joins and search filter
     *
     * @param Builder $c
     *
     * @return mixed
     */
    protected function beforeGet(Builder $c): Builder
    {
        return $this->beforeCount($c);
    }

    /**
     * Add joins and search filter
     *
     * @param Builder $c
     *
     * @return mixed
     */
    protected function beforeCount(Builder $c): Builder
    {
        $c->where(['users.active' => true, 'favorite' => true]);
        //->groupBy('users.id')
        //->where('users.role_id', '<', 3)
        //->select(['users.id', 'users.fullname', 'users.company', 'users.description', 'users.photo_id'])
        //->join('lessons', 'lessons.author_id', '=', 'users.id');
        $c->orderByRaw('field(id,36,35,29,32,33)');

        return $c;
    }

    /**
     * @param User|Model $object
     *
     * @return array
     */
    public function prepareRow(Model $object): array
    {
        return [
            'id' => $object->id,
            'fullname' => $object->fullname,
            'company' => $object->company,
            'description' => $object->description,
            'long_description' => $object->long_description,
            'photo' => $object->photo_id
                ? [
                    'id' => $object->photo->id,
                    'updated_at' => $object->photo->updated_at,
                ]
                : null,
        ];
    }
}
