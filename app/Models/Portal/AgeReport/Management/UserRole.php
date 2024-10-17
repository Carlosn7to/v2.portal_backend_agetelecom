<?php

namespace App\Models\Portal\AgeReport\Management;

use App\Models\Portal\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'portal';
    protected $table = 'age_relatorio_usuario_permissoes';
    protected $fillable = ['usuario_id', 'nivel', 'relatorios_liberados', 'liberado_por'];

    public function releasedByUser()
    {
        return $this->belongsTo(User::class, 'liberado_por', 'id')
            ->select('id', 'nome', 'login');
    }
}
