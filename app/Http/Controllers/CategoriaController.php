<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
    public function index(): JsonResponse
    {
        $categorias = Categoria::where('utilizador_id', Auth::id())->get();
        
        return response()->json($categorias);
    }

    /**
     * Armazena uma nova categoria criada pelo usuário.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias')->where(function ($query) {
                    return $query->where('utilizador_id', Auth::id());
                }),
            ],
            'cor' => 'nullable|string|max:30',
        ]);

        $categoria = Categoria::create([
            'nome' => $validated['nome'],
            'cor' => $validated['cor'] ?? '#6366F1', // Cor padrão (indigo)
            'utilizador_id' => Auth::id(),
        ]);

        return response()->json($categoria, 201);
    }
}
