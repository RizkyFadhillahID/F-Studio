@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')
@section('content')
    <div class="page-card p-4" style="max-width:500px">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}"
                    class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn text-white" style="background:#e94560">Update</button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
