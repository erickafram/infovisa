<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProcessoSetorMunicipio extends Model
{
    protected $table = 'tipo_processo_setor_municipio';

    protected $fillable = [
        'tipo_processo_id',
        'municipio_id',
        'tipo_setor_id',
    ];

    public function tipoProcesso()
    {
        return $this->belongsTo(TipoProcesso::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function tipoSetor()
    {
        return $this->belongsTo(TipoSetor::class);
    }
}