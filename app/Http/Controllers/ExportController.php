<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyAdminRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\Vehicle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Menggantikan lib/piellot-excel.ts pada source aslinya.
 * Versi Next.js menulis file .xlsx secara manual byte-per-byte (tanpa
 * dependency) karena berjalan di Cloudflare Workers. Di Laravel kita punya
 * akses composer, jadi kita pakai PhpSpreadsheet (phpoffice/phpspreadsheet)
 * yang jauh lebih mudah dibaca dan dirawat.
 *
 * Hasilnya sama: 7 sheet -> Ringkasan, Perusahaan, Pengguna, Armada,
 * Booking & Tagihan, Permintaan Admin, Chat.
 */
class ExportController extends Controller
{
    private const STATUS_LABELS = [
        'ACTIVE' => 'Aktif', 'AVAILABLE' => 'Tersedia', 'MAINTENANCE' => 'Perawatan',
        'PENDING' => 'Menunggu', 'APPROVED' => 'Disetujui', 'CONFIRMED' => 'Dikonfirmasi',
        'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak',
    ];

    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->summarySheet($spreadsheet);
        $this->companiesSheet($spreadsheet);
        $this->usersSheet($spreadsheet);
        $this->vehiclesSheet($spreadsheet);
        $this->bookingsSheet($spreadsheet);
        $this->adminRequestsSheet($spreadsheet);
        $this->messagesSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'piellot-export-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function label(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    private function writeSheet(Spreadsheet $spreadsheet, string $name, array $headers, array $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($name);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function summarySheet(Spreadsheet $spreadsheet): void
    {
        $this->writeSheet($spreadsheet, 'Ringkasan', ['Item', 'Jumlah'], [
            ['Perusahaan', Company::count()],
            ['Pengguna (PIC)', User::where('role', 'PIC')->count()],
            ['Armada', Vehicle::count()],
            ['Booking', Booking::count()],
            ['Permintaan admin', CompanyAdminRequest::count()],
            ['Pesan chat', Message::count()],
            ['Diekspor pada', now()->format('d M Y H:i')],
        ]);
    }

    private function companiesSheet(Spreadsheet $spreadsheet): void
    {
        $rows = Company::orderByDesc('created_at')->get()->map(fn (Company $c) => [
            $c->id, $c->name, $c->primary_pic_name, $c->phone, $c->email,
            $this->label($c->status), $c->created_at->format('d M Y H:i'),
        ])->all();

        $this->writeSheet($spreadsheet, 'Perusahaan', ['ID', 'Nama', 'PIC', 'Telepon', 'Email', 'Status', 'Dibuat'], $rows);
    }

    private function usersSheet(Spreadsheet $spreadsheet): void
    {
        $rows = User::where('role', 'PIC')->with('company')->orderBy('created_at')->get()->map(fn (User $u) => [
            $u->id, $u->company?->name ?? '-', $u->name, $u->email, $this->label($u->status),
        ])->all();

        $this->writeSheet($spreadsheet, 'Pengguna', ['ID', 'Perusahaan', 'Nama', 'Email', 'Status'], $rows);
    }

    private function vehiclesSheet(Spreadsheet $spreadsheet): void
    {
        $rows = Vehicle::orderBy('created_at')->get()->map(fn (Vehicle $v) => [
            $v->id, $v->plate, $v->type, $v->capacity_ton, $this->label($v->status), $v->created_at->format('d M Y H:i'),
        ])->all();

        $this->writeSheet($spreadsheet, 'Armada', ['ID', 'Plat', 'Tipe', 'Kapasitas (ton)', 'Status', 'Dibuat'], $rows);
    }

    private function bookingsSheet(Spreadsheet $spreadsheet): void
    {
        $rows = Booking::with(['company', 'vehicle'])->orderByDesc('created_at')->get()->map(fn (Booking $b) => [
            $b->id, $b->company?->name, $b->pic_name, $b->vehicle?->plate, $b->vehicle?->type,
            $b->packageLabel(), $b->booking_date->format('d M Y'), (float) $b->load_ton, $b->destination,
            $b->passengers, $b->need_driver ? 'Ya' : 'Tidak', $this->label($b->status),
            $b->rental_fee, $b->driver_fee, $b->other_fee, $b->total_fee, $b->paid ? 'Lunas' : 'Belum lunas',
            $b->created_at->format('d M Y H:i'),
        ])->all();

        $this->writeSheet($spreadsheet, 'Booking & Tagihan', [
            'ID', 'Perusahaan', 'PIC', 'Plat', 'Tipe Armada', 'Paket', 'Tanggal', 'Muatan (ton)',
            'Tujuan', 'Kernet', 'Driver', 'Status', 'Biaya Sewa', 'Biaya Driver', 'Biaya Lain',
            'Total Tagihan', 'Pembayaran', 'Dibuat',
        ], $rows);
    }

    private function adminRequestsSheet(Spreadsheet $spreadsheet): void
    {
        $rows = CompanyAdminRequest::with('company')->orderByDesc('created_at')->get()->map(fn (CompanyAdminRequest $r) => [
            $r->id, $r->company?->name, $r->requested_name, $r->requested_email,
            $this->label($r->status), $r->created_at->format('d M Y H:i'),
        ])->all();

        $this->writeSheet($spreadsheet, 'Permintaan Admin', ['ID', 'Perusahaan', 'Nama', 'Email', 'Status', 'Dibuat'], $rows);
    }

    private function messagesSheet(Spreadsheet $spreadsheet): void
    {
        $rows = Message::with('company')->orderBy('created_at')->get()->map(fn (Message $m) => [
            $m->id, $m->company?->name, $this->label($m->sender_role), $m->sender_name, $m->body,
            $m->created_at->format('d M Y H:i'),
        ])->all();

        $this->writeSheet($spreadsheet, 'Chat', ['ID', 'Perusahaan', 'Pengirim', 'Nama Pengirim', 'Pesan', 'Waktu'], $rows);
    }
}
