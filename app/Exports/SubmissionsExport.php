<?php

namespace App\Exports;

use App\Models\Form;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubmissionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $form;
    protected $fields;

    public function __construct(Form $form)
    {
        $this->form = $form;
        // Ambil daftar field HANYA SATU KALI dan simpan. Ini sangat efisien.
        $this->fields = $this->form->formFields()->orderBy('ordering')->get();
    }

    /**
    * Mengambil data submission dari database.
    */
    public function collection()
    {
        // Ambil semua submission beserta data jawabannya sekaligus.
        return $this->form->submissions()->with('submissionData')->latest('submitted_at')->get();
    }

    /**
     * Menentukan header (judul kolom) di file Excel.
     */
    public function headings(): array
    {
        // Ambil semua label dari field yang sudah disimpan.
        $headers = $this->fields->pluck('label')->toArray();
        // Tambahkan 'Tanggal Submit' sebagai kolom pertama.
        array_unshift($headers, 'Tanggal Submit');
        return $headers;
    }

    /**
     * Memetakan data untuk setiap baris di Excel.
     * Ini adalah logika baru yang lebih aman dan cepat.
     */
    public function map($submission): array
    {
        // 1. Buat "kamus" jawaban untuk submission ini (field_id => value).
        $submissionValues = $submission->submissionData->pluck('value', 'form_field_id');

        // 2. Siapkan baris kosong.
        $row = [];
        
        // 3. Kolom pertama selalu diisi tanggal submit.
        $row[] = $submission->submitted_at->format('d-m-Y H:i:s');

        // 4. Loop melalui setiap field yang seharusnya ada di form.
        foreach ($this->fields as $field) {
            // Ambil jawaban dari "kamus" berdasarkan ID field.
            // Jika tidak ada jawaban, gunakan tanda '-'.
            $row[] = $submissionValues[$field->id] ?? '-';
        }
        
        return $row;
    }
}