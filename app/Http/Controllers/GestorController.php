<?php

namespace App\Http\Controllers;

use App\Models\Espaco;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GestorController extends Controller
{
    public function pedidos(Request $request): View
    {
        $estado = $request->query('estado', 'APROVADA');
        $estadosPermitidos = ['APROVADA', 'RECUSADA', 'TODAS'];

        if (! in_array($estado, $estadosPermitidos, true)) {
            $estado = 'APROVADA';
        }

        $pedidosVisiveis = Pedido::query()
            ->when(Auth::user()->perfil !== 'ADMINISTRADOR', function ($query): void {
                $query->whereHas('espaco.gestores', function ($gestores): void {
                    $gestores->where('utilizadores.id', Auth::id());
                });
            });

        $pedidosFiltrados = (clone $pedidosVisiveis)
            ->when($estado !== 'TODAS', function ($query) use ($estado): void {
                $query->where('estado', $estado);
            })
            ->with(['espaco', 'utilizador'])
            ->orderBy('inicio')
            ->get();

        $aprovadasSemana = (clone $pedidosVisiveis)
            ->where('estado', 'APROVADA')
            ->whereBetween('inicio', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $indisponibilidades = DB::table('indisponibilidades')
            ->where('inicio', '<=', now())
            ->where('fim', '>', now())
            ->when(Auth::user()->perfil !== 'ADMINISTRADOR', function ($query): void {
                $query->whereIn('espaco_id', Espaco::query()
                    ->whereHas('gestores', function ($gestores): void {
                        $gestores->where('utilizadores.id', Auth::id());
                    })
                    ->select('id'));
            })
            ->count();

        return view('gestor.pedidos', [
            'pedidos' => $pedidosFiltrados,
            'estadoAtual' => $estado,
            'aprovadasSemana' => $aprovadasSemana,
            'indisponibilidades' => $indisponibilidades,
        ]);
    }

    public function mostrarRecusa(Pedido $pedido): View
    {
        $this->garantirAcessoAoEspaco($pedido);

        if ($pedido->estado !== 'APROVADA') {
            abort(404);
        }

        $pedido->load(['espaco', 'utilizador']);

        return view('gestor.recusar', ['pedido' => $pedido]);
    }

    public function mostrarMotivo(Pedido $pedido): View
    {
        $this->garantirAcessoAoEspaco($pedido);

        if ($pedido->estado !== 'RECUSADA') {
            abort(404);
        }

        $pedido->load(['espaco', 'utilizador']);

        return view('gestor.motivo', ['pedido' => $pedido]);
    }

    public function editarPedido(Pedido $pedido): View
    {
        $this->garantirAcessoAoEspaco($pedido);

        if ($pedido->estado !== 'RECUSADA') {
            abort(404);
        }

        $pedido->load(['espaco', 'utilizador']);

        return view('gestor.editar-pedido', [
            'pedido' => $pedido,
            'espacos' => $this->espacosDoGestor(),
        ]);
    }

    public function atualizarPedido(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->garantirAcessoAoEspaco($pedido);

        if ($pedido->estado !== 'RECUSADA') {
            abort(404);
        }

        $dados = $request->validate([
            'espaco_id' => ['required', 'integer', 'exists:espacos,id'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date', 'after:inicio'],
            'estado' => ['required', 'in:APROVADA,RECUSADA'],
            'justificacao_decisao' => ['required', 'string', 'min:3'],
        ]);

        $espaco = Espaco::query()->findOrFail($dados['espaco_id']);
        $this->garantirAcessoAoEspaco($espaco);

        if ($dados['estado'] === 'APROVADA') {
            if ($pedido->participantes > $espaco->capacidade) {
                return back()->withErrors(['espaco_id' => 'A sala escolhida não tem capacidade para esta reserva.'])->withInput();
            }

            $temIndisponibilidade = DB::table('indisponibilidades')
                ->where('espaco_id', $espaco->id)
                ->where('inicio', '<', $dados['fim'])
                ->where('fim', '>', $dados['inicio'])
                ->exists();

            if ($temIndisponibilidade) {
                return back()->withErrors(['espaco_id' => 'A sala está indisponível nesse período.'])->withInput();
            }

            $temConflito = Pedido::query()
                ->where('id', '!=', $pedido->id)
                ->where('espaco_id', $espaco->id)
                ->where('estado', 'APROVADA')
                ->where('inicio', '<', $dados['fim'])
                ->where('fim', '>', $dados['inicio'])
                ->exists();

            if ($temConflito) {
                return back()->withErrors(['espaco_id' => 'Já existe uma reserva aprovada nesse período.'])->withInput();
            }
        }

        $pedido->update([
            'espaco_id' => $dados['espaco_id'],
            'inicio' => $dados['inicio'],
            'fim' => $dados['fim'],
            'estado' => $dados['estado'],
            'decidido_por' => Auth::id(),
            'decidido_em' => now(),
            'justificacao_decisao' => $dados['justificacao_decisao'],
        ]);

        return redirect('/gestor?estado='.($dados['estado'] === 'APROVADA' ? 'APROVADA' : 'RECUSADA'))
            ->with('sucesso', 'Reserva atualizada.');
    }

    public function ocupacao(Request $request): View
    {
        $periodo = $request->query('periodo', 'semana');
        $periodosPermitidos = ['semana', 'mes'];

        if (! in_array($periodo, $periodosPermitidos, true)) {
            $periodo = 'semana';
        }

        $inicioPeriodo = $periodo === 'mes' ? now()->startOfMonth() : now()->startOfWeek();
        $fimPeriodo = $periodo === 'mes' ? now()->endOfMonth() : now()->endOfWeek();
        $espacosVisiveis = Espaco::query()
            ->where('ativo', true)
            ->when(Auth::user()->perfil !== 'ADMINISTRADOR', function ($query): void {
                $query->whereHas('gestores', function ($gestores): void {
                    $gestores->where('utilizadores.id', Auth::id());
                });
            });
        $espacos = (clone $espacosVisiveis)
            ->when($request->filled('espaco'), function ($query) use ($request): void {
                $query->whereKey($request->integer('espaco'));
            })
            ->when($request->filled('tipo'), function ($query) use ($request): void {
                $query->where('tipo', $request->string('tipo')->toString());
            })
            ->orderBy('edificio')
            ->orderBy('nome')
            ->get();

        $reservas = Pedido::query()
            ->where('estado', 'APROVADA')
            ->where('inicio', '<', $fimPeriodo)
            ->where('fim', '>', $inicioPeriodo)
            ->whereIn('espaco_id', $espacos->pluck('id'))
            ->get(['espaco_id', 'inicio', 'fim']);

        $duracaoPeriodo = max(1, $inicioPeriodo->diffInHours($fimPeriodo));
        $ocupacao = $espacos->map(function ($espaco) use ($reservas, $inicioPeriodo, $fimPeriodo, $duracaoPeriodo): array {
            $horasReservadas = $reservas
                ->where('espaco_id', $espaco->id)
                ->sum(function ($reserva) use ($inicioPeriodo, $fimPeriodo): float {
                    $inicio = $reserva->inicio->greaterThan($inicioPeriodo) ? $reserva->inicio : $inicioPeriodo;
                    $fim = $reserva->fim->lessThan($fimPeriodo) ? $reserva->fim : $fimPeriodo;

                    return max(0, $inicio->diffInMinutes($fim) / 60);
                });

            return [
                'espaco' => $espaco,
                'percentagem' => min(100, (int) round(($horasReservadas / $duracaoPeriodo) * 100)),
            ];
        });

        return view('gestor.ocupacao', [
            'espacos' => (clone $espacosVisiveis)->orderBy('edificio')->orderBy('nome')->get(),
            'tipos' => (clone $espacosVisiveis)->whereNotNull('tipo')->distinct()->orderBy('tipo')->pluck('tipo'),
            'ocupacao' => $ocupacao,
            'periodo' => $periodo,
            'totalReservas' => $reservas->count(),
            'mediaOcupacao' => $ocupacao->count() > 0 ? (int) round($ocupacao->avg('percentagem')) : 0,
        ]);
    }

    public function indisponibilidades(Request $request): View
    {
        $estado = $request->query('estado', 'TODOS');
        $estadosPermitidos = ['CANCELADO', 'MANUTENCAO', 'INDISPONIVEL', 'DISPONIVEL', 'TODOS'];

        if (! in_array($estado, $estadosPermitidos, true)) {
            $estado = 'TODOS';
        }

        $espacosVisiveis = Espaco::query()
            ->where('ativo', true)
            ->when(Auth::user()->perfil !== 'ADMINISTRADOR', function ($query): void {
                $query->whereHas('gestores', function ($gestores): void {
                    $gestores->where('utilizadores.id', Auth::id());
                });
            });

        $registos = DB::table('indisponibilidades')
            ->join('espacos', 'espacos.id', '=', 'indisponibilidades.espaco_id')
            ->whereIn('indisponibilidades.espaco_id', (clone $espacosVisiveis)->select('id'))
            ->select('indisponibilidades.*', 'espacos.nome', 'espacos.edificio')
            ->orderBy('indisponibilidades.inicio')
            ->get()
            ->map(function (object $item): object {
                $motivo = mb_strtolower($item->motivo ?? '');
                $item->estado = str_contains($motivo, 'manuten') ? 'MANUTENCAO' : (str_contains($motivo, 'cancel') ? 'CANCELADO' : 'INDISPONIVEL');

                return $item;
            });

        $agora = now();
        $espacosDisponiveis = (clone $espacosVisiveis)->get()->filter(function ($espaco) use ($registos, $agora): bool {
            return ! $registos->contains(function (object $registo) use ($espaco, $agora): bool {
                return (int) $registo->espaco_id === $espaco->id
                    && Carbon::parse($registo->inicio)->lessThanOrEqualTo($agora)
                    && Carbon::parse($registo->fim)->greaterThan($agora);
            });
        })->map(function ($espaco): object {
            return (object) [
                'id' => null,
                'espaco_id' => $espaco->id,
                'nome' => $espaco->nome,
                'edificio' => $espaco->edificio,
                'inicio' => $espaco->created_at ?? now(),
                'fim' => now()->endOfDay(),
                'motivo' => 'Espaço disponível',
                'estado' => 'DISPONIVEL',
            ];
        });

        $indisponibilidades = $registos->merge($espacosDisponiveis);
        if ($estado !== 'TODOS') {
            $indisponibilidades = $indisponibilidades->filter(fn (object $item): bool => $item->estado === $estado);
        }

        return view('gestor.indisponibilidades', [
            'indisponibilidades' => $indisponibilidades,
            'estadoAtual' => $estado,
            'contagens' => [
                'DISPONIVEL' => $espacosDisponiveis->count(),
                'MANUTENCAO' => DB::table('indisponibilidades')->whereIn('espaco_id', (clone $espacosVisiveis)->select('id'))->where('motivo', 'ilike', '%manuten%')->count(),
                'INDISPONIVEL' => DB::table('indisponibilidades')->whereIn('espaco_id', (clone $espacosVisiveis)->select('id'))->where('motivo', 'not ilike', '%manuten%')->where('motivo', 'not ilike', '%cancel%')->count(),
                'CANCELADO' => DB::table('indisponibilidades')->whereIn('espaco_id', (clone $espacosVisiveis)->select('id'))->where('motivo', 'ilike', '%cancel%')->count(),
            ],
        ]);
    }

    public function criarIndisponibilidade(): View
    {
        return view('gestor.criar-indisponibilidade', [
            'espacos' => $this->espacosDoGestor(),
            'estados' => $this->estadosIndisponibilidade(),
        ]);
    }

    public function guardarIndisponibilidade(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'espaco_id' => ['required', 'integer', 'exists:espacos,id'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date', 'after:inicio'],
            'estado' => ['required', Rule::in(array_keys($this->estadosIndisponibilidade()))],
        ]);

        $this->garantirAcessoAoEspaco(Espaco::query()->findOrFail($dados['espaco_id']));

        if ($dados['estado'] === 'DISPONIVEL') {
            return redirect('/gestor/indisponibilidades')->with('sucesso', 'A sala permanece disponível.');
        }

        DB::table('indisponibilidades')->insert([
            'espaco_id' => $dados['espaco_id'],
            'inicio' => $dados['inicio'],
            'fim' => $dados['fim'],
            'motivo' => $this->estadosIndisponibilidade()[$dados['estado']],
            'criado_por' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect('/gestor/indisponibilidades')->with('sucesso', 'Indisponibilidade criada.');
    }

    public function apagarIndisponibilidade(int $indisponibilidade): RedirectResponse
    {
        $registo = DB::table('indisponibilidades')->where('id', $indisponibilidade)->first();

        abort_unless($registo !== null, 404);
        $this->garantirAcessoAoEspaco(Espaco::query()->findOrFail($registo->espaco_id));
        DB::table('indisponibilidades')->where('id', $indisponibilidade)->delete();

        return back()->with('sucesso', 'Indisponibilidade apagada.');
    }

    public function editarIndisponibilidade(int $indisponibilidade): View
    {
        $registo = DB::table('indisponibilidades')->where('id', $indisponibilidade)->first();

        abort_unless($registo !== null, 404);
        $espaco = Espaco::query()->findOrFail($registo->espaco_id);
        $this->garantirAcessoAoEspaco($espaco);

        return view('gestor.editar-indisponibilidade', [
            'indisponibilidade' => $registo,
            'espacos' => $this->espacosDoGestor(),
            'estados' => $this->estadosIndisponibilidade(),
            'estadoAtual' => $this->estadoDaIndisponibilidade($registo->motivo),
        ]);
    }

    public function atualizarIndisponibilidade(Request $request, int $indisponibilidade): RedirectResponse
    {
        $registo = DB::table('indisponibilidades')->where('id', $indisponibilidade)->first();

        abort_unless($registo !== null, 404);
        $this->garantirAcessoAoEspaco(Espaco::query()->findOrFail($registo->espaco_id));

        $dados = $request->validate([
            'espaco_id' => ['required', 'integer', 'exists:espacos,id'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date', 'after:inicio'],
            'estado' => ['required', Rule::in(array_keys($this->estadosIndisponibilidade()))],
        ]);

        $this->garantirAcessoAoEspaco(Espaco::query()->findOrFail($dados['espaco_id']));

        if ($dados['estado'] === 'DISPONIVEL') {
            DB::table('indisponibilidades')->where('id', $indisponibilidade)->delete();

            return redirect('/gestor/indisponibilidades')->with('sucesso', 'A sala ficou disponível.');
        }

        DB::table('indisponibilidades')->where('id', $indisponibilidade)->update([
            'espaco_id' => $dados['espaco_id'],
            'inicio' => $dados['inicio'],
            'fim' => $dados['fim'],
            'motivo' => $this->estadosIndisponibilidade()[$dados['estado']],
        ]);

        return redirect('/gestor/indisponibilidades')->with('sucesso', 'Indisponibilidade atualizada.');
    }

    public function salas(): View
    {
        return view('gestor.salas', ['espacos' => $this->espacosDoGestor()->load('equipamentos')]);
    }

    public function criarSalaForm(): View
    {
        return view('gestor.criar-sala');
    }

    public function criarSala(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'edificio' => ['required', 'string', 'max:100'],
            'nome' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::in(['SALA', 'LABORATORIO', 'AUDITORIO', 'ESPACO_ESTUDO'])],
            'capacidade' => ['required', 'integer', 'min:1'],
            'piso' => ['nullable', 'integer'],
            'descricao' => ['nullable', 'string'],
        ]);

        $dados['ativo'] = true;
        $espaco = Espaco::query()->create($dados);

        if (Auth::user()->perfil !== 'ADMINISTRADOR') {
            $espaco->gestores()->syncWithoutDetaching([Auth::id()]);
        }

        return redirect('/gestor/salas/'.$espaco->id.'/editar')->with('sucesso', 'Sala adicionada com sucesso.');
    }

    public function editarSala(Espaco $espaco): View
    {
        $this->garantirAcessoAoEspaco($espaco);

        return view('gestor.editar-sala', ['espaco' => $espaco->load('equipamentos')]);
    }

    public function adicionarEquipamento(Request $request, Espaco $espaco): RedirectResponse
    {
        $this->garantirAcessoAoEspaco($espaco);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string'],
            'reservavel' => ['nullable', 'boolean'],
        ]);

        $espaco->equipamentos()->create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'reservavel' => $request->boolean('reservavel'),
            'ativo' => true,
        ]);

        return back()->with('sucesso', 'Equipamento adicionado à sala.');
    }

    public function atualizarSala(Request $request, Espaco $espaco): RedirectResponse
    {
        $this->garantirAcessoAoEspaco($espaco);

        $dados = $request->validate([
            'edificio' => ['required', 'string', 'max:100'],
            'nome' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::in(['SALA', 'LABORATORIO', 'AUDITORIO', 'ESPACO_ESTUDO'])],
            'capacidade' => ['required', 'integer', 'min:1'],
            'piso' => ['nullable', 'integer'],
            'descricao' => ['nullable', 'string'],
        ]);

        $espaco->update($dados);

        return redirect('/gestor/salas')->with('sucesso', 'Sala atualizada com sucesso.');
    }

    private function espacosDoGestor()
    {
        return Espaco::query()
            ->where('ativo', true)
            ->when(Auth::user()->perfil !== 'ADMINISTRADOR', function ($query): void {
                $query->whereHas('gestores', function ($gestores): void {
                    $gestores->where('utilizadores.id', Auth::id());
                });
            })
            ->orderBy('edificio')
            ->orderBy('nome')
            ->get();
    }

    private function estadosIndisponibilidade(): array
    {
        return [
            'DISPONIVEL' => 'Disponível',
            'INDISPONIVEL' => 'Indisponível',
            'CANCELADO' => 'Cancelada',
            'MANUTENCAO' => 'Manutenção',
        ];
    }

    private function estadoDaIndisponibilidade(?string $motivo): string
    {
        $motivo = mb_strtolower($motivo ?? '');

        return match (true) {
            str_contains($motivo, 'manuten') => 'MANUTENCAO',
            str_contains($motivo, 'cancel') => 'CANCELADO',
            str_contains($motivo, 'dispon') && ! str_contains($motivo, 'indispon') => 'DISPONIVEL',
            default => 'INDISPONIVEL',
        };
    }

    public function recusar(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->garantirAcessoAoEspaco($pedido);

        $dados = $request->validate([
            'justificacao_decisao' => ['required', 'string', 'min:3'],
        ]);

        $pedido->update([
            'estado' => 'RECUSADA',
            'decidido_por' => Auth::id(),
            'decidido_em' => now(),
            'justificacao_decisao' => $dados['justificacao_decisao'],
        ]);

        return redirect('/gestor?estado=RECUSADA')->with('sucesso', 'Pedido recusado.');
    }

    private function garantirAcessoAoEspaco(Pedido|Espaco $registo): void
    {
        $espaco = $registo instanceof Pedido ? $registo->espaco : $registo;

        if (Auth::user()->perfil !== 'ADMINISTRADOR' && ! $espaco->gestores()->whereKey(Auth::id())->exists()) {
            abort(403);
        }
    }
}
