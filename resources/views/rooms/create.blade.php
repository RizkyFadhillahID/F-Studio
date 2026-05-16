@extends('layouts.app')
@section('title', 'Tambah Ruangan')
@section('page-title', 'Tambah Ruangan')
@section('content')
    <div class="page-card p-4" style="max-width:600px">
        <form method="POST" action="{{ route('rooms.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nama Ruangan</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kode</label>
                    <input type="text" name="code" value="{{ old('code') }}"
                        class="form-control @error('code') is-invalid @enderror" placeholder="STD-01" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kapasitas</label>
                    <input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1"
                        class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Fasilitas <small class="text-muted">(pisahkan dengan
                            koma)</small></label>
                    <input type="text" name="facilities" value="{{ old('facilities') }}" class="form-control"
                        placeholder="AC, WiFi, Proyektor">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Foto</label>
                    <input type="file" name="image" accept="image/*"
                        class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background:#e94560">Simpan</button>
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
