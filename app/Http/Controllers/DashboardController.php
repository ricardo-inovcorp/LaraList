<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use App\Models\Categoria;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Mostrar o dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }

    /**
     * Obter estatísticas para o dashboard.
     */
    public function stats(): JsonResponse
    {
        $userId = Auth::id();
        
        // Inicializar arrays com valores vazios para garantir que todas chaves existam
        $estadosDefault = [
            'pendente' => 0,
            'em_progresso' => 0,
            'concluida' => 0,
            'cancelada' => 0
        ];
        
        $prioridadesDefault = [
            'baixa' => 0,
            'media' => 0,
            'alta' => 0,
            'urgente' => 0
        ];
        
        // Tarefas por estado
        $porEstado = Tarefa::where('utilizador_id', $userId)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get()
            ->pluck('total', 'estado')
            ->toArray();
            
        // Mesclar com os valores default para garantir que todas as chaves existam
        $porEstado = array_merge($estadosDefault, $porEstado);
        
        // Total de tarefas - soma dos estados
        $total = array_sum($porEstado);
        
        // Tarefas por prioridade
        $porPrioridade = Tarefa::where('utilizador_id', $userId)
            ->select('prioridade', DB::raw('count(*) as total'))
            ->groupBy('prioridade')
            ->get()
            ->pluck('total', 'prioridade')
            ->toArray();
            
        // Mesclar com os valores default para garantir que todas as chaves existam
        $porPrioridade = array_merge($prioridadesDefault, $porPrioridade);
        
        // Calcular taxas de conclusão por períodos
        $data15Dias = Carbon::now()->subDays(15)->startOfDay();
        $data30Dias = Carbon::now()->subDays(30)->startOfDay();
        
        // Tarefas totais criadas nos últimos 15 dias
        $totalUltimos15Dias = Tarefa::where('utilizador_id', $userId)
            ->where('created_at', '>=', $data15Dias)
            ->count();
            
        // Tarefas concluídas nos últimos 15 dias
        $concluidasUltimos15Dias = Tarefa::where('utilizador_id', $userId)
            ->where('estado', 'concluida')
            ->where('updated_at', '>=', $data15Dias)
            ->count();
            
        // Taxa de conclusão para 15 dias (%)
        $taxaConclusao15Dias = $totalUltimos15Dias > 0 
            ? round(($concluidasUltimos15Dias / $totalUltimos15Dias) * 100) 
            : 0;
            
        // Tarefas totais criadas nos últimos 30 dias
        $totalUltimos30Dias = Tarefa::where('utilizador_id', $userId)
            ->where('created_at', '>=', $data30Dias)
            ->count();
            
        // Tarefas concluídas nos últimos 30 dias
        $concluidasUltimos30Dias = Tarefa::where('utilizador_id', $userId)
            ->where('estado', 'concluida')
            ->where('updated_at', '>=', $data30Dias)
            ->count();
            
        // Taxa de conclusão para 30 dias (%)
        $taxaConclusao30Dias = $totalUltimos30Dias > 0 
            ? round(($concluidasUltimos30Dias / $totalUltimos30Dias) * 100) 
            : 0;
        
        // Tarefas por categoria
        $porCategoria = DB::table('tarefas')
            ->leftJoin('categorias', 'tarefas.categoria_id', '=', 'categorias.id')
            ->where('tarefas.utilizador_id', $userId)
            ->select(DB::raw('COALESCE(categorias.nome, "Sem categoria") as nome'), DB::raw('count(*) as total'))
            ->groupBy('categorias.nome')
            ->get();
        
        // Tendência de tarefas (últimos 7 dias)
        $dataInicio = Carbon::now()->subDays(6)->startOfDay();
        $dataFim = Carbon::now()->endOfDay();
        
        $tendencia = [];
        
        for ($i = 0; $i < 7; $i++) {
            $data = Carbon::now()->subDays(6 - $i);
            $dataFormatada = $data->format('d/m');
            
            $pendentes = Tarefa::where('utilizador_id', $userId)
                ->where('estado', 'pendente')
                ->whereDate('created_at', '<=', $data)
                ->count();
            
            $emProgresso = Tarefa::where('utilizador_id', $userId)
                ->where('estado', 'em_progresso')
                ->whereDate('created_at', '<=', $data)
                ->count();
            
            $concluidas = Tarefa::where('utilizador_id', $userId)
                ->where('estado', 'concluida')
                ->whereDate('created_at', '<=', $data)
                ->count();
            
            $tendencia[] = [
                'data' => $dataFormatada,
                'pendentes' => $pendentes,
                'em_progresso' => $emProgresso,
                'concluidas' => $concluidas
            ];
        }
        
        // Tarefas recentes (tarefas concluídas por dia nos últimos 7 dias)
        $recentes = [];
        
        for ($i = 0; $i < 7; $i++) {
            $data = Carbon::now()->subDays(6 - $i);
            $dataFormatada = $data->format('d/m');
            
            $total = Tarefa::where('utilizador_id', $userId)
                ->where('estado', 'concluida')
                ->whereDate('updated_at', $data)
                ->count();
            
            $recentes[] = [
                'data' => $dataFormatada,
                'total' => $total
            ];
        }
        
        // Próximas tarefas (com data de conclusão futura)
        $proximas = Tarefa::where('utilizador_id', $userId)
            ->whereIn('estado', ['pendente', 'em_progresso'])
            ->whereNotNull('data_conclusao')
            ->where('data_conclusao', '>=', Carbon::now())
            ->orderBy('data_conclusao')
            ->limit(5)
            ->get(['id', 'titulo', 'data_conclusao', 'prioridade']);
        
        // Tarefas vencidas (com data de conclusão passada)
        $vencidas = Tarefa::where('utilizador_id', $userId)
            ->whereIn('estado', ['pendente', 'em_progresso'])
            ->whereNotNull('data_conclusao')
            ->where('data_conclusao', '<', Carbon::now())
            ->orderBy('data_conclusao', 'desc')
            ->limit(5)
            ->get(['id', 'titulo', 'data_conclusao', 'prioridade']);
        
        return response()->json([
            'total' => $total,
            'por_estado' => $porEstado,
            'por_prioridade' => $porPrioridade,
            'por_categoria' => $porCategoria,
            'tendencia' => $tendencia,
            'recentes' => $recentes,
            'proximas' => $proximas,
            'vencidas' => $vencidas,
            'taxa_conclusao_15_dias' => $taxaConclusao15Dias,
            'taxa_conclusao_30_dias' => $taxaConclusao30Dias,
            'tarefas_15_dias' => [
                'total' => $totalUltimos15Dias,
                'concluidas' => $concluidasUltimos15Dias
            ],
            'tarefas_30_dias' => [
                'total' => $totalUltimos30Dias,
                'concluidas' => $concluidasUltimos30Dias
            ]
        ]);
    }
} 