@extends('layouts.admin')

@section('title', 'Profil Saya - Admin DLH')
@section('heading', 'Profil Saya')

@section('content')
    <x-admin.page-header
        title="Profil Saya"
        subtitle="Kelola informasi akun dan keamanan Anda."
        icon="user"
    />

    @if($errors->any())
        <x-admin.alert type="error" dismissible>
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-admin.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Informasi Akun --}}
        <div class="lg:col-span-2">
            <x-admin.card>
                <h2 class="mb-1 text-h4 font-bold text-ink-900">Informasi Akun</h2>
                <p class="mb-5 text-sm text-slate-500">Perbarui nama, username, email, dan foto profil.</p>

                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5"
                    x-data="{ preview: '{{ $user->photoUrl() ?? '' }}' }">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-4">
                        <template x-if="preview">
                            <img :src="preview" alt="Foto" class="size-16 rounded-full object-cover ring-2 ring-slate-200">
                        </template>
                        <template x-if="!preview">
                            <span class="grid size-16 place-items-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">
                                {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn($w) => mb_substr($w,0,1))->take(2)->implode('') }}
                            </span>
                        </template>
                        <div>
                            <label class="cursor-pointer">
                                <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-ink-700 transition hover:bg-slate-50">
                                    <x-admin.icon name="upload" :size="16" /> Ganti Foto
                                </span>
                                <input type="file" name="photo" accept="image/*" class="hidden"
                                    x-on:change="preview = URL.createObjectURL($event.target.files[0])">
                            </label>
                            <p class="mt-1 text-xs text-slate-400">JPG, PNG, WEBP maks 2MB</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-ink-700">Nama Lengkap</label>
                            <input name="name" value="{{ old('name', $user->name) }}" required
                                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-ink-700">Username</label>
                            <input name="username" value="{{ old('username', $user->username) }}" required
                                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-bold text-ink-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-admin.button variant="primary" type="submit" icon="check">Simpan Perubahan</x-admin.button>
                    </div>
                </form>
            </x-admin.card>
        </div>

        {{-- Ganti Password --}}
        <div>
            <x-admin.card>
                <h2 class="mb-1 text-h4 font-bold text-ink-900">Keamanan</h2>
                <p class="mb-5 text-sm text-slate-500">Ubah password akun Anda.</p>

                <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-bold text-ink-700">Password Saat Ini</label>
                        <input type="password" name="current_password" required
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-ink-700">Password Baru</label>
                        <input type="password" name="password" required
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-ink-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" required
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="flex justify-end">
                        <x-admin.button variant="secondary" type="submit" icon="lock">Ubah Password</x-admin.button>
                    </div>
                </form>
            </x-admin.card>
        </div>
    </div>
@endsection
