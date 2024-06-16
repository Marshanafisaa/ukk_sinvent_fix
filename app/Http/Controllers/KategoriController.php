<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class KategoriController extends Controller
{
    public function index(Request $request)
    {
        // $rsetKategori = DB::table('kategori')
        // ->select('id', 'deskripsi', DB::raw('ketKategorik(kategori) as ketkategorik'))
        // ->orderBy('kategori', 'asc') // Menambahkan orderBy untuk mengurutkan berdasarkan deskripsi (kategori) secara ascending
        // ->paginate(10);

        // return view('kategori.index', compact('rsetKategori'))
        // ->with('i', ($request->input('page', 1) - 1) * 10);

        $keyword = $request->input('keyword');

        // Query untuk mencari kategori berdasarkan keyword
        $query = DB::table('kategori')
            ->select('id', 'deskripsi', DB::raw('ketKategorik(kategori) as ketkategorik'))
            ->orderBy('kategori', 'asc');
    
        if (!empty($keyword)) {
            $query->where('deskripsi', 'LIKE', "%$keyword%")
                  ->orWhereRaw('ketKategorik(kategori) COLLATE utf8mb4_unicode_ci LIKE ?', ["%$keyword%"]);
        }
    
        $rsetKategori = $query->paginate(10);
    
        return view('kategori.index', compact('rsetKategori'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $aKategori = array('blank'=>'Pilih Kategori',
                            'A'=>'Alat',
                            'M'=>'Barang Modal',
                            'BHP'=>'Bahan Habis Pakai',
                            'BTHP'=>'Bahan Tidak Habis Pakai'
                            );
        return view('kategori.create',compact('aKategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    
        // return $request->all();
        // if(DB::table('kategori')->where('deskripsi', $request)->exists()){
        //     return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Disimpan!']);
       
        // }else{
        //     $request->validate( [
        //         'deskripsi'   => 'required | unique:kategori',
        //         'kategori'     => 'required | in:M,A,BHP,BTHP',
        //     ]);
    
    
        //     //create post
        //     Kategori::create([
        //         'deskripsi'  => $request->deskripsi,
        //         'kategori'  => $request->kategori,
        //     ])
        //     ->latest()
        //     ->paginate(10);;
    
        //     //redirect to index
    
        //     return redirect()->route('kategori.index')->with(['gagal' => 'Data Gagal Dihapus! Data Sudah Ada']);
        
        {
            // Validate the request
            $request->validate([
                'deskripsi' => 'required|unique:kategori',
                'kategori'  => 'required|in:M,A,BHP,BTHP',
            ]);
    
            try {
                DB::beginTransaction(); // Start the transaction
    
                // Insert a new category using Eloquent
                Kategori::create([
                    'deskripsi' => $request->deskripsi,
                    'kategori'  => $request->kategori,
                    'status'    => 'pending',
                ]);
    
                DB::commit(); // Commit the changes
    
                // Flash success message to the session
                Session::flash('success', 'Kategori berhasil disimpan!');
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback in case of an exception
                report($e); // Report the exception
    
                // Flash failure message to the session
                Session::flash('gagal', 'Kategori gagal disimpan!');
            }
    
            // Redirect to the index route with a success message
            return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rsetKategori = Kategori::find($id);

        return view('kategori.show', compact('rsetKategori'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kategori = Kategori::find($id);
        return view('kategori.edit', compact('kategori'));    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate( [
            'deskripsi'              => 'required',
            'kategori'              => 'required',
        ]);

        $kategori = Kategori::find($id);
            $kategori->update([
                'deskripsi'              => $request->deskripsi,
                'kategori'              => $request->kategori,
            ]);
            
            try {
                DB::beginTransaction(); // Start the transaction
    
                // Insert a new category using Eloquent
                Kategori::create([
                    'deskripsi' => $request->deskripsi,
                    'kategori'  => $request->kategori,
                    'status'    => 'pending',
                ]);
    
                DB::commit(); // Commit the changes
    
                // Flash success message to the session
                Session::flash('success', 'Kategori berhasil disimpan!');
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback in case of an exception
                report($e); // Report the exception
    
                // Flash failure message to the session
                Session::flash('gagal', 'Kategori gagal disimpan!');
            }

        return redirect()->route('kategori.index')->with(['success' => 'Data Kategori Berhasil Diubah!']);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if(DB::table('barang')->where('kategori_id', $id)->exists()) {
        return redirect()->route('kategori.index')->with(['gagal' => 'Data Gagal Dihapus Karena Sedang Dipakai!']);            

        } 

        else {
            $rsetKategori = Kategori::find($id);

            //delete post
            $rsetKategori->delete();
    
            //redirect to index
            return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Dihapus!']);
        }
    }

}