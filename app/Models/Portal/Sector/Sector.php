<?php

namespace App\Models\Portal\Sector;

use App\Models\Portal\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'local';
    protected $table = 'portal_setores';

    protected $fillable = [
        'titulo',
        'descricao',
        'criado_por',
        'atualizado_por'
    ];


    public function user()
    {
        $this->hasMany(User::class, 'setor_id', 'id');
    }
}
