<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class M_PasangLelang extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'pasang_lelangs';

    protected $fillable = [
        'lelang_id',
        'user_id',
        'harga_pengajuan',
    ];

    // reference this lelang_id ke lelangs id
    public function lelang()
    {
        return $this->belongsTo(M_Lelang::class, 'lelang_id');
    }
    // reference this user_id ke users id
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function transaksi()
    {
        return $this->hasMany(M_Transaksi::class, 'pasang_lelang_id');
    }

}
