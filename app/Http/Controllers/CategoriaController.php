<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    /**
     * Constructor do controller
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Retorna todas as categorias do usuário autenticado.
     */
    public function index()
    {
        $categorias = Auth::user()->categorias()
            ->withCount('tarefas')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias
        ]);
    }

    /**
     * Armazena uma nova categoria criada pelo usuário.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias')->where(fn ($query) => 
                    $query->where('utilizador_id', Auth::id())
                ),
            ],
        ]);

        $categoria = Auth::user()->categorias()->create([
            'nome' => $validated['nome'],
            'cor' => '#6B7280', // Cor cinza padrão
        ]);

        if ($request->wantsJson()) {
            return response()->json($categoria);
        }

        return back()->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, Categoria $categoria)
    {
        if ($categoria->utilizador_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias')
                    ->where(fn ($query) => $query->where('utilizador_id', Auth::id()))
                    ->ignore($categoria->id),
            ],
            'cor' => 'nullable|string|max:7',
        ]);

        $categoria->update($validated);

        return back()->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->utilizador_id !== Auth::id()) {
            abort(403);
        }

        $categoria->delete();

        return back()->with('success', 'Categoria excluída com sucesso!');
    }
}
