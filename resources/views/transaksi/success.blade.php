@extends('layouts.app')

@section('title', 'Checkout Berhasil')

@section('content')

    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                <h1 class="text-success">Pembayaran Berhasil</h1>
                <p>{{ $transaksi->kode_transaksi }}</p>
                <p>{{ $transaksi->status_transaksi_id }}</p>
                <p class="text-success">Terima kasih telah melakukan pembayaran</p>
                <a href="{{ route('transaksi.index') }}" class="btn btn-primary mt-3">Lihat Transaksi</a>
            </div>
        </div>
    </div>

@endsection
