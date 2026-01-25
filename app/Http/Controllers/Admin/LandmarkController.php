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

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $landmarks = $query->orderBy('category')->orderBy('name')->paginate(10);

        return view('admin.landmarks.index', compact('landmarks'));
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
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(Landmark::CATEGORIES)),
            'is_featured' => 'boolean',
            'icon_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        // Handle icon image upload
        if ($request->hasFile('icon_image')) {
            $validated['icon_image'] = $request->file('icon_image')->store('landmarks/icons', 'public');
        }

        // Handle gallery images upload
        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $galleryPaths[] = $image->store('landmarks/gallery', 'public');
            }
        }
        $validated['gallery_images'] = $galleryPaths;

        $validated['is_featured'] = $request->has('is_featured');

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
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(Landmark::CATEGORIES)),
            'is_featured' => 'boolean',
            'icon_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_icon' => 'boolean',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_gallery' => 'nullable|array',
            'remove_gallery.*' => 'integer'
        ]);

        // Handle removing icon image
        if ($request->has('remove_icon') && $request->remove_icon == '1') {
            if ($landmark->icon_image) {
                Storage::disk('public')->delete($landmark->icon_image);
            }
            $validated['icon_image'] = null;
        }
        
        // Handle uploading new icon image
        if ($request->hasFile('icon_image')) {
            // Delete old icon if exists and not already removed
            if ($landmark->icon_image && !$request->has('remove_icon')) {
                Storage::disk('public')->delete($landmark->icon_image);
            }
            $validated['icon_image'] = $request->file('icon_image')->store('landmarks/icons', 'public');
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
                    Storage::disk('public')->delete($existingGallery[$index]);
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
        }
        
        $validated['gallery_images'] = $existingGallery;

        $validated['is_featured'] = $request->has('is_featured');

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
}
