@extends('layouts.app')
@section('title', 'Edit Peralatan')
@section('page-title', 'Edit Peralatan')
@section('content')
    <div class="page-card p-4" style="max-width:640px">
        <form method="POST" action="{{ route('equipment.update', $equipment) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Peralatan</label>
                    <input type="text" name="name" value="{{ old('name', $equipment->name) }}"
                        class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode</label>
                    <input type="text" name="code" value="{{ old('code', $equipment->code) }}"
                        class="form-control @error('code') is-invalid @enderror" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $equipment->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Jumlah Total</label>
                    <input type="number" name="quantity_total"
                        value="{{ old('quantity_total', $equipment->quantity_total) }}" min="1" class="form-control"
                        required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $equipment->description) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $equipment->location) }}"
                        class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Foto Baru</label>
                    @if ($equipment->image)
                        <img src="{{ Storage::url($equipment->image) }}" height="50" class="d-block mb-1 rounded">
                    @endif
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background:#e94560">Update</button>
                <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
