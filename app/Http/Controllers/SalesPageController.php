<?php

namespace App\Http\Controllers;

use App\Models\SalesPage;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $validatedInput = $request->validate($this->productInputRules());

        Log::info('AI Generation Request Started', ['product' => $validatedInput['product_name']]);

        try {
            $generatedContent = $this->aiService->generateSalesPage($validatedInput);
        } catch (\Throwable $exception) {
            Log::error('AI Generation Failed', ['error' => $exception->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'AI generation failed: '.$exception->getMessage());
        }

        $salesPage = SalesPage::create([
            'user_id' => Auth::id(),
            'product_name' => $validatedInput['product_name'],
            'original_input' => $validatedInput,
            'generated_content' => $generatedContent,
            'status' => 'published',
        ]);

        return redirect()->route('pages.show', $salesPage)
            ->with('success', 'Sales page generated successfully!');
    }

    /**
     * Generate a sales page while streaming progress to capable browsers.
     */
    public function storeStream(Request $request): StreamedResponse
    {
        $validatedInput = $request->validate($this->productInputRules());
        $userId = Auth::id();

        return response()->stream(function () use ($validatedInput, $userId): void {
            $this->sendSse('status', ['message' => 'Analyzing product']);

            try {
                $this->sendSse('status', ['message' => 'Generating sales copy']);
                $generatedContent = $this->aiService->streamSalesPage(
                    $validatedInput,
                    fn (string $chunk, int $attempt = 1) => $this->sendSse('chunk', [
                        'text' => $chunk,
                        'attempt' => $attempt,
                    ])
                );

                $salesPage = SalesPage::create([
                    'user_id' => $userId,
                    'product_name' => $validatedInput['product_name'],
                    'original_input' => $validatedInput,
                    'generated_content' => $generatedContent,
                    'status' => 'published',
                ]);

                $this->sendSse('redirect', [
                    'url' => route('pages.show', $salesPage),
                ]);
            } catch (\Throwable $exception) {
                Log::error('AI Streaming Generation Failed', ['error' => $exception->getMessage()]);

                $this->sendSse('error', [
                    'message' => 'AI generation failed: '.$exception->getMessage(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
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

            return back()->with('error', 'Regeneration failed: '.$exception->getMessage());
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

        return back()->with('success', 'Style updated to '.ucfirst($style));
    }

    /**
     * Update the typography pair of the sales page.
     */
    public function updateFontPair(Request $request, SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);

        $validated = $request->validate([
            'font_pair' => ['required', 'in:sans,serif'],
        ]);

        $content = $salesPage->generated_content ?? [];
        $content['font_pair'] = $validated['font_pair'];

        $salesPage->update(['generated_content' => $content]);

        return back()->with('success', 'Font pair updated to '.ucfirst($validated['font_pair']));
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

            return back()->with('success', ucfirst($section).' updated!');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Section regeneration failed.');
        }
    }

    /**
     * Update a specific section of the sales page manually.
     */
    public function updateSection(Request $request, SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);
        $section = $request->input('section');
        $value = $request->input('value');
        $content = $salesPage->generated_content ?? [];

        // Handle local file upload for media
        if ($section === 'media_url') {
            $request->validate([
                'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm,ogg', 'max:20480'],
                'value' => ['nullable', 'url', 'max:2048'],
            ]);

            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('sales-media', 'public');
                $content[$section] = asset('storage/'.$path);
            } else {
                $content[$section] = $value;
            }
        } elseif ($section === 'benefits') {
            // Convert newline-separated string to array
            $content[$section] = array_values(array_filter(array_map('trim', explode("\n", $value))));
        } elseif ($section === 'features') {
            // Parse JSON string
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $content[$section] = $decoded;
            } else {
                return back()->with('error', 'Invalid JSON format for Features.');
            }
        } else {
            $content[$section] = $value;
        }

        $salesPage->update(['generated_content' => $content]);

        return back()->with('success', ucfirst(str_replace('_', ' ', $section)).' updated!');
    }

    /**
     * Reorder sections.
     */
    public function reorder(Request $request, SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);
        $section = $request->input('section');
        $direction = $request->input('direction');

        $content = $salesPage->generated_content;
        $order = $content['order'] ?? ['hero', 'media', 'product_vision', 'benefits', 'features', 'proof', 'pricing'];

        $index = array_search($section, $order);
        if ($index !== false) {
            if ($direction === 'up' && $index > 0) {
                $temp = $order[$index - 1];
                $order[$index - 1] = $order[$index];
                $order[$index] = $temp;
            } elseif ($direction === 'down' && $index < count($order) - 1) {
                $temp = $order[$index + 1];
                $order[$index + 1] = $order[$index];
                $order[$index] = $temp;
            }
            $content['order'] = $order;
            $salesPage->update(['generated_content' => $content]);
        }

        return back()->with('success', 'Section reordered!');
    }

    /**
     * Export the sales page as a standalone HTML file.
     */
    public function export(SalesPage $salesPage)
    {
        $this->authorizePageAccess($salesPage);

        $html = view('pages.export', compact('salesPage'))->render();
        $filename = Str::slug($salesPage->product_name).'-sales-page.html';

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

    private function productInputRules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'key_features' => ['required', 'string'],
            'usp' => ['required', 'string'],
            'target_audience' => ['required', 'string'],
            'price' => ['required', 'string', 'max:255'],
        ];
    }

    private function sendSse(string $event, array $payload): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($payload)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
