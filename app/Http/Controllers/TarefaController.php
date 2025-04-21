<?php

namespace App\Http\Controllers;

use App\Http\Requests\TarefaRequest;
use App\Models\Tarefa;
use App\Models\Categoria;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TarefaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth']); // Removido o middleware verified para testes
    }

    /**
     * Mostrar listagem das tarefas.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Adiciona um log para debug
        Log::info('User ID: ' . ($user ? $user->id : 'null'));
        
        // Query básica - sem tentar acessar relação tarefas diretamente
        $query = Tarefa::query()
            ->where('utilizador_id', $user->id)
            ->with('categoria'); // Carrega a relação de categoria
        
        // Busca por título ou descrição
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('titulo', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%");
            });
        }
        
        // Filtragem
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }
        
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        
        // Ordenação
        $orderBy = 'created_at';
        $direction = 'desc';
        
        if ($request->filled('ordem')) {
            $ordem = $request->ordem;
            
            if ($ordem === 'prioridade_asc') {
                $orderBy = 'prioridade';
                $direction = 'asc';
            } elseif ($ordem === 'prioridade_desc') {
                $orderBy = 'prioridade';
                $direction = 'desc';
            } elseif ($ordem === 'data_conclusao_asc') {
                $orderBy = 'data_conclusao';
                $direction = 'asc';
            } elseif ($ordem === 'data_conclusao_desc') {
                $orderBy = 'data_conclusao';
                $direction = 'desc';
            } elseif ($ordem === 'created_at_asc') {
                $orderBy = 'created_at';
                $direction = 'asc';
            } elseif ($ordem === 'created_at_desc') {
                $orderBy = 'created_at';
                $direction = 'desc';
            }
        }
        
        $query->orderBy($orderBy, $direction);
        
        // Paginação
        $tarefas = $query->paginate(10)->withQueryString();
        
        // Log do número de resultados para debug
        Log::info('Número de tarefas: ' . $tarefas->count());
        
        // Busca todas as categorias do usuário para o filtro
        $categorias = Categoria::where('utilizador_id', $user->id)->get();
        
        // Filtros para a view
        $filtrosAtivos = [
            'estado' => $request->estado,
            'prioridade' => $request->prioridade,
            'categoria_id' => $request->categoria_id,
            'busca' => $request->busca,
        ];

        return Inertia::render('Tarefas/Index', [
            'tarefas' => $tarefas,
            'categorias' => $categorias,
            'filtrosAtivos' => $filtrosAtivos,
        ]);
    }

    /**
     * Mostrar formulário para criar uma nova tarefa.
     */
    public function create(): Response
    {
        // Busca todas as categorias do usuário para o form
        $categorias = Categoria::where('utilizador_id', Auth::id())->get();
        
        return Inertia::render('Tarefas/Create', [
            'categorias' => $categorias,
        ]);
    }

    /**
     * Armazenar uma nova tarefa.
     */
    public function store(TarefaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['utilizador_id'] = Auth::id();
        
        Tarefa::create($data);

        return redirect()->route('tarefas.index')
            ->with('mensagem', 'Tarefa criada com sucesso!');
    }

    /**
     * Mostrar detalhes de uma tarefa específica.
     */
    public function show(Tarefa $tarefa): Response
    {
        $this->authorize('view', $tarefa);
        
        // Carrega a relação de categoria
        $tarefa->load('categoria');
        
        return Inertia::render('Tarefas/Show', [
            'tarefa' => $tarefa,
        ]);
    }

    /**
     * Mostrar formulário para editar uma tarefa.
     */
    public function edit(Tarefa $tarefa): Response
    {
        $this->authorize('update', $tarefa);
        
        // Carrega a relação de categoria
        $tarefa->load('categoria');
        
        // Busca todas as categorias do usuário para o form
        $categorias = Categoria::where('utilizador_id', Auth::id())->get();
        
        return Inertia::render('Tarefas/Edit', [
            'tarefa' => $tarefa,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Atualizar uma tarefa específica.
     */
    public function update(TarefaRequest $request, Tarefa $tarefa): RedirectResponse
    {
        $this->authorize('update', $tarefa);
        
        $tarefa->update($request->validated());

        return redirect()->route('tarefas.index')
            ->with('mensagem', 'Tarefa atualizada com sucesso!');
    }

    /**
     * Marcar tarefa como concluída/não concluída.
     */
    public function toggleConcluida(Tarefa $tarefa): RedirectResponse
    {
        $this->authorize('update', $tarefa);
        
        $tarefa->update([
            'concluida' => !$tarefa->concluida,
            'estado' => $tarefa->concluida ? 'pendente' : 'concluida',
        ]);

        return back()->with('mensagem', 'Estado da tarefa alterado com sucesso!');
    }

    /**
     * Remover uma tarefa específica.
     */
    public function destroy(Tarefa $tarefa): RedirectResponse
    {
        $this->authorize('delete', $tarefa);
        
        $tarefa->delete();

        return redirect()->route('tarefas.index')
            ->with('mensagem', 'Tarefa eliminada com sucesso!');
    }
}
