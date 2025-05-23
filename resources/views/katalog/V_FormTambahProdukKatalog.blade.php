@extends('layouts.app')

@section('title')
    @if(isset($katalog))
        Ubah Katalog
    @else
        Tambah Katalog
    @endif
@endsection

@section('content')
    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg my-10 ">
        <h1 class="text-2xl font-bold mb-6 text-center p-5 bg-[#CEED82]">
            {{ isset($katalog) ? 'Ubah Detail Produk' : 'Tambahkan Produk' }}
        </h1>

        <div class="px-6 pb-6">

            <form id="katalogForm" action="{{ isset($katalog) ? route('katalog.update', $katalog->id) : route('katalog.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
                >
                @csrf
                @if (isset($katalog))
                    @method('PUT')
                    <input id="katalogID" type="hidden" name="katalog_id" value="{{ $katalog->id }}">
                @endif
                {{-- nama produk --}}
                <div>
                    <label for="nama_produk" class="block text-gray-700 font-medium">Nama Produk:</label>
                    <input type="text" name="nama_produk" id="nama_produk" value="{{ old('nama_produk', $katalog->nama_produk ?? '') }}" required
                        class="w-full mt-1 border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                {{-- deskripsi produk --}}
                <div>
                    <label for="deskripsi_produk" class="block text-gray-700 font-medium">Deskripsi Produk:</label>
                    <textarea name="deskripsi_produk" id="deskripsi_produk"
                        class="w-full mt-1 border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi_produk', $katalog->deskripsi_produk ?? '') }}</textarea>
                </div>
                {{-- harga perkilo --}}
                <div>
                    <label for="harga_perkilo" class="block text-gray-700 font-medium">Harga per Kilo:</label>
                    <input type="number" name="harga_perkilo" id="harga_perkilo" value="{{ old('harga_perkilo', $katalog->harga_perkilo ?? '') }}" required
                        class="w-full mt-1 border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                {{-- foto produk --}}
                <div>
                    <label for="foto_produk" class="block text-gray-700 font-medium mb-2">Foto Produk : <a id="teksFotoDipilih"></a></label>
                    <div class="flex items-start mt-4 space-x-4">
                        <!-- Kotak Input untuk Upload -->
                        <div class="flex flex-col space-y-2">
                            <p class="text-sm text-gray-500"><br></p>
                            <label for="foto_produk" class="w-32 h-32 flex items-center justify-center cursor-pointer border-2 border-gray-300 border-dashed text-gray-500 hover:border-blue-500">
                                <span id="uploadPlaceholder" class="text-sm text-center">Klik untuk Upload</span>
                                <input type="file" name="foto_produk" id="foto_produk" accept="image/*" class="hidden" onchange="previewImage(event)">
                            </label>
                        </div>
                         <!-- Foto Saat Ini (Jika Ada) -->
                        @if(isset($katalog) && $katalog->foto_produk)
                            <div class="flex flex-col space-y-2">
                                <p class="text-sm text-gray-500">Foto Saat Ini:</p>
                                <img src="{{ asset('storage/' . $katalog->foto_produk) }}" alt="Foto Produk" class="w-32 h-32 object-cover cursor-pointer border-2 border-gray-300" onclick="selectImage(this, '{{ $katalog->foto_produk }}', 'Foto Saat Ini')" id="currentImage">
                            </div>
                        @endif
                        <!-- Pratinjau Gambar Baru -->
                        <div class="flex flex-col space-y-2">
                            <p class="text-sm text-gray-500">Pratinjau Upload Baru:</p>
                            <img id="preview" src="#" alt="Pratinjau Foto Baru" class="w-32 h-32 object-cover cursor-pointer border-2 border-gray-300 hidden" onclick="selectImage(this, 'new', 'Pratinjau Upload Baru')">
                        </div>
                    </div>
                    <input type="hidden" name="selected_image" id="selected_image" value="{{ $katalog->foto_produk ?? '' }}" required>
                </div>
                {{-- tombol2 --}}
                <div class="text-center">
                    {{-- <button type="button" onclick="window.history.back();" class="px-4 py-2 mt-4 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 cursor-pointer">
                        Kembali
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#255B22] text-white rounded-lg shadow hover:bg-[#1d331c] focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 cursor-pointer">
                        {{ isset($katalog) ? 'Simpan Perubahan' : 'Tambahkan' }}
                    </button> --}}

                    <button type="button" onclick="window.history.back();"
                        class="px-4 py-2 mt-4 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 cursor-pointer"
                        >
                        Kembali
                    </button>
                    @if (isset($katalog))
                        <button type="button" id="confirmButton"
                            class="px-4 py-2 bg-sekunderDark text-white rounded-lg shadow hover:bg-primer focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 cursor-pointer"
                            >
                            Simpan Perubahan
                        </button>
                    @else
                        <button type="submit" id="confirmButton"
                            class="px-4 py-2 bg-sekunderDark text-white rounded-lg shadow hover:bg-primer focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 cursor-pointer">
                            Tambahkan
                        </button>
                    @endif
                </div>
            </form>

        </div>

    </div>

    {{-- logic pemilihan gambar --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Set the initial selected image to the current image, if available
            const currentImage = document.getElementById('currentImage');
            if (currentImage) {
                selectImage(currentImage, currentImage.getAttribute('onclick').match(/'(.*?)'/)[1], 'Foto Saat Ini');
            }
        });

        // memunculkan image yang diupload
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            const currentImage = document.getElementById('currentImage');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    selectImage(preview, 'new', 'Pratinjau Upload Baru'); // Automatically select the new image
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
                preview.src = '#';
                if (currentImage) {
                    selectImage(currentImage, currentImage.getAttribute('onclick').match(/'(.*?)'/)[1], 'Foto Saat Ini');
                } else {
                    document.getElementById('selected_image').value = '';
                    document.getElementById('teksFotoDipilih').innerText = null;
                }
            }
        }

        // memilih image yang akan disubmit
        function selectImage(imageElement, value, teks) {
            console.log("Selected image value:", document.getElementById('selected_image').value);
            console.log("Selected image value:", document.getElementById('foto_produk').value);

            // Reset styles on all images
            document.querySelectorAll('img').forEach(img => img.classList.remove('ring-4', 'ring-blue-500'));
            // Highlight selected image
            imageElement.classList.add('ring-4', 'ring-blue-500');
            document.getElementById('teksFotoDipilih').innerText = teks;
            // Set selected image value
            document.getElementById('selected_image').value = value;
            console.log(document.getElementById('selected_image').value);
        }
        console.log(document.getElementById('selected_image').value);
    </script>

    <script>
        document.getElementById("confirmButton").addEventListener("click", () => {
            const form = document.getElementById("katalogForm");
            const katalogId = document.getElementById("katalogID").value;

            const metadataUrl = `/katalog/${katalogId}/edit-konfirm`;
            console.log(metadataUrl);
            fetch(metadataUrl, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
            })
            .then((response) => {
                if (!response.ok) throw new Error("Gagal mengambil metadata");
                return response.json();
            })
            .then((metadata) => {
                // Panggil showAlert dengan metadata dari backend
                showAlert({
                    title: metadata.title,
                    text: metadata.text,
                    icon: metadata.icon,
                    confirmButtonText: metadata.confirmButtonText,
                    cancelButtonText: metadata.cancelButtonText,
                    onConfirm: () => {
                        // Ubah form method ke PUT dan submit
                        const methodInput = document.createElement("input");
                        methodInput.type = "hidden";
                        methodInput.name = "_method";
                        methodInput.value = "PUT";
                        form.appendChild(methodInput);

                        form.submit();
                    },
                });
            })
            .catch((error) => {
                console.error("Error:", error);
                // Tampilkan notifikasi error jika diperlukan
            });
        });
    </script>
@endsection
