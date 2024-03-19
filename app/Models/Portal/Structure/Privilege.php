<?php

namespace App\Models\Portal\Structure;

use App\Models\Portal\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'portal_privilegios';

    protected $fillable = [
        'titulo',
        'descricao',
        'permissoes'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'privilegio_id');
    }
}
