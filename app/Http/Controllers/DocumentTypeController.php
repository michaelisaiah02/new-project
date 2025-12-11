<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::all();

        return view('misc.document-type.index', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DocumentType::create($validated);

        return redirect()->route('document-type.index')->with('success', 'Document type added successfully.');
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $documentType->update($validated);

        return redirect()->route('document-type.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function destroy(DocumentType $documentType)
    {
        $documentType->delete();

        return redirect()->route('document-type.index')->with('success', 'Document type has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = DocumentType::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                });
            });

        $documentTypes = $query->orderBy('name')->get();

        return response()->json([
            'html' => view('misc.document-type.partials.table-rows', compact('documentTypes'))->render(),
        ]);
    }
}
