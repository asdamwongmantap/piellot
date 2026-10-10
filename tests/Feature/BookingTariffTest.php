<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Company;
use App\Models\RentalPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTariffTest extends TestCase
{
    use RefreshDatabase;

    private function book(string $code, bool $driver, int $toll): Booking
    {
        $company = Company::create(['name' => 'PT Uji', 'primary_pic_name' => 'Budi', 'phone' => '0812345678', 'email' => 'b@x.id', 'status' => 'ACTIVE']);
        $pic = User::factory()->create(['role' => 'PIC', 'status' => 'ACTIVE', 'company_id' => $company->id]);
        $vehicle = Vehicle::create(['plate' => 'B 1234 XY', 'type' => 'CDE', 'capacity_ton' => 2.5, 'status' => 'AVAILABLE']);

        $this->actingAs($pic)->post('/bookings', [
            'vehicle_id' => $vehicle->id, 'package_code' => $code, 'booking_date' => now()->addDay()->toDateString(),
            'load_ton' => 1, 'destination' => 'Bandung', 'passengers' => 1, 'need_driver' => $driver, 'toll_fee' => $toll,
        ])->assertSessionHasNoErrors();

        return Booking::firstOrFail();
    }

    public function test_seeded_packages_match_tariff(): void
    {
        $this->assertSame([200_000, 75_000, 0], RentalPackage::where('code', '4h')->get(['rate', 'driver_rate', 'tolerance_hours'])->map(fn ($p) => array_values($p->only(['rate', 'driver_rate', 'tolerance_hours'])))->first());
        $this->assertSame(350_000, RentalPackage::where('code', '8h')->value('rate'));
        $this->assertSame(['08.00–08.00 (+1 hari)', '20.00–20.00 (+1 hari)'], RentalPackage::whereIn('code', ['24h-am', '24h-pm'])->orderBy('code')->pluck('window_label')->all());
    }

    public function test_24h_evening_with_driver_and_toll_is_billed_cost_to_cost(): void
    {
        $booking = $this->book('24h-pm', true, 85_000);

        $this->assertSame(500_000, $booking->rental_fee);
        $this->assertSame(300_000, $booking->driver_fee);
        $this->assertSame(85_000, $booking->toll_fee);
        $this->assertSame(885_000, $booking->total_fee);
    }

    public function test_driver_rate_follows_package(): void
    {
        $this->assertSame(150_000, $this->book('8h', true, 0)->driver_fee);
    }

    private function adminUpdate(Booking $booking, array $data): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
        $this->actingAs($admin)->post("/invoices/{$booking->id}", $data + [
            'other_fee' => 0, 'toll_fee' => $booking->toll_fee, 'late_fee' => $booking->late_fee,
        ])->assertSessionHasNoErrors();
    }

    public function test_late_fee_is_automatic_after_tolerance_and_can_be_adjusted(): void
    {
        $booking = $this->book('24h-am', false, 0); // jatuh tempo 08.00 hari berikutnya, toleransi 1 jam
        $due = $booking->dueAt();

        $this->adminUpdate($booking, ['returned_at' => $due->copy()->addMinutes(50)->format('Y-m-d\TH:i')]);
        $this->assertSame(0, $booking->fresh()->late_fee); // dalam toleransi

        $this->adminUpdate($booking->fresh(), ['returned_at' => $due->copy()->addHours(3)->format('Y-m-d\TH:i')]);
        $this->assertSame(100_000, $booking->fresh()->late_fee); // 3 jam - 1 jam toleransi = 2 jam
        $this->assertSame(600_000, $booking->fresh()->total_fee);

        $this->adminUpdate($booking->fresh(), ['returned_at' => $due->copy()->addHours(3)->format('Y-m-d\TH:i'), 'late_fee' => 25_000]);
        $this->assertSame(25_000, $booking->fresh()->late_fee);
        $this->assertTrue($booking->fresh()->late_fee_adjusted);

        $this->adminUpdate($booking->fresh(), ['returned_at' => $due->copy()->addHours(3)->format('Y-m-d\TH:i'), 'auto_late_fee' => true]);
        $this->assertSame(100_000, $booking->fresh()->late_fee);
        $this->assertFalse($booking->fresh()->late_fee_adjusted);
    }

    public function test_city_package_has_no_tolerance(): void
    {
        $booking = $this->book('4h', false, 0); // 08.00–12.00
        $this->adminUpdate($booking, ['returned_at' => $booking->dueAt()->addMinutes(10)->format('Y-m-d\TH:i')]);

        $this->assertSame(50_000, $booking->fresh()->late_fee);
    }
}
