<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\M_Katalog;
use App\Models\M_Lelang;

class C_Katalog extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', 'active'); // Default 'all' jika tidak ada kategori.
        $sortBy = $request->get('sort_by', 'date_added'); // Default 'date_added' jika tidak ada sort.

        $katalogs = M_Katalog::query();

        // Filter berdasarkan kategori
        if ($kategori === 'active') {
            $katalogs = $katalogs->whereNull('deleted_at'); // Produk tersedia.
        } elseif ($kategori === 'deleted') {
            $katalogs = $katalogs->onlyTrashed(); // Produk dihapus.
        } elseif ($kategori === 'all') {
            $katalogs = $katalogs->withTrashed(); // semua.
        }

        // Logika sort berdasarkan pilihan
        if ($sortBy === 'date_added') {
            $katalogs = $katalogs->orderBy('created_at', 'asc'); // Mengurutkan berdasarkan tanggal ditambahkan
        } elseif ($sortBy === 'alphabetical') {
            $katalogs = $katalogs->orderBy('nama_produk', 'asc'); // Mengurutkan berdasarkan nama produk secara abjad
        }

        // Pagination dengan query string diteruskan.
        $katalogs = $katalogs->paginate(12)->appends($request->query());

        return view('katalog.index', compact('katalogs', 'kategori', 'sortBy'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        return view('katalog.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:128',
            'deskripsi_produk' => 'nullable|string',
            'harga_perkilo' => 'required|integer',
            'foto_produk' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi!',
            'harga_perkilo.required' => 'Harga perkilo wajib diisi!',
            'foto_produk.required' => 'Foto produk wajib diisi!',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            // Kirim error kembalikan ke halaman sebelumnya
            return redirect()->back()
                ->withErrors($validator) // Kirim error validasi ke error bag
                ->withInput(); // Kirim input sebelumnya agar tetap terisi
        }

        try {
            // Simpan file foto ke dalam folder storage
            $fotoPath = $request->file('foto_produk')->store('katalogs', 'public');

            // Simpan data produk ke database
            M_Katalog::create([
                'nama_produk' => $request->nama_produk,
                'deskripsi_produk' => $request->deskripsi_produk,
                'harga_perkilo' => $request->harga_perkilo,
                'foto_produk' => $fotoPath,
            ]);

            // Kirim pesan sukses ke session
            $request->session()->flash('success', 'Produk berhasil ditambahkan!');
            return redirect()->route('katalog.index');

        } catch (\Exception $e) {
            // Tangkap error runtime dan kirim pesan error ke session
            $request->session()->flash('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
            return redirect()->back()
                ->withInput(); // Tetap isi form dengan data sebelumnya
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Cari data katalog berdasarkan ID
        $katalog = M_Katalog::withTrashed()->findOrFail($id); // Akan melempar 404 jika ID tidak ditemukan

        // Kirim data ke view 'katalog.show'
        return view('katalog.show', ['katalog' => $katalog]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $katalog = M_Katalog::findOrFail($id);
        return view('katalog.add', compact('katalog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga_perkilo' => 'required|integer',
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'selected_image' => 'nullable|string',
        ]);

        $katalog = M_Katalog::findOrFail($id);
        // dd($request->foto_produk, $request->selected_image);

        // makai gambar lama
        if ($request->input('selected_image') !== 'new') {

        }
        else {
            $basenameLama = basename($katalog->foto_produk);
            // Hapus foto lama jika ada
            if ($katalog->foto_produk && Storage::exists($katalog->foto_produk)) {
                Storage::delete($katalog->foto_produk);
            }

            // Simpan foto baru ke folder katalog
            $fotoPath = $request->file('foto_produk')->store('katalogs', 'public');
            $katalog->foto_produk = $fotoPath;

            // Ambil basename file untuk dicari di folder lelangs
            $basenameFoto = basename($fotoPath);

            // Cari semua lelang yang menggunakan gambar dengan basename yang sama
            // $lelangs = M_Lelang::where('foto_produk', 'like', "lelangs/%$basenameLama")->get();

            // if ($lelangs->isNotEmpty()) {
                // foreach ($lelangs as $lelang) {
                    // Hapus gambar lama di folder lelangs
                    if (Storage::exists('lelangs/'. $basenameLama)) {
                        Storage::delete('lelangs/'. $basenameLama);
                    }

                    // Salin gambar baru ke folder lelangs
                    $destinationPath = 'lelangs/' . $basenameFoto;
                    Storage::copy($fotoPath, $destinationPath);

                    // Update path di database lelangs
                    $lelangs = M_Lelang::where('foto_produk', 'like', "lelangs/$basenameLama")->get();
                    foreach ($lelangs as $lelang) {
                        $lelang->foto_produk = $destinationPath;
                        $lelang->save();
                    }
                // }
            // } else {
                // Jika tidak ada lelang yang memakai gambar ini, salin gambar baru ke folder lelangs
                // $destinationPath = 'lelangs/' . $basenameFoto;
                // Storage::copy($fotoPath, $destinationPath);
            // }

        }

        // Perbarui atribut lainnya
        $katalog->nama_produk = $request->input('nama_produk');
        $katalog->deskripsi_produk = $request->input('deskripsi_produk');
        $katalog->harga_perkilo = $request->input('harga_perkilo');

        // Simpan perubahan
        $katalog->save();

        return redirect()->route('katalog.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $katalog = M_Katalog::findOrFail($id);
        $katalog->delete(); // soft delete

        return redirect()->route('katalog.index')->with('success', 'Produk berhasil dihapus.');
    }

    // Menampilkan produk yang terhapus
    public function trashed()
    {
        $katalog = M_Katalog::onlyTrashed()->get(); // Ambil produk yang dihapus
        return view('products.trashed', compact('products'));
    }

    // Mengembalikan produk yang dihapus
    public function restore($id)
    {
        if (!$id) {
            return redirect()->route('katalog.index')->with('error', 'ID tidak ditemukan.');
        }

        $katalog = M_Katalog::onlyTrashed()->findOrFail($id);
        $katalog->restore(); // Mengembalikan produk

        return redirect()->route('katalog.index')->with('success', 'Produk berhasil dikembalikan.');
    }

    // Menghapus permanen produk
    public function forceDelete($id)
    {
        $katalog = M_Katalog::onlyTrashed()->findOrFail($id);
        $katalog->forceDelete(); // Hapus permanen

        return redirect()->route('katalog.index')->with('success', 'Produk berhasil dihapus permanen.');
    }

    // api
    public function getKatalog($id): JsonResponse
    {
        $katalog = M_Katalog::find($id);

        if ($katalog) {
            return response()->json([
                'id' => $katalog->id,
                'nama_produk' => $katalog->nama_produk,
                'foto_produk' => $katalog->foto_produk, // Pastikan kolom ini benar
            ]);
        } else {
            return response()->json(['error' => 'Katalog tidak ditemukan'], 404);
        }
    }
}
