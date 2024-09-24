<?php

namespace App\Models\AgeFacilitate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'age_facilita_tokens';
    protected $fillable = ['token', 'celular', 'status', 'data_alteracao_status'];
    protected $connection = 'portal';
}
