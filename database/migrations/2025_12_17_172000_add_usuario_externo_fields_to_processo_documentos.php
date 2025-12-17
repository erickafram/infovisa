<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('processo_documentos', 'usuario_externo_id')) {
                $table->foreignId('usuario_externo_id')->nullable()->after('usuario_id')->constrained('usuarios_externos')->onDelete('set null');
            }
            if (!Schema::hasColumn('processo_documentos', 'tipo_usuario')) {
                $table->string('tipo_usuario')->default('interno')->after('usuario_externo_id');
            }
            if (!Schema::hasColumn('processo_documentos', 'status_aprovacao')) {
                $table->string('status_aprovacao')->nullable()->after('tipo_usuario');
            }
            if (!Schema::hasColumn('processo_documentos', 'motivo_rejeicao')) {
                $table->text('motivo_rejeicao')->nullable()->after('status_aprovacao');
            }
            if (!Schema::hasColumn('processo_documentos', 'aprovado_por')) {
                $table->foreignId('aprovado_por')->nullable()->after('motivo_rejeicao')->constrained('usuarios_internos')->onDelete('set null');
            }
            if (!Schema::hasColumn('processo_documentos', 'aprovado_em')) {
                $table->timestamp('aprovado_em')->nullable()->after('aprovado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            if (Schema::hasColumn('processo_documentos', 'usuario_externo_id')) {
                $table->dropForeign(['usuario_externo_id']);
                $table->dropColumn('usuario_externo_id');
            }
            if (Schema::hasColumn('processo_documentos', 'tipo_usuario')) {
                $table->dropColumn('tipo_usuario');
            }
            if (Schema::hasColumn('processo_documentos', 'status_aprovacao')) {
                $table->dropColumn('status_aprovacao');
            }
            if (Schema::hasColumn('processo_documentos', 'motivo_rejeicao')) {
                $table->dropColumn('motivo_rejeicao');
            }
            if (Schema::hasColumn('processo_documentos', 'aprovado_por')) {
                $table->dropForeign(['aprovado_por']);
                $table->dropColumn('aprovado_por');
            }
            if (Schema::hasColumn('processo_documentos', 'aprovado_em')) {
                $table->dropColumn('aprovado_em');
            }
        });
    }
};
