<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil semua berita, diurutkan dari yang terbaru, dan memuat relasi dengan petugas
        $berita = Berita::with('petugas')->latest()->get();
        return response()->json($berita);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:100|unique:beritas,judul',
            'isi' => 'required|string',
            'kategori' => 'required|in:Berita,Artikel,Event',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk gambar
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            // Simpan gambar dan dapatkan path-nya
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails/berita', 'public');
        }

        $berita = Berita::create([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'kategori' => $validated['kategori'],
            'thumbnail' => $thumbnailPath,
            'tanggal' => now(), // Menggunakan tanggal saat ini
            'petugas_id' => Auth::id(), // Mengambil ID petugas yang sedang login
        ]);

        return response()->json([
            'message' => 'Berita berhasil dibuat.',
            'data' => $berita
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $berita = Berita::with('petugas')->find($id);

        if (!$berita) {
            return response()->json(['message' => 'Berita tidak ditemukan'], 404);
        }

        return response()->json($berita);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $berita = Berita::find($id);

        if (!$berita) {
            return response()->json(['message' => 'Berita tidak ditemukan'], 404);
        }

        // Otorisasi: Hanya petugas yang membuat yang bisa mengedit
        if ($berita->petugas_id !== Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit berita ini'], 403);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:100|unique:beritas,judul,' . $id . ',berita_id',
            'isi' => 'required|string',
            'kategori' => 'required|in:Berita,Artikel,Event',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $thumbnailPath = $berita->thumbnail;
        if ($request->hasFile('thumbnail')) {
            // Hapus thumbnail lama jika ada
            if ($berita->thumbnail) {
                Storage::disk('public')->delete($berita->thumbnail);
            }
            // Simpan thumbnail baru
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails/berita', 'public');
        }

        $berita->update([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'kategori' => $validated['kategori'],
            'thumbnail' => $thumbnailPath,
        ]);

        return response()->json([
            'message' => 'Berita berhasil diperbarui.',
            'data' => $berita
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $berita = Berita::find($id);

        if (!$berita) {
            return response()->json(['message' => 'Berita tidak ditemukan'], 404);
        }

        // Otorisasi: Hanya petugas yang membuat yang bisa menghapus
        if ($berita->petugas_id !== Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus berita ini'], 403);
        }

        // Hapus thumbnail dari storage jika ada
        if ($berita->thumbnail) {
            Storage::disk('public')->delete($berita->thumbnail);
        }

        $berita->delete();

        return response()->json(['message' => 'Berita berhasil dihapus']);
    }
}
