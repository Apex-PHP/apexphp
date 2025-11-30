<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class User extends Model
{
    /**
     * Nome da tabela usada no model
     * @var string
     */
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'role', 'email_verified_at'];

    protected $hidden = ['password'];

    public $timestamps = true;
}
