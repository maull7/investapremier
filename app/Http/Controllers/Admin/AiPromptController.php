<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;

class AiPromptController extends Controller
{
    public function index()
    {
        $prompts = AiPrompt::orderBy('key')->get();
        return view('admin.ai-prompts.index', compact('prompts'));
    }

    public function update(Request $request, string $key)
    {
        $request->validate(['value' => 'required|string']);
        $aiPrompt = \App\Models\AiPrompt::findOrFail($key);
        $aiPrompt->update(['value' => $request->value]);
        return back()->with('success', "Prompt \"{$aiPrompt->label}\" berhasil disimpan.");
    }
}
