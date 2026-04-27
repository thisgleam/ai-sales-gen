<?php

namespace App\Http\Controllers;

use App\Models\SalesPage;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SalesPageController extends Controller
{
    public function __construct(private readonly AIService $aiService) {}

    /**
     * List all sales pages belonging to the authenticated user.
     */
    public function index()
    {
        $salesPages = Auth::user()
            ->salesPages()
            ->latest()
            ->get();

        return view('dashboard', compact('salesPages'));
    }

    /**
     * Show the product input form.
     */
    public function create()
    {
        return view('pages.create');
    }

    /**
     * Validate input, call AI, save the generated page, and redirect to preview.
     */
    public function store(Request $request)
    {
        $validatedInput = $request->validate([
            'product_name'    => ['required', 'string', 'max:255'],
            'description'     => ['required', 'string'],
            'key_features'    => ['required', 'string'],
            'usp'             => ['required', 'string'],
            'target_audience' => ['required', 'string'],
            'price'           => ['required', 'string', 'max:255'],
        ]);

        Log::info('AI Generation Request Started', ['product' => $validatedInput['product_name']]);

        try {
            $generatedContent = $this->aiService->generateSalesPage($validatedInput);
        } catch (\Throwable $exception) {
            Log::error('AI Generation Failed', ['error' => $exception->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'AI generation failed: ' . $exception->getMessage());
        }

        $salesPage = SalesPage::create([
            'user_id'           => Auth::id(),
            'product_name'      => $validatedInput['product_name'],
            'original_input'    => $validatedInput,
            'generated_content' => $generatedContent,
            'status'            => 'published',
        ]);

        return redirect()->route('pages.show', $salesPage)
            ->with('success', 'Sales page generated successfully!');
    }

    /**
     * Display the live preview of a generated sales page.
     */
    public function show(SalesPage $salesPage)
    {
        return view('pages.show', compact('salesPage'));
    }

    /**
     * Re-generate a sales page using its original input data.
     */
    public function regenerate(SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);

        try {
            $generatedContent = $this->aiService->generateSalesPage($salesPage->original_input);
        } catch (\Throwable $exception) {
            Log::error('AI Regeneration Failed', ['error' => $exception->getMessage()]);
            return back()->with('error', 'Regeneration failed: ' . $exception->getMessage());
        }

        $salesPage->update([
            'generated_content' => $generatedContent,
        ]);

        return redirect()->route('pages.show', $salesPage)
            ->with('success', 'Sales page regenerated!');
    }

    /**
     * Delete a sales page belonging to the authenticated user.
     */
    public function destroy(SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);

        $salesPage->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Page deleted.');
    }

    /**
     * Update the design style/template of the sales page.
     */
    public function updateStyle(Request $request, SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);
        $style = $request->input('style', 'modern');

        $content = $salesPage->generated_content;
        $content['style'] = $style;
        
        $salesPage->update(['generated_content' => $content]);
        
        return back()->with('success', 'Style updated to ' . ucfirst($style));
    }

    /**
     * Regenerate a specific section of the sales page.
     */
    public function regenerateSection(Request $request, SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);
        $section = $request->input('section');

        try {
            $newValue = $this->aiService->regenerateSection($salesPage->original_input, $section);
            $content = $salesPage->generated_content;
            $content[$section] = $newValue;
            
            $salesPage->update(['generated_content' => $content]);
            
            return back()->with('success', ucfirst($section) . ' updated!');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Section regeneration failed.');
        }
    }

    /**
     * Export the sales page as a standalone HTML file.
     */
    public function export(SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);

        $html = view('pages.export', compact('salesPage'))->render();
        $filename = \Illuminate\Support\Str::slug($salesPage->product_name) . '-sales-page.html';

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * Ensure the authenticated user owns the given sales page.
     */
    private function authorizePageAccess(SalesPage $salesPage): void
    {
        abort_if($salesPage->user_id !== Auth::id(), 403);
    }
}
