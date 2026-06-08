<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;

class AiPromptController extends Controller
{
    public function index(?string $group = null)
    {
        if ($group) {
            $prompts = AiPrompt::group($group)->ordered()->get();
            $groups = AiPrompt::groups();
            return view('admin.ai-prompts.index', compact('prompts', 'group', 'groups'));
        }

        $groups = AiPrompt::groups();
        $group = $groups[0] ?? null;
        $prompts = $group ? AiPrompt::group($group)->ordered()->get() : collect();

        return view('admin.ai-prompts.index', compact('prompts', 'group', 'groups'));
    }

    public function create()
    {
        $groups = AiPrompt::groups();
        return view('admin.ai-prompts.form', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'key'         => 'required|string|max:50|unique:ai_prompts,key',
            'group'       => 'nullable|string|max:50',
            'label'       => 'required|string|max:255',
            'value'       => 'required|string',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        $prompt = AiPrompt::create($request->only(['key', 'group', 'label', 'value', 'description', 'sort_order']));

        ActivityLogger::log(
            'Membuat AI Prompt',
            "Prompt \"{$prompt->label}\" berhasil ditambahkan",
            'success',
            $prompt,
        );

        return redirect()->route('admin.ai-prompts.group', $request->group ?: 'general')
            ->with('success', "Prompt \"{$request->label}\" berhasil ditambahkan.");
    }

    public function edit(string $key)
    {
        $prompt = AiPrompt::findOrFail($key);
        $groups = AiPrompt::groups();
        return view('admin.ai-prompts.form', compact('prompt', 'groups'));
    }

    public function update(Request $request, string $key)
    {
        $request->validate([
            'label'       => 'required|string|max:255',
            'value'       => 'required|string',
            'description' => 'nullable|string',
            'group'       => 'nullable|string|max:50',
            'sort_order'  => 'nullable|integer',
        ]);

        $aiPrompt = AiPrompt::findOrFail($key);
        $aiPrompt->update($request->only(['label', 'value', 'description', 'group', 'sort_order']));

        ActivityLogger::log(
            'Memperbarui AI Prompt',
            "Prompt \"{$aiPrompt->label}\" berhasil diperbarui",
            'success',
            $aiPrompt,
        );

        return redirect()->route('admin.ai-prompts.group', $aiPrompt->group ?: 'general')
            ->with('success', "Prompt \"{$aiPrompt->label}\" berhasil disimpan.");
    }

    public function destroy(string $key)
    {
        $aiPrompt = AiPrompt::findOrFail($key);
        $group = $aiPrompt->group ?: 'general';

        ActivityLogger::log(
            'Menghapus AI Prompt',
            "Prompt \"{$aiPrompt->label}\" berhasil dihapus",
            'success',
            $aiPrompt,
        );

        $aiPrompt->delete();

        return redirect()->route('admin.ai-prompts.group', $group)
            ->with('success', "Prompt \"{$aiPrompt->label}\" berhasil dihapus.");
    }

    public function updateValue(Request $request, string $key)
    {
        $request->validate(['value' => 'required|string']);
        $aiPrompt = AiPrompt::findOrFail($key);
        $aiPrompt->update(['value' => $request->value]);

        ActivityLogger::log(
            'Memperbarui Nilai AI Prompt',
            "Nilai prompt \"{$aiPrompt->label}\" berhasil diperbarui",
            'success',
            $aiPrompt,
        );

        return back()->with('success', "Prompt \"{$aiPrompt->label}\" berhasil disimpan.");
    }
}
