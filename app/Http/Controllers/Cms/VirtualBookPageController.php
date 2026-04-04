<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Book;
use App\Models\VirtualBookPage;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VirtualBookPageController extends Controller
{
    use \App\Traits\SwapsOrder;
    /**
     * Show create form for a new page within a book.
     */
    public function create(Feature $feature, Book $book)
    {
        $feature->load('parent');
        $maxOrder = $book->pages()->max('order') ?? 0;

        return view('cms.features.virtual_books.pages.create', compact('feature', 'book', 'maxOrder'));
    }

    /**
     * Show edit form for a page.
     */
    public function edit(Feature $feature, Book $book, VirtualBookPage $virtualBookPage)
    {
        $feature->load('parent');

        return view('cms.features.virtual_books.pages.edit', compact('feature', 'book', 'virtualBookPage'));
    }

    /**
     * Store a new page for a book.
     */
    public function store(Request $request, Feature $feature, Book $book, TranslationService $translationService)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_height' => 'nullable|integer|min:10|max:100',
            'image_positions' => 'nullable|array',
            'text_position' => 'nullable|array',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'generated_thumbnail' => 'nullable|string',
            'order' => 'required|integer|min:0',
        ]);

        $validated['feature_id'] = $feature->id;
        $validated['book_id'] = $book->id;
        $validated['image_height'] = $validated['image_height'] ?? 50;

        // Always content page (covers are managed in book settings)
        $validated['is_cover'] = false;
        $validated['is_back_cover'] = false;

        // Handle translation
        $validated['title_en'] = !empty($validated['title'])
            ? $translationService->translate($validated['title'])
            : null;
        $validated['content_en'] = !empty($validated['content'])
            ? $translationService->translate($validated['content'])
            : null;

        // Handle multiple image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('features/virtual-books', 'public');
            }
        }
        $validated['images'] = $images;
        $validated['image_positions'] = $validated['image_positions'] ?? array_fill(0, count($images), ['x' => 0, 'y' => 0]);
        $validated['text_position'] = $validated['text_position'] ?? ['x' => 0, 'y' => 0, 'width' => 45, 'height' => 30];

        // Handle thumbnail
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('features/virtual-books/thumbnails', 'public');
        } elseif ($request->has('generated_thumbnail') && !empty($request->generated_thumbnail)) {
            $generatedThumbnail = $request->generated_thumbnail;
            if (preg_match('/^data:image\/(\w+);base64,/', $generatedThumbnail, $matches)) {
                $imageData = base64_decode(substr($generatedThumbnail, strpos($generatedThumbnail, ',') + 1));
                $extension = $matches[1];
                $filename = 'page_thumb_' . time() . '_' . uniqid() . '.' . $extension;
                $path = 'features/virtual-books/thumbnails/' . $filename;
                Storage::disk('public')->put($path, $imageData);
                $validated['thumbnail'] = $path;
            }
        }
        unset($validated['generated_thumbnail']);

        VirtualBookPage::create($validated);

        return redirect()->route('cms.features.virtual_books.pages.index', [$feature, $book])
            ->with('success', 'Halaman buku berhasil ditambahkan');
    }

    /**
     * Update a page.
     */
    public function update(Request $request, Feature $feature, Book $book, VirtualBookPage $virtualBookPage, TranslationService $translationService)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_height' => 'nullable|integer|min:10|max:100',
            'image_positions' => 'nullable|array',
            'text_position' => 'nullable|array',
            'remove_images' => 'nullable|array',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'generated_thumbnail' => 'nullable|string',
            'remove_thumbnail' => 'boolean',
            'order' => 'required|integer|min:0',
        ]);

        // Set default image height if not provided
        if (!isset($validated['image_height'])) {
            $validated['image_height'] = $virtualBookPage->image_height ?? 50;
        }

        // Always content page (covers are managed in book settings)
        $validated['is_cover'] = false;
        $validated['is_back_cover'] = false;

        // Handle translation
        $validated['title_en'] = !empty($validated['title'])
            ? $translationService->translate($validated['title'])
            : null;
        $validated['content_en'] = !empty($validated['content'])
            ? $translationService->translate($validated['content'])
            : null;

        // Handle multiple image uploads
        $images = $virtualBookPage->page_images ?? [];
        $imagePositions = $virtualBookPage->image_positions ?? [];

        // Handle removing images
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $index => $value) {
                if (isset($images[$index])) {
                    Storage::disk('public')->delete($images[$index]);
                    unset($images[$index]);
                }
            }
            $images = array_values($images);
            $imagePositions = array_values($imagePositions);
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('features/virtual-books', 'public');
                $imagePositions[] = ['x' => 0, 'y' => 0];
            }
        }

        $validated['images'] = $images;
        $validated['image_positions'] = $imagePositions;

        // Handle text position
        if (!isset($validated['text_position'])) {
            $validated['text_position'] = $virtualBookPage->text_position ?? ['x' => 0, 'y' => 0];
        }

        // Handle thumbnail removal
        if ($request->boolean('remove_thumbnail')) {
            if ($virtualBookPage->thumbnail) {
                Storage::disk('public')->delete($virtualBookPage->thumbnail);
            }
            $validated['thumbnail'] = null;
        }

        // Handle thumbnail
        if ($request->hasFile('thumbnail')) {
            if ($virtualBookPage->thumbnail) {
                Storage::disk('public')->delete($virtualBookPage->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('features/virtual-books/thumbnails', 'public');
        } elseif ($request->has('generated_thumbnail') && !empty($request->generated_thumbnail)) {
            $generatedThumbnail = $request->generated_thumbnail;
            if (preg_match('/^data:image\/(\w+);base64,/', $generatedThumbnail, $matches)) {
                if ($virtualBookPage->thumbnail) {
                    Storage::disk('public')->delete($virtualBookPage->thumbnail);
                }
                $imageData = base64_decode(substr($generatedThumbnail, strpos($generatedThumbnail, ',') + 1));
                $extension = $matches[1];
                $filename = 'page_thumb_' . time() . '_' . uniqid() . '.' . $extension;
                $path = 'features/virtual-books/thumbnails/' . $filename;
                Storage::disk('public')->put($path, $imageData);
                $validated['thumbnail'] = $path;
            }
        }
        unset($validated['generated_thumbnail'], $validated['remove_thumbnail']);

        $this->swapOrder($virtualBookPage, (int) $validated['order'], (int) $virtualBookPage->order, ['book_id' => $virtualBookPage->book_id]);
        $virtualBookPage->update($validated);

        return redirect()->route('cms.features.virtual_books.pages.index', [$feature, $book])
            ->with('success', 'Halaman buku berhasil diperbarui');
    }

    /**
     * Delete a page.
     */
    public function destroy(Feature $feature, Book $book, VirtualBookPage $virtualBookPage)
    {
        // Delete images
        $images = $virtualBookPage->page_images ?? [];
        foreach ($images as $image) {
            Storage::disk('public')->delete($image);
        }

        // Delete thumbnail
        if ($virtualBookPage->thumbnail) {
            Storage::disk('public')->delete($virtualBookPage->thumbnail);
        }

        $this->deleteAndShiftOrder($virtualBookPage, ['book_id' => $virtualBookPage->book_id]);

        return redirect()->route('cms.features.virtual_books.pages.index', [$feature, $book])
            ->with('success', 'Halaman buku berhasil dihapus');
    }
}
