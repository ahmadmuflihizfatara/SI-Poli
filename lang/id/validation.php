<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    |
    | Pesan galat bawaan validator dalam bahasa Indonesia (diadaptasi dari
    | laravel-lang/lang). Aturan ukuran punya beberapa versi sesuai tipe data.
    |
    */

    'accepted' => ':Attribute harus disetujui.',
    'accepted_if' => ':Attribute harus disetujui bila :other bernilai :value.',
    'active_url' => ':Attribute harus berupa URL yang valid.',
    'after' => ':Attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':Attribute tidak valid.',
    'array' => ':Attribute harus berupa larik.',
    'array_keys' => ':Attribute hanya boleh berisi kunci berikut: :values.',
    'ascii' => ':Attribute hanya boleh berisi karakter alfanumerik dan simbol satu bita.',
    'base64' => ':Attribute harus berupa teks Base64 yang valid.',
    'before' => ':Attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':Attribute harus berisi antara :min dan :max item.',
        'file' => ':Attribute harus berukuran antara :min dan :max kilobita.',
        'numeric' => ':Attribute harus bernilai antara :min dan :max.',
        'string' => ':Attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai benar atau salah.',
    'can' => ':Attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => ':Attribute kehilangan nilai yang wajib ada.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':Attribute harus berupa tanggal yang valid.',
    'date_equals' => ':Attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':Attribute harus sesuai format :format.',
    'decimal' => ':Attribute harus memiliki :decimal angka desimal.',
    'declined' => ':Attribute harus ditolak.',
    'declined_if' => ':Attribute harus ditolak bila :other bernilai :value.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus terdiri dari :digits digit.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max digit.',
    'dimensions' => 'Dimensi gambar :attribute tidak valid.',
    'distinct' => ':Attribute memiliki nilai ganda.',
    'doesnt_contain' => ':Attribute tidak boleh berisi salah satu dari: :values.',
    'doesnt_end_with' => ':Attribute tidak boleh diakhiri salah satu dari: :values.',
    'doesnt_start_with' => ':Attribute tidak boleh diawali salah satu dari: :values.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'encoding' => ':Attribute harus dikodekan dalam :encoding.',
    'ends_with' => ':Attribute harus diakhiri salah satu dari: :values.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'extensions' => ':Attribute harus berekstensi salah satu dari: :values.',
    'file' => ':Attribute harus berupa berkas.',
    'filled' => ':Attribute harus diisi.',
    'gt' => [
        'array' => ':Attribute harus berisi lebih dari :value item.',
        'file' => ':Attribute harus berukuran lebih dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai lebih dari :value.',
        'string' => ':Attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':Attribute harus berisi :value item atau lebih.',
        'file' => ':Attribute harus berukuran minimal :value kilobita.',
        'numeric' => ':Attribute harus bernilai minimal :value.',
        'string' => ':Attribute harus minimal :value karakter.',
    ],
    'hex_color' => ':Attribute harus berupa warna heksadesimal yang valid.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute harus ada di :other.',
    'in_array_keys' => ':Attribute harus berisi minimal salah satu kunci berikut: :values.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'ip' => ':Attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':Attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':Attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':Attribute harus berupa teks JSON yang valid.',
    'list' => ':Attribute harus berupa daftar.',
    'lowercase' => ':Attribute harus berupa huruf kecil.',
    'lt' => [
        'array' => ':Attribute harus berisi kurang dari :value item.',
        'file' => ':Attribute harus berukuran kurang dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai kurang dari :value.',
        'string' => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':Attribute tidak boleh berisi lebih dari :value item.',
        'file' => ':Attribute harus berukuran maksimal :value kilobita.',
        'numeric' => ':Attribute harus bernilai maksimal :value.',
        'string' => ':Attribute harus maksimal :value karakter.',
    ],
    'mac_address' => ':Attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':Attribute tidak boleh berisi lebih dari :max item.',
        'file' => ':Attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => ':Attribute tidak boleh lebih dari :max digit.',
    'mimes' => ':Attribute harus berupa berkas bertipe: :values.',
    'mimetypes' => ':Attribute harus berupa berkas bertipe: :values.',
    'min' => [
        'array' => ':Attribute harus berisi minimal :min item.',
        'file' => ':Attribute harus berukuran minimal :min kilobita.',
        'numeric' => ':Attribute harus bernilai minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'min_digits' => ':Attribute harus minimal :min digit.',
    'missing' => ':Attribute harus tidak ada.',
    'missing_if' => ':Attribute harus tidak ada bila :other bernilai :value.',
    'missing_unless' => ':Attribute harus tidak ada kecuali :other bernilai :value.',
    'missing_with' => ':Attribute harus tidak ada bila :values ada.',
    'missing_with_all' => ':Attribute harus tidak ada bila :values ada.',
    'multiple_of' => ':Attribute harus kelipatan :value.',
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus berisi minimal satu huruf.',
        'mixed' => ':Attribute harus berisi minimal satu huruf besar dan satu huruf kecil.',
        'numbers' => ':Attribute harus berisi minimal satu angka.',
        'symbols' => ':Attribute harus berisi minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor dalam kebocoran data. Silakan pilih :attribute lain.',
    ],
    'present' => ':Attribute harus ada.',
    'present_if' => ':Attribute harus ada bila :other bernilai :value.',
    'present_unless' => ':Attribute harus ada kecuali :other bernilai :value.',
    'present_with' => ':Attribute harus ada bila :values ada.',
    'present_with_all' => ':Attribute harus ada bila :values ada.',
    'prohibited' => ':Attribute tidak boleh diisi.',
    'prohibited_if' => ':Attribute tidak boleh diisi bila :other bernilai :value.',
    'prohibited_if_accepted' => ':Attribute tidak boleh diisi bila :other disetujui.',
    'prohibited_if_declined' => ':Attribute tidak boleh diisi bila :other ditolak.',
    'prohibited_unless' => ':Attribute tidak boleh diisi kecuali :other ada di :values.',
    'prohibits' => ':Attribute melarang :other untuk diisi.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_array_keys' => ':Attribute wajib berisi entri untuk: :values.',
    'required_if' => ':Attribute wajib diisi bila :other bernilai :value.',
    'required_if_accepted' => ':Attribute wajib diisi bila :other disetujui.',
    'required_if_declined' => ':Attribute wajib diisi bila :other ditolak.',
    'required_unless' => ':Attribute wajib diisi kecuali :other ada di :values.',
    'required_with' => ':Attribute wajib diisi bila :values ada.',
    'required_with_all' => ':Attribute wajib diisi bila :values ada.',
    'required_without' => ':Attribute wajib diisi bila :values tidak ada.',
    'required_without_all' => ':Attribute wajib diisi bila tidak ada satu pun dari :values.',
    'same' => ':Attribute harus sama dengan :other.',
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
        'file' => ':Attribute harus berukuran :size kilobita.',
        'numeric' => ':Attribute harus bernilai :size.',
        'string' => ':Attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => ':Attribute harus diawali salah satu dari: :values.',
    'string' => ':Attribute harus berupa teks.',
    'timezone' => ':Attribute harus berupa zona waktu yang valid.',
    'unique' => ':Attribute sudah digunakan.',
    'uploaded' => ':Attribute gagal diunggah.',
    'uppercase' => ':Attribute harus berupa huruf besar.',
    'url' => ':Attribute harus berupa URL yang valid.',
    'ulid' => ':Attribute harus berupa ULID yang valid.',
    'uuid' => ':Attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Kustom
    |--------------------------------------------------------------------------
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut
    |--------------------------------------------------------------------------
    |
    | Mengganti nama kolom (mis. "username") dengan nama yang mudah dibaca.
    |
    */

    'attributes' => [
        'username' => 'nama pengguna',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'name' => 'nama',
        'email' => 'email',
        'role' => 'peran',
        'dari' => 'tanggal awal',
        'sampai' => 'tanggal akhir',
    ],

];
