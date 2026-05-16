@extends('layouts.app')
@section('title', 'Tambah Peralatan')
@section('page-title', 'Tambah Peralatan')
@section('content')
    <div class="page-card p-4" style="max-width:640px">
        <form method="POST" action="{{ route('equipment.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Peralatan</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode</label>
                    <input type="text" name="code" value="{{ old('code') }}"
                        class="form-control @error('code') is-invalid @enderror" placeholder="CAM-001" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Jumlah</label>
                    <input type="number" name="quantity_total" value="{{ old('quantity_total', 1) }}" min="1"
                        class="form-control @error('quantity_total') is-invalid @enderror" required>
                    @error('quantity_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location') }}" class="form-control">
                </div>
                <div class="col-md-6">
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
                <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection