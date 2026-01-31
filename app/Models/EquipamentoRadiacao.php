<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipamentoRadiacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipamentos_radiacao';

    protected $fillable = [
        'estabelecimento_id',
        'tipo_equipamento',
        'fabricante',
        'modelo',
        'numero_serie',
        'ano_fabricacao',
        'registro_anvisa',
        'numero_patrimonio',
        'setor_localizacao',
        'sala',
        'data_ultima_calibracao',
        'data_proxima_calibracao',
        'responsavel_tecnico',
        'registro_cnen',
        'status',
        'observacoes',
        'usuario_externo_id',
    ];

    protected $casts = [
        'data_ultima_calibracao' => 'date',
        'data_proxima_calibracao' => 'date',
    ];

    /**
     * Status disponíveis
     */
    const STATUS_ATIVO = 'ativo';
    const STATUS_INATIVO = 'inativo';
    const STATUS_EM_MANUTENCAO = 'em_manutencao';
    const STATUS_DESCARTADO = 'descartado';

    /**
     * Lista de status com labels
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ATIVO => 'Ativo',
            self::STATUS_INATIVO => 'Inativo',
            self::STATUS_EM_MANUTENCAO => 'Em Manutenção',
            self::STATUS_DESCARTADO => 'Descartado',
        ];
    }

    /**
     * Tipos comuns de equipamentos de radiação ionizante
     */
    public static function getTiposEquipamento(): array
    {
        return [
            // Raio-X Odontológico
            'Raio-X Odontológico Intraoral',
            'Raio-X Odontológico Panorâmico',
            'Raio-X Odontológico Cefalométrico',
            'Tomógrafo Computadorizado Cone Beam (CBCT)',
            
            // Raio-X Médico
            'Raio-X Médico Fixo',
            'Raio-X Médico Móvel',
            'Raio-X Médico Portátil',
            'Raio-X de Tórax',
            'Raio-X de Coluna',
            'Raio-X de Extremidades',
            
            // Tomografia e Diagnóstico
            'Tomógrafo Computadorizado (TC)',
            'Tomógrafo Multislice',
            'Ressonância Magnética (RM)',
            
            // Mamografia
            'Mamógrafo Analógico',
            'Mamógrafo Digital',
            'Mamógrafo com Tomossíntese',
            
            // Densitometria
            'Densitômetro Ósseo (DEXA)',
            
            // Fluoroscopia e Hemodinâmica
            'Fluoroscópio',
            'Angiografia',
            'Hemodinâmica',
            'Arco Cirúrgico (C-Arm)',
            'Mini C-Arm',
            
            // Medicina Nuclear
            'Gama Câmara',
            'SPECT',
            'SPECT-CT',
            'PET-CT',
            'PET-RM',
            'Cintilógrafo',
            
            // Radioterapia
            'Equipamento de Radioterapia',
            'Acelerador Linear (LINAC)',
            'Braquiterapia HDR',
            'Braquiterapia LDR',
            'Cobalto-60',
            'CyberKnife',
            'Gamma Knife',
            'Tomoterapia',
            
            // Ultrassom (para serviços de diagnóstico por imagem)
            'Ultrassom Convencional',
            'Ultrassom Doppler',
            'Ultrassom 3D/4D',
            'Ecocardiograma',
            
            // Outros
            'Litotriptor',
            'Equipamento de Radiologia Intervencionista',
            'Outro',
        ];
    }

    /**
     * Relacionamento com o estabelecimento
     */
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    /**
     * Relacionamento com o usuário externo que cadastrou
     */
    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class);
    }

    /**
     * Scope para equipamentos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    /**
     * Scope para filtrar por estabelecimento
     */
    public function scopeDoEstabelecimento($query, $estabelecimentoId)
    {
        return $query->where('estabelecimento_id', $estabelecimentoId);
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? 'Desconhecido';
    }

    /**
     * Retorna a cor do badge do status
     */
    public function getStatusCorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ATIVO => 'bg-green-100 text-green-800',
            self::STATUS_INATIVO => 'bg-gray-100 text-gray-800',
            self::STATUS_EM_MANUTENCAO => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DESCARTADO => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Verifica se a calibração está vencida
     */
    public function calibracaoVencida(): bool
    {
        if (!$this->data_proxima_calibracao) {
            return false;
        }

        return $this->data_proxima_calibracao->isPast();
    }

    /**
     * Verifica se a calibração está próxima de vencer (30 dias)
     */
    public function calibracaoProximaVencer(): bool
    {
        if (!$this->data_proxima_calibracao) {
            return false;
        }

        return $this->data_proxima_calibracao->isBetween(now(), now()->addDays(30));
    }
}
