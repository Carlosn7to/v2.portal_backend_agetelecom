<?php

namespace App\Models\Portal\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Portal\AgeReport\Management\UserRole;
use App\Models\Portal\Sector\Sector;
use App\Models\Portal\Structure\Privilege;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    protected $connection = 'portal';
    protected $table = 'portal_usuarios';
    protected $guard = 'portal';

    use Notifiable;

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'email',
        'login',
        'password',
        'criado_por',
        'modificado_por'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'criado_por')->select('id', 'login');
    }

    public function updatedBy()
    {
        return $this->hasOne(User::class, 'id', 'modificado_por')->select('id', 'login');
    }

    public function privilege()
    {
        return $this->belongsTo(Privilege::class, 'privilegio_id', 'id')->select('id', 'titulo');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'setor_id', 'id')->select('id', 'titulo');
    }

    public function getAuthenticatedUserStructure()
    {
        return [
            'id' => $this->id,
            'name' => $this->nome,
            'login' => $this->login,
            'email' => $this->email,
            'privilege' => $this->privilege
        ];
    }

    public function ageReportRoles()
    {
        return $this->hasOne(UserRole::class, 'usuario_id', 'id')
            ->select(['id as permissao_id', 'usuario_id', 'nivel', 'relatorios_liberados', 'liberado_por'])
            ->with('releasedByUser');
    }

}
