<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Landmark;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandmarkController extends Controller
{
    /**
     * Display a listing of landmarks
     */
    public function index(Request $request)
    {
        $query = Landmark::query();

        // Combined filter (category + sort)
        $filter = $request->get('filter', 'all');

        // Category filters
        $categoryFilters = ['city_center', 'mall', 'school', 'hospital', 'transport', 'other', 'featured'];
        if (in_array($filter, $categoryFilters)) {
            if ($filter === 'featured') {
                $query->where('is_featured', true);
            } else {
                $query->where('category', $filter);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting — featured first, then alphabetical by default
        switch ($filter) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderByDesc('is_featured')->orderBy('name', 'asc');
                break;
        }

        $landmarks = $query->paginate(10);

        // Get statistics
        $stats = [
            'total' => Landmark::count(),
            'city_center' => Landmark::where('category', 'city_center')->count(),
            'malls' => Landmark::where('category', 'mall')->count(),
            'schools' => Landmark::where('category', 'school')->count(),
            'hospitals' => Landmark::where('category', 'hospital')->count(),
        ];

        return view('admin.landmarks.index', compact('landmarks', 'stats'));
    }

    /**
     * Show the form for creating a new landmark
     */
    public function create()
    {
        return view('admin.landmarks.create');
    }

    /**
     * Store a newly created landmark
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'required|string',
            'category' => 'required|string|in:' . implode(',', array_keys(Landmark::CATEGORIES)),
            'is_featured' => 'boolean',
            'icon_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:min_width=50,min_height=50,max_width=2048,max_height=2048',
            'icon_image_url' => 'nullable|url|max:500',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120|dimensions:min_width=100,min_height=100,max_width=4096,max_height=4096',
            'gallery_images_urls' => 'nullable|string'
        ]);

        // Validate that at least one icon source is provided
        if (!$request->hasFile('icon_image') && !$request->filled('icon_image_url')) {
            return back()->withErrors(['icon_image' => 'Please provide an icon image file or URL'])->withInput();
        }

        // Handle icon image upload or URL
        if ($request->hasFile('icon_image')) {
            $validated['icon_image'] = $request->file('icon_image')->store('landmarks/icons', 'public');
        } elseif ($request->filled('icon_image_url')) {
            $validated['icon_image'] = $request->icon_image_url;
        }

        // Handle gallery images upload or URLs
        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $galleryPaths[] = $image->store('landmarks/gallery', 'public');
            }
        } elseif ($request->filled('gallery_images_urls')) {
            $urls = array_filter(array_map('trim', explode("\n", $request->gallery_images_urls)));
            foreach ($urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $galleryPaths[] = $url;
                }
            }
        }
        $validated['gallery_images'] = $galleryPaths;

        $validated['is_featured'] = $request->has('is_featured');
        
        // Remove URL fields that shouldn't be stored
        unset($validated['icon_image_url'], $validated['gallery_images_urls']);

        $landmark = Landmark::create($validated);

        // Log activity
        ActivityLog::log(
            'created',
            'Landmark',
            $landmark->id,
            $landmark->name,
            "New landmark '{$landmark->name}' was added in {$landmark->category} category"
        );

        return redirect()->route('admin.landmarks.index')
            ->with('success', 'Landmark "' . $validated['name'] . '" created successfully!');
    }

    /**
     * Show the form for editing a landmark
     */
    public function edit(Landmark $landmark)
    {
        return view('admin.landmarks.edit', compact('landmark'));
    }

    /**
     * Update the specified landmark
     */
    public function update(Request $request, Landmark $landmark)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'required|string',
            'category' => 'required|string|in:' . implode(',', array_keys(Landmark::CATEGORIES)),
            'is_featured' => 'boolean',
            'icon_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:min_width=50,min_height=50,max_width=2048,max_height=2048',
            'icon_image_url' => 'nullable|url|max:500',
            'remove_icon' => 'boolean',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120|dimensions:min_width=100,min_height=100,max_width=4096,max_height=4096',
            'gallery_images_urls' => 'nullable|string',
            'remove_gallery' => 'nullable|array',
            'remove_gallery.*' => 'integer'
        ]);

        // Handle removing icon image
        if ($request->has('remove_icon') && $request->remove_icon == '1') {
            if ($landmark->icon_image && !filter_var($landmark->icon_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($landmark->icon_image);
            }
            $validated['icon_image'] = null;
        }
        
        // Handle uploading new icon image or URL
        if ($request->hasFile('icon_image')) {
            // Delete old icon if exists and not already removed
            if ($landmark->icon_image && !$request->has('remove_icon') && !filter_var($landmark->icon_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($landmark->icon_image);
            }
            $validated['icon_image'] = $request->file('icon_image')->store('landmarks/icons', 'public');
        } elseif ($request->filled('icon_image_url')) {
            // Use URL if provided
            if ($landmark->icon_image && !filter_var($landmark->icon_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($landmark->icon_image);
            }
            $validated['icon_image'] = $request->icon_image_url;
        } elseif (!$request->has('remove_icon')) {
            // Keep existing icon if not removing and not uploading new one
            unset($validated['icon_image']);
        }

        // Handle gallery images
        $existingGallery = $landmark->gallery_images ?? [];
        
        // Remove selected gallery images
        if ($request->has('remove_gallery')) {
            foreach ($request->remove_gallery as $index) {
                if (isset($existingGallery[$index])) {
                    if (!filter_var($existingGallery[$index], FILTER_VALIDATE_URL)) {
                        Storage::disk('public')->delete($existingGallery[$index]);
                    }
                    unset($existingGallery[$index]);
                }
            }
            $existingGallery = array_values($existingGallery); // Re-index array
        }
        
        // Add new gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $existingGallery[] = $image->store('landmarks/gallery', 'public');
            }
        } elseif ($request->filled('gallery_images_urls')) {
            $urls = array_filter(array_map('trim', explode("\n", $request->gallery_images_urls)));
            foreach ($urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $existingGallery[] = $url;
                }
            }
        }
        
        $validated['gallery_images'] = $existingGallery;

        $validated['is_featured'] = $request->has('is_featured');
        
        // Remove URL fields that shouldn't be stored
        unset($validated['icon_image_url'], $validated['gallery_images_urls'], $validated['remove_icon'], $validated['remove_gallery']);
        $landmark->update($validated);

        // Log activity
        ActivityLog::log(
            'updated',
            'Landmark',
            $landmark->id,
            $landmark->name,
            "Landmark '{$landmark->name}' was updated"
        );

        return redirect()->route('admin.landmarks.index')
            ->with('success', 'Landmark "' . $landmark->name . '" updated successfully!');
    }

    /**
     * Remove the specified landmark
     */
    public function destroy(Landmark $landmark)
    {
        $name = $landmark->name;

        // Delete icon image
        if ($landmark->icon_image) {
            Storage::disk('public')->delete($landmark->icon_image);
        }

        // Delete gallery images
        if ($landmark->gallery_images) {
            foreach ($landmark->gallery_images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $landmark->delete();

        // Log activity
        ActivityLog::log(
            'deleted',
            'Landmark',
            null,
            $name,
            "Landmark '{$name}' was removed from {$landmark->category} category"
        );

        return redirect()->route('admin.landmarks.index')
            ->with('success', 'Landmark "' . $name . '" deleted successfully!');
    }

    /**
     * Batch delete landmarks
     */
    public function batchDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:landmarks,id'
        ]);

        $ids = $validated['ids'];
        $landmarks = Landmark::whereIn('id', $ids)->get();
        
        if ($landmarks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No landmarks found to delete'
            ], 404);
        }

        $deletedCount = 0;
        $deletedNames = [];

        foreach ($landmarks as $landmark) {
            $deletedNames[] = $landmark->name;

            // Delete icon image
            if ($landmark->icon_image && Storage::disk('public')->exists($landmark->icon_image)) {
                Storage::disk('public')->delete($landmark->icon_image);
            }

            // Delete gallery images
            if (!empty($landmark->gallery_images)) {
                foreach ($landmark->gallery_images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            // Delete the landmark
            $landmark->delete();
            $deletedCount++;

            // Log activity
            ActivityLog::log(
                'deleted',
                'Landmark',
                null,
                $landmark->name,
                "Landmark '{$landmark->name}' was batch deleted from {$landmark->category} category"
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} landmark(s): " . implode(', ', $deletedNames),
            'deleted_count' => $deletedCount
        ]);
    }
}
