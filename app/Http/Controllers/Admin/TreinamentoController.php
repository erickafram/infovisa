<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TreinamentoApresentacao;
use App\Models\TreinamentoEvento;
use App\Models\TreinamentoInscricao;
use App\Models\TreinamentoPergunta;
use App\Models\TreinamentoPerguntaOpcao;
use App\Models\TreinamentoPerguntaResposta;
use App\Models\TreinamentoSlide;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TreinamentoController extends Controller
{
    public function index()
    {
        $this->authorizeTreinamentos();

        $eventos = TreinamentoEvento::withCount(['inscricoes', 'apresentacoes'])
            ->with('criador')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('treinamentos.index', compact('eventos'));
    }

    public function create()
    {
        $this->authorizeTreinamentos();

        $evento = new TreinamentoEvento([
            'status' => 'planejado',
            'inscricoes_ativas' => true,
        ]);

        return view('treinamentos.form', compact('evento'));
    }

    public function store(Request $request)
    {
        $this->authorizeTreinamentos();

        $data = $this->validateEvento($request);
        $data['link_inscricao_token'] = Str::lower(Str::random(40));
        $data['criado_por'] = Auth::guard('interno')->id();
        $data['atualizado_por'] = Auth::guard('interno')->id();

        $evento = TreinamentoEvento::create($data);

        return redirect()
            ->route('admin.treinamentos.show', $evento)
            ->with('success', 'Evento de treinamento criado com sucesso.');
    }

    public function show(TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        $evento->load([
            'criador',
            'inscricoes' => fn ($query) => $query->latest(),
            'apresentacoes' => fn ($query) => $query->withCount('slides')->with(['slides.perguntas.opcoes', 'slides.perguntas.respostas']),
        ]);

        $evento->apresentacoes->each(function ($apresentacao) {
            $apresentacao->total_perguntas = $apresentacao->slides->sum(fn ($slide) => $slide->perguntas->count());
            $apresentacao->total_respostas = $apresentacao->slides->sum(
                fn ($slide) => $slide->perguntas->sum(fn ($pergunta) => $pergunta->respostas->count())
            );
        });

        $linkInscricao = route('treinamentos.public.inscricao', $evento->link_inscricao_token);

        return view('treinamentos.show', compact('evento', 'linkInscricao'));
    }

    public function edit(TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        return view('treinamentos.form', compact('evento'));
    }

    public function update(Request $request, TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        $data = $this->validateEvento($request);
        $data['atualizado_por'] = Auth::guard('interno')->id();

        $evento->update($data);

        return redirect()
            ->route('admin.treinamentos.show', $evento)
            ->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy(TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        $evento->delete();

        return redirect()
            ->route('admin.treinamentos.index')
            ->with('success', 'Evento removido com sucesso.');
    }

    public function storeApresentacao(Request $request, TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'status' => 'required|in:rascunho,publicada,arquivada',
        ]);

        $data['treinamento_evento_id'] = $evento->id;
        $data['criado_por'] = Auth::guard('interno')->id();

        $apresentacao = TreinamentoApresentacao::create($data);

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $apresentacao)
            ->with('success', 'Apresentação criada com sucesso.');
    }

    public function showApresentacao(TreinamentoApresentacao $apresentacao)
    {
        $this->authorizeTreinamentos();

        $apresentacao->load([
            'evento',
            'slides' => fn ($query) => $query->with(['perguntas.opcoes', 'perguntas.respostas'])->orderBy('ordem'),
        ]);

        $apresentacao->slides->each(function ($slide) {
            $slide->perguntas->each(function ($pergunta) {
                $pergunta->public_url = route('treinamentos.public.pergunta', $pergunta->token);
                $pergunta->qr_code_base64 = $this->generateQrCodeBase64($pergunta->public_url);
                $pergunta->estatisticas = $this->buildQuestionStats($pergunta);
            });
        });

        return view('treinamentos.apresentacao', compact('apresentacao'));
    }

    public function updateApresentacao(Request $request, TreinamentoApresentacao $apresentacao)
    {
        $this->authorizeTreinamentos();

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'status' => 'required|in:rascunho,publicada,arquivada',
        ]);

        $apresentacao->update($data);

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $apresentacao)
            ->with('success', 'Apresentação atualizada com sucesso.');
    }

    public function destroyApresentacao(TreinamentoApresentacao $apresentacao)
    {
        $this->authorizeTreinamentos();

        $evento = $apresentacao->evento;
        $apresentacao->delete();

        return redirect()
            ->route('admin.treinamentos.show', $evento)
            ->with('success', 'Apresentação removida com sucesso.');
    }

    public function createSlide(TreinamentoApresentacao $apresentacao)
    {
        $this->authorizeTreinamentos();

        $slide = new TreinamentoSlide([
            'ordem' => ((int) $apresentacao->slides()->max('ordem')) + 1,
        ]);

        return view('treinamentos.slide-form', compact('apresentacao', 'slide'));
    }

    public function storeSlide(Request $request, TreinamentoApresentacao $apresentacao)
    {
        $this->authorizeTreinamentos();

        $data = $this->validateSlide($request);
        $data['treinamento_apresentacao_id'] = $apresentacao->id;

        TreinamentoSlide::create($data);

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $apresentacao)
            ->with('success', 'Slide criado com sucesso.');
    }

    public function editSlide(TreinamentoSlide $slide)
    {
        $this->authorizeTreinamentos();

        $slide->load('apresentacao.evento');
        $apresentacao = $slide->apresentacao;

        return view('treinamentos.slide-form', compact('apresentacao', 'slide'));
    }

    public function updateSlide(Request $request, TreinamentoSlide $slide)
    {
        $this->authorizeTreinamentos();

        $data = $this->validateSlide($request);
        $slide->update($data);

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $slide->apresentacao)
            ->with('success', 'Slide atualizado com sucesso.');
    }

    public function destroySlide(TreinamentoSlide $slide)
    {
        $this->authorizeTreinamentos();

        $apresentacao = $slide->apresentacao;
        $slide->delete();

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $apresentacao)
            ->with('success', 'Slide removido com sucesso.');
    }

    public function createPergunta(TreinamentoSlide $slide)
    {
        $this->authorizeTreinamentos();

        $slide->load('apresentacao.evento');
        $pergunta = new TreinamentoPergunta(['ativa' => true]);
        $opcoes = collect(['', '', '', '']);

        return view('treinamentos.pergunta-form', compact('slide', 'pergunta', 'opcoes'));
    }

    public function storePergunta(Request $request, TreinamentoSlide $slide)
    {
        $this->authorizeTreinamentos();

        $data = $this->validatePergunta($request);

        $pergunta = TreinamentoPergunta::create([
            'treinamento_slide_id' => $slide->id,
            'enunciado' => $data['enunciado'],
            'token' => (string) Str::uuid(),
            'ativa' => $request->boolean('ativa', true),
        ]);

        $this->syncQuestionOptions($pergunta, $data['opcoes']);

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $slide->apresentacao)
            ->with('success', 'Pergunta criada com sucesso.');
    }

    public function editPergunta(TreinamentoPergunta $pergunta)
    {
        $this->authorizeTreinamentos();

        $pergunta->load('opcoes', 'slide.apresentacao.evento', 'respostas');
        $slide = $pergunta->slide;
        $opcoes = $pergunta->opcoes->sortBy('ordem')->pluck('texto')->values();
        while ($opcoes->count() < 4) {
            $opcoes->push('');
        }

        return view('treinamentos.pergunta-form', compact('slide', 'pergunta', 'opcoes'));
    }

    public function updatePergunta(Request $request, TreinamentoPergunta $pergunta)
    {
        $this->authorizeTreinamentos();

        $data = $this->validatePergunta($request);

        $pergunta->update([
            'enunciado' => $data['enunciado'],
            'ativa' => $request->boolean('ativa', false),
        ]);

        $this->syncQuestionOptions($pergunta, $data['opcoes']);

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $pergunta->slide->apresentacao)
            ->with('success', 'Pergunta atualizada com sucesso.');
    }

    public function destroyPergunta(TreinamentoPergunta $pergunta)
    {
        $this->authorizeTreinamentos();

        $apresentacao = $pergunta->slide->apresentacao;
        $pergunta->delete();

        return redirect()
            ->route('admin.treinamentos.apresentacoes.show', $apresentacao)
            ->with('success', 'Pergunta removida com sucesso.');
    }

    public function apresentar(TreinamentoApresentacao $apresentacao)
    {
        $this->authorizeTreinamentos();

        $apresentacao->load([
            'evento',
            'slides' => fn ($query) => $query->with(['perguntas.opcoes', 'perguntas.respostas'])->orderBy('ordem'),
        ]);

        $slidesPayload = $apresentacao->slides->map(function ($slide) {
            return [
                'id' => $slide->id,
                'titulo' => $slide->titulo,
                'conteudo' => $slide->conteudo,
                'ordem' => $slide->ordem,
                'perguntas' => $slide->perguntas->map(function ($pergunta) {
                    return [
                        'id' => $pergunta->id,
                        'enunciado' => $pergunta->enunciado,
                        'ativa' => (bool) $pergunta->ativa,
                        'url' => route('treinamentos.public.pergunta', $pergunta->token),
                        'qr_code_base64' => $this->generateQrCodeBase64(route('treinamentos.public.pergunta', $pergunta->token)),
                        'opcoes' => $pergunta->opcoes->map(fn ($opcao) => [
                            'id' => $opcao->id,
                            'texto' => $opcao->texto,
                        ])->values(),
                        'resultados_url' => route('admin.treinamentos.perguntas.resultados', $pergunta),
                        'estatisticas' => $this->buildQuestionStats($pergunta),
                    ];
                })->values(),
            ];
        })->values();

        return view('treinamentos.apresentar', compact('apresentacao', 'slidesPayload'));
    }

    public function resultadosPergunta(TreinamentoPergunta $pergunta)
    {
        $this->authorizeTreinamentos();

        $pergunta->loadMissing(['opcoes', 'respostas']);

        return response()->json($this->buildQuestionStats($pergunta));
    }

    public function relatorioInscritos(TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        $evento->load(['inscricoes' => fn ($query) => $query->latest()]);

        return view('treinamentos.relatorios-inscritos', compact('evento'));
    }

    public function relatorioRespostas(TreinamentoEvento $evento)
    {
        $this->authorizeTreinamentos();

        $evento->load([
            'apresentacoes' => fn ($query) => $query->with([
                'slides' => fn ($slides) => $slides->with(['perguntas.opcoes', 'perguntas.respostas'])->orderBy('ordem'),
            ]),
        ]);

        $evento->apresentacoes->each(function ($apresentacao) {
            $apresentacao->slides->each(function ($slide) {
                $slide->perguntas->each(function ($pergunta) {
                    $pergunta->estatisticas = $this->buildQuestionStats($pergunta);
                    $pergunta->public_url = route('treinamentos.public.pergunta', $pergunta->token);
                });
            });
        });

        return view('treinamentos.relatorios-respostas', compact('evento'));
    }

    private function authorizeTreinamentos(): void
    {
        $usuario = Auth::guard('interno')->user();

        abort_unless(
            $usuario && ($usuario->isAdmin() || $usuario->nivel_acesso->value === 'gestor_estadual'),
            403,
            'Acesso não autorizado ao módulo de treinamentos.'
        );
    }

    private function validateEvento(Request $request): array
    {
        return $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'local' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'status' => 'required|in:planejado,aberto,encerrado,cancelado',
            'inscricoes_ativas' => 'nullable|boolean',
        ]);
    }

    private function validateSlide(Request $request): array
    {
        return $request->validate([
            'titulo' => 'required|string|max:255',
            'conteudo' => 'nullable|string',
            'ordem' => 'required|integer|min:1',
        ]);
    }

    private function validatePergunta(Request $request): array
    {
        $data = $request->validate([
            'enunciado' => 'required|string',
            'ativa' => 'nullable|boolean',
            'opcoes' => 'required|array|min:2',
            'opcoes.*' => 'nullable|string|max:255',
        ]);

        $opcoes = collect($data['opcoes'])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values();

        if ($opcoes->count() < 2) {
            abort(422, 'Informe pelo menos duas alternativas para a pergunta.');
        }

        $data['opcoes'] = $opcoes->all();

        return $data;
    }

    private function syncQuestionOptions(TreinamentoPergunta $pergunta, array $options): void
    {
        $existing = $pergunta->opcoes()->orderBy('ordem')->get();
        $hasResponses = $pergunta->respostas()->exists();

        if ($hasResponses && count($options) !== $existing->count()) {
            abort(422, 'Não é possível alterar a quantidade de alternativas após o recebimento de respostas.');
        }

        foreach ($options as $index => $texto) {
            if (isset($existing[$index])) {
                $existing[$index]->update([
                    'texto' => $texto,
                    'ordem' => $index + 1,
                ]);
                continue;
            }

            TreinamentoPerguntaOpcao::create([
                'treinamento_pergunta_id' => $pergunta->id,
                'texto' => $texto,
                'ordem' => $index + 1,
            ]);
        }

        if (!$hasResponses && $existing->count() > count($options)) {
            $existing->slice(count($options))->each->delete();
        }
    }

    private function buildQuestionStats(TreinamentoPergunta $pergunta): array
    {
        $pergunta->loadMissing(['opcoes', 'respostas']);
        $total = $pergunta->respostas->count();

        $opcoes = $pergunta->opcoes->map(function ($opcao) use ($pergunta, $total) {
            $count = $pergunta->respostas->where('treinamento_pergunta_opcao_id', $opcao->id)->count();

            return [
                'id' => $opcao->id,
                'texto' => $opcao->texto,
                'quantidade' => $count,
                'percentual' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        })->values();

        return [
            'total_respostas' => $total,
            'opcoes' => $opcoes,
        ];
    }

    private function generateQrCodeBase64(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $qr = new QrCode($url);
            $writer = new PngWriter();

            return base64_encode($writer->write($qr)->getString());
        } catch (\Throwable $e) {
            return null;
        }
    }
}
