<?php

namespace App\Http\Controllers;

use App\Models\OpenAIChatType;
use App\Models\OpenAIChat;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class OpenAIChatTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:OPENAI - CHAT']);
    }

    public function index(): View
    {
        $types = OpenAIChatType::orderBy('name')->get();
        // Contagem de chats por tipo (opcional)
        $counts = OpenAIChat::selectRaw('type_id, COUNT(*) as total')
            ->whereNotNull('type_id')
            ->groupBy('type_id')
            ->pluck('total', 'type_id');

        return view('openai.types.index', compact('types', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = trim(preg_replace('/\s+/', ' ', (string) $validated['name']));
        $slug = Str::slug($name);

        // Evita duplicidade por slug
        if (OpenAIChatType::where('slug', $slug)->exists()) {
            return back()->with('error', 'Já existe um tipo com este nome.');
        }

        OpenAIChatType::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        return back()->with('success', 'Tipo criado com sucesso.');
    }

    public function update(OpenAIChatType $type, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = trim(preg_replace('/\s+/', ' ', (string) $validated['name']));
        $slug = Str::slug($name);

        if (OpenAIChatType::where('slug', $slug)->where('id', '<>', $type->id)->exists()) {
            return back()->with('error', 'Já existe outro tipo com este nome.');
        }

        $type->update(['name' => $name, 'slug' => $slug]);

        return back()->with('success', 'Tipo atualizado.');
    }

    public function destroy(OpenAIChatType $type): RedirectResponse
    {
        // Impede excluir se houver conversas vinculadas
        $inUse = OpenAIChat::where('type_id', $type->id)->exists();
        if ($inUse) {
            return back()->with('error', 'Não é possível excluir: existem conversas vinculadas a este tipo.');
        }

        $type->delete();
        return back()->with('success', 'Tipo excluído.');
    }
}
