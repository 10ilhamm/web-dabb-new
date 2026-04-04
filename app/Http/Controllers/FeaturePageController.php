<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\FeaturePage;
use App\Models\FeaturePageSection;
use App\Models\VirtualSlideshowPage;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FeaturePageController extends Controller
{
    use \App\Traits\SwapsOrder;
    /**
     * List pages for a feature (CMS).
     */
    public function index(Feature $feature)
    {
        // Redirect to slideshow for slideshow page_type
        if ($feature->page_type === 'slideshow') {
            return redirect()->route('cms.features.slideshow.index', $feature);
        }

        // Redirect to profile for profile page_type
        if ($feature->page_type === 'profile') {
            $parent = Feature::find($feature->parent_id);
            return redirect()->route('cms.features.profile.index', [$parent, $feature]);
        }

        $feature->load(['pages' => function ($q) {
            $q->withCount('sections');
        }, 'parent']);

        return view('cms.features.pages.index', compact('feature'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create(Feature $feature)
    {
        $feature->load('parent');

        // Use different view based on page_type
        if ($feature->page_type === 'slideshow') {
            return view('cms.features.virtual_slideshow.create', compact('feature'));
        }

        return view('cms.features.pages.create', compact('feature'));
    }

    /**
     * Show the form for editing a page.
     */
    public function edit(Feature $feature, $pageId)
    {
        $feature->load('parent');

        // Use VirtualSlideshowPage for slideshow page_type
        if ($feature->page_type === 'slideshow') {
            $page = VirtualSlideshowPage::findOrFail($pageId);
            return view('cms.features.virtual_slideshow.edit', compact('feature', 'page'));
        }

        $page = FeaturePage::findOrFail($pageId);
        return view('cms.features.pages.edit', compact('feature', 'page'));
    }

    /**
     * Store a new page for a feature.
     */
    public function store(Request $request, Feature $feature, TranslationService $translationService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['feature_id'] = $feature->id;
        $validated['title_en'] = $translationService->translate($validated['title']);
        if (! empty($validated['description'])) {
            $validated['description_en'] = $translationService->translate($validated['description']);
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('features/pages/thumbnails', 'public');
        }

        $insertOrder = (int) $validated['order'];
        $scopeConditions = ['feature_id' => $feature->id];
        $extraAttributes = array_filter([
            'title' => $validated['title'],
            'title_en' => $validated['title_en'] ?? null,
            'description' => $validated['description'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
            'thumbnail_path' => $validated['thumbnail_path'] ?? null,
        ], fn($v) => $v !== null);

        // Use VirtualSlideshowPage for slideshow page_type
        if ($feature->page_type === 'slideshow') {
            $this->insertAndShiftOrder(VirtualSlideshowPage::class, $insertOrder, $scopeConditions, $extraAttributes);
            return redirect()->route('cms.features.slideshow.index', $feature)
                ->with('success', __('cms.feature_pages.flash.page_added'));
        }

        $this->insertAndShiftOrder(FeaturePage::class, $insertOrder, $scopeConditions, $extraAttributes);

        return redirect()->route('cms.features.pages.index', $feature)
            ->with('success', __('cms.feature_pages.flash.page_added'));
    }

    /**
     * Show page detail - manage sections (CMS).
     */
    public function show(Feature $feature, FeaturePage $page)
    {
        $page->load('sections');
        $feature->load('parent');

        return view('cms.features.pages.show', compact('feature', 'page'));
    }

    /**
     * Update a page.
     */
    public function update(Request $request, Feature $feature, $pageId, TranslationService $translationService)
    {
        // Use VirtualSlideshowPage for slideshow page_type
        if ($feature->page_type === 'slideshow') {
            $page = VirtualSlideshowPage::findOrFail($pageId);
        } else {
            $page = FeaturePage::findOrFail($pageId);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['title_en'] = $translationService->translate($validated['title']);
        $validated['description_en'] = ! empty($validated['description'])
            ? $translationService->translate($validated['description'])
            : null;

        // Handle thumbnail only for slideshow pages
        if ($feature->page_type === 'slideshow') {
            // Handle remove thumbnail request
            if ($request->input('remove_thumbnail') === '1' && $page->thumbnail_path) {
                Storage::disk('public')->delete($page->thumbnail_path);
                $page->thumbnail_path = null;
            }

            // Handle new thumbnail upload
            if ($request->hasFile('thumbnail')) {
                if ($page->thumbnail_path) {
                    Storage::disk('public')->delete($page->thumbnail_path);
                }
                $validated['thumbnail_path'] = $request->file('thumbnail')->store('features/pages/thumbnails', 'public');
            }
        }

        $this->swapOrder($page, (int) $validated['order'], (int) $page->order, ['feature_id' => $page->feature_id]);
        $page->update($validated);

        if ($feature->page_type === 'slideshow') {
            return redirect()->route('cms.features.slideshow.index', $feature)
                ->with('success', __('cms.feature_pages.flash.page_updated'));
        }

        return redirect()->route('cms.features.pages.index', $feature)
            ->with('success', __('cms.feature_pages.flash.page_updated'));
    }

    /**
     * Delete a page.
     */
    public function destroy(Feature $feature, $pageId)
    {
        // Use VirtualSlideshowPage for slideshow page_type
        if ($feature->page_type === 'slideshow') {
            $page = VirtualSlideshowPage::findOrFail($pageId);
            // Delete thumbnail
            if ($page->thumbnail_path) {
                Storage::disk('public')->delete($page->thumbnail_path);
            }
            $this->deleteAndShiftOrder($page, ['feature_id' => $page->feature_id]);
            return redirect()->route('cms.features.slideshow.index', $feature)
                ->with('success', __('cms.feature_pages.flash.page_deleted'));
        }

        $page = FeaturePage::findOrFail($pageId);
        // Delete section images
        foreach ($page->sections as $section) {
            $this->deleteSectionImages($section);
        }

        $this->deleteAndShiftOrder($page, ['feature_id' => $page->feature_id]);

        return redirect()->route('cms.features.pages.index', $feature)
            ->with('success', __('cms.feature_pages.flash.page_deleted'));
    }

    /**
     * Store a new section for a page.
     */
    public function storeSection(Request $request, Feature $feature, FeaturePage $page, TranslationService $translationService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'images' => 'nullable|array', // unlimited
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_positions' => 'nullable|array',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('features/sections', 'public');
            }
        }

        FeaturePageSection::create([
            'feature_page_id' => $page->id,
            'title' => $validated['title'],
            'title_en' => $translationService->translate($validated['title']),
            'description' => $validated['description'] ?? null,
            'description_en' => ! empty($validated['description'])
                ? $translationService->translate($validated['description'])
                : null,
            'images' => $imagePaths ?: null,
            'image_positions' => $validated['image_positions'] ?? null,
            'order' => $validated['order'],
        ]);

        return redirect()->route('cms.features.pages.show', [$feature, $page])
            ->with('success', __('cms.feature_pages.flash.section_added'));
    }

    /**
     * Update a section.
     */
    public function updateSection(Request $request, Feature $feature, FeaturePage $page, FeaturePageSection $section, TranslationService $translationService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'images' => 'nullable|array', // unlimited
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
            'image_positions' => 'nullable|array',
        ]);

        // Keep existing images that weren't removed
        $existingImages = $validated['existing_images'] ?? [];

        // Delete removed images from storage
        $oldImages = $section->images ?? [];
        foreach ($oldImages as $oldImage) {
            if (! in_array($oldImage, $existingImages)) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        // Add new uploaded images
        $imagePaths = $existingImages;
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('features/sections', 'public');
            }
        }

        $this->swapOrder($section, (int) $validated['order'], (int) $section->order, ['feature_page_id' => $section->feature_page_id]);
        $section->update([
            'title' => $validated['title'],
            'title_en' => $translationService->translate($validated['title']),
            'description' => $validated['description'] ?? null,
            'description_en' => ! empty($validated['description'])
                ? $translationService->translate($validated['description'])
                : null,
            'images' => $imagePaths ?: null,
            'image_positions' => $validated['image_positions'] ?? null,
            'order' => $validated['order'],
        ]);

        return redirect()->route('cms.features.pages.show', [$feature, $page])
            ->with('success', __('cms.feature_pages.flash.section_updated'));
    }

    /**
     * Delete a section.
     */
    public function destroySection(Feature $feature, FeaturePage $page, FeaturePageSection $section)
    {
        $this->deleteSectionImages($section);
        $this->deleteAndShiftOrder($section, ['feature_page_id' => $section->feature_page_id]);

        return redirect()->route('cms.features.pages.show', [$feature, $page])
            ->with('success', __('cms.feature_pages.flash.section_deleted'));
    }

    /**
     * Public: show feature page with sections (paginated).
     */
    public function publicShow(Feature $feature, ?int $pageNum = null, bool $requiresLoginModal = false, ?string $loginModalPreview = null, ?string $loginModalRoomName = null)
    {
        $feature->load('parent');
        $pages = $feature->pages()->withCount('sections')->orderBy('order')->get();

        if ($pages->isEmpty()) {
            abort(404);
        }

        $pageNum = $pageNum ?? 1;
        $currentPage = $pages->values()->get($pageNum - 1);

        if (! $currentPage) {
            abort(404);
        }

        $currentPage->load('sections');

        $virtual3dRooms = $feature->virtual3dRooms()->with('media')->get();

        return view('pages.virtual_3d_tour', [
            'feature'             => $feature,
            'pages'               => $pages,
            'currentPage'         => $currentPage,
            'currentPageNum'      => $pageNum,
            'totalPages'          => $pages->count(),
            'requiresLoginModal'  => $requiresLoginModal,
            'loginModalPreview'   => $loginModalPreview,
            'loginModalRoomName'  => $loginModalRoomName,
            'virtual3dRooms'      => $virtual3dRooms,
        ]);
    }

    /**
     * @internal wrapped call from publicShowByPath
     */
    private function publicShowWithModal(Feature $feature, int $pageNum, bool $requiresLoginModal)
    {
        return $this->publicShow($feature, $pageNum, $requiresLoginModal);
    }

    /**
     * Public: show feature page by path (e.g., /pameran/tetap).
     */
    public function publicShowByPath(Request $request)
    {
        $path = '/'.$request->path;
        $feature = Feature::where('path', $path)->firstOrFail();
        $feature->loadCount('pages');

        // Pages under /pameran/virtual or /pameran-arsip-virtual require authentication — show login modal if guest
        $requiresLoginModal = !Auth::check() && (
            str_contains($path, '/pameran/virtual') ||
            str_contains($path, '/pameran-virtual') ||
            str_contains($path, '/pameran-arsip-virtual')
        );

        // Resolve preview image for the login modal right panel
        $loginModalPreview = null;
        $loginModalRoomName = null;

        // Profile page type — load all profile pages from profiles table with their sections
        if ($feature->page_type === 'profile') {
            $allProfilePages = $feature->profiles()->with('sections')->orderBy('order')->get();
            $locale = app()->getLocale();

            // Handle pagination with ?page=N parameter
            $totalPages = $allProfilePages->count();
            $pageNum = $request->input('page', 1);
            $currentPageIndex = max(0, min((int)$pageNum - 1, $totalPages - 1));
            $currentPage = $allProfilePages->values()->get($currentPageIndex);

            // Calculate isEven for alternating section backgrounds
            $isEven = ($currentPageIndex + 1) % 2 === 0;

            return view('pages.profile', compact(
                'feature', 'allProfilePages', 'locale', 'totalPages', 'currentPage', 'currentPageIndex', 'isEven'
            ));
        }

        // Virtual Slideshow — show SimHive-style interactive page with page selection
        if ($feature->page_type === 'slideshow') {
            $pages = $feature->slideshowPages()->with('slideshowSlides')->orderBy('order')->get();
            $selectedPage = null;
            $slides = collect();
            $locale = app()->getLocale();

            // Check if specific page is requested
            $pageNum = $request->input('page');
            if ($pageNum) {
                $selectedPage = $pages->firstWhere('order', $pageNum);
                if ($selectedPage) {
                    $slides = $selectedPage->slideshowSlides->sortBy('order')->values();
                }
            }

            // Use separate views for landing vs content
            if ($selectedPage) {
                return view('pages.virtual_slideshow_content', compact(
                    'feature', 'pages', 'selectedPage', 'slides', 'locale'
                ));
            }

            return view('pages.virtual_slideshow_landing', compact(
                'feature', 'pages'
            ));
        }

        // Handle beranda page type - load content from language files
        // Check if there's a dedicated home_{id}.php file for this feature (except for original beranda)
        $homeFilePath = resource_path("lang/id/home_{$feature->id}.php");
        if ($feature->id != 1 && File::exists($homeFilePath)) {
            $locale = app()->getLocale();
            $idContent = $this->loadBerandaContent($feature->id, 'id');
            $enContent = $this->loadBerandaContent($feature->id, 'en');
            $content = $locale === 'id' ? $enContent : $idContent;

            return view('welcome', compact('feature', 'content'));
        }
        if ($requiresLoginModal) {
            // Try direct virtual rooms on this feature
            $firstRoom = $feature->virtual3dRooms()->first() ?? $feature->virtualRooms()->first();

            // If none, check sub-features' virtual rooms
            if (!$firstRoom && method_exists($feature, 'subfeatures')) {
                foreach ($feature->subfeatures as $sub) {
                    $firstRoom = $sub->virtual3dRooms()->first() ?? $sub->virtualRooms()->first();
                    if ($firstRoom) break;
                }
            }
            if ($firstRoom) {
                $imgPath = $firstRoom->thumbnail_path ?? $firstRoom->image_360_path ?? null;
                $loginModalPreview = $imgPath ? asset('storage/'.$imgPath) : null;
                $loginModalRoomName = $firstRoom->name;
            }
        }

        // Virtual 3D Rooms feature — show interactive 4-walls 3D room
        if (method_exists($feature, 'virtual3dRooms')) {
            $virtual3dRooms = $feature->virtual3dRooms()->with('media')->get();

            // Check subfeatures if the parent feature has no virtual 3d rooms
            if ($virtual3dRooms->isEmpty() && method_exists($feature, 'subfeatures')) {
                foreach ($feature->subfeatures as $sub) {
                    if (method_exists($sub, 'virtual3dRooms')) {
                        $virtual3dRooms = $virtual3dRooms->merge($sub->virtual3dRooms()->with('media')->get());
                    }
                }
            }

            if ($virtual3dRooms->isNotEmpty()) {
                // Add first room thumbnail for modal if needed
                if ($requiresLoginModal) {
                    $first3dRoom = $virtual3dRooms->first();
                    $loginModalPreview = $first3dRoom->thumbnail_path ? asset('storage/'.$first3dRoom->thumbnail_path) : null;
                    $loginModalRoomName = $first3dRoom->name;
                }

                return view('pages.virtual_3d_tour', compact(
                    'feature', 'virtual3dRooms', 'requiresLoginModal',
                    'loginModalPreview', 'loginModalRoomName'
                ));
            }
        }

        // Virtual rooms feature (360) — show dedicated 360° tour page
        if (method_exists($feature, 'virtualRooms')) {
            $virtualRooms = $feature->virtualRooms()->withCount('hotspots')->with('hotspots')->get();
            if ($virtualRooms->isNotEmpty()) {
                if ($requiresLoginModal) {
                    $firstRoom = $virtualRooms->first();
                    $imgPath = $firstRoom->thumbnail_path ?? $firstRoom->image_360_path ?? null;
                    $loginModalPreview = $imgPath ? asset('storage/'.$imgPath) : null;
                    $loginModalRoomName = $firstRoom->name;
                }

                return view('pages.virtual_tour', compact(
                    'feature', 'virtualRooms', 'requiresLoginModal',
                    'loginModalPreview', 'loginModalRoomName'
                ));
            }
        }

        // Virtual Book Pages - show flip book
        if ($feature->is_virtual_book || $feature->books()->exists()) {
            $books = $feature->books()->with('pages')->orderBy('order')->get();
            $readBookId = request('read');

            if ($readBookId) {
                $book = $books->firstWhere('id', $readBookId);
                if ($book) {
                    return view('pages.virtual_book_viewer', compact(
                        'feature', 'book', 'requiresLoginModal',
                        'loginModalPreview', 'loginModalRoomName'
                    ));
                }
            }

            return view('pages.virtual_book_grid', compact(
                'feature', 'books', 'requiresLoginModal',
                'loginModalPreview', 'loginModalRoomName'
            ));
        }

        if ($feature->pages_count > 0) {
            return $this->publicShow($feature, 1, $requiresLoginModal, $loginModalPreview, $loginModalRoomName);
        }

        $virtual3dRooms = $feature->virtual3dRooms()->with('media')->get();
        if ($virtual3dRooms->isEmpty() && method_exists($feature, 'subfeatures')) {
            foreach ($feature->subfeatures as $sub) {
                if (method_exists($sub, 'virtual3dRooms')) {
                    $virtual3dRooms = $virtual3dRooms->merge($sub->virtual3dRooms()->with('media')->get());
                }
            }
        }

        return view('pages.virtual_3d_tour', compact(
            'feature', 'requiresLoginModal', 'loginModalPreview', 'loginModalRoomName', 'virtual3dRooms'
        ));
    }

    /**
     * Load beranda content from language files.
     */
    private function loadBerandaContent(int $featureId, string $locale): array
    {
        // For feature ID 1, use original home.php
        if ($featureId == 1) {
            $path = resource_path("lang/{$locale}/home.php");
        } else {
            $path = resource_path("lang/{$locale}/home_{$featureId}.php");
        }

        if (File::exists($path)) {
            return include $path;
        }

        return [];
    }

    private function deleteSectionImages(FeaturePageSection $section): void
    {
        if ($section->images) {
            foreach ($section->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
    }
}
