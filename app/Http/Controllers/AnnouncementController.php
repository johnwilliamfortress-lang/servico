<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $sort = (string) $request->query('sort', 'recentes');

        $announcements = Announcement::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('offer_info', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($category, Announcement::CATEGORIES, true), fn ($query) => $query->where('category', $category))
            ->when($sort === 'titulo', fn ($query) => $query->orderBy('title'), fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        return view('announcements.index', [
            'announcements' => $announcements,
            'categories' => Announcement::CATEGORIES,
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
        ]);
    }

    public function create(): View
    {
        return view('announcements.form', [
            'announcement' => new Announcement,
            'categories' => Announcement::CATEGORIES,
            'editing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        unset($validated['image'], $validated['remove_image']);
        $validated['image_path'] = $this->storeImage($request);
        $code = Str::random(64);
        $validated['management_token'] = hash('sha256', $code);

        $announcement = Announcement::create($validated);

        return redirect()->route('announcements.index')
            ->with('status', 'Anúncio publicado com sucesso!')
            ->with('management_url', route('announcements.edit', [$announcement, $code]));
    }

    public function edit(Announcement $announcement, string $code): View
    {
        $this->authorizeManagement($announcement, $code);

        return view('announcements.form', [
            'announcement' => $announcement,
            'categories' => Announcement::CATEGORIES,
            'editing' => true,
            'managementCode' => $code,
        ]);
    }

    public function update(Request $request, Announcement $announcement, string $code): RedirectResponse
    {
        $this->authorizeManagement($announcement, $code);

        $validated = $request->validate($this->rules());
        unset($validated['image'], $validated['remove_image']);
        $previousImage = $announcement->image_path;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->storeImage($request);
        } elseif ($request->boolean('remove_image')) {
            $validated['image_path'] = null;
        }

        $announcement->update($validated);

        if ($previousImage && $announcement->image_path !== $previousImage && ! Storage::disk('public')->delete($previousImage)) {
            throw new RuntimeException('Não foi possível remover a imagem antiga do anúncio.');
        }

        return redirect()->route('announcements.index')
            ->with('status', 'Anúncio atualizado com sucesso!')
            ->with('management_url', route('announcements.edit', [$announcement, $code]));
    }

    public function destroy(Announcement $announcement, string $code): RedirectResponse
    {
        $this->authorizeManagement($announcement, $code);

        $imagePath = $announcement->image_path;
        $announcement->delete();

        if ($imagePath && ! Storage::disk('public')->delete($imagePath)) {
            throw new RuntimeException('Não foi possível remover a imagem do anúncio.');
        }

        return redirect()->route('announcements.index')->with('status', 'Anúncio excluído.');
    }

    private function authorizeManagement(Announcement $announcement, string $code): void
    {
        abort_unless(hash_equals($announcement->management_token, hash('sha256', $code)), 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', Rule::in(Announcement::CATEGORIES)],
            'description' => ['required', 'string', 'max:5000'],
            'offer_info' => ['required', 'string', 'max:500'],
            'contact' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->storePublicly('announcements', 'public');

        if (! $path) {
            throw new RuntimeException('Não foi possível salvar a imagem do anúncio.');
        }

        return $path;
    }
}
