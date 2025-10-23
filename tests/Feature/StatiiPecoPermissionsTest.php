<?php

namespace Tests\Feature;

use App\Models\StatiePeco;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class StatiiPecoPermissionsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    public function test_dispatcher_can_import_fuel_stations(): void
    {
        $dispatcher = $this->createUserWithRoles('dispecer');

        $this->assertTrue($dispatcher->hasPermission('statii-peco'));
        $this->assertFalse($dispatcher->hasPermission('statii-peco-manage'));

        $file = $this->createStationsWorkbook([
            ['9001', 'Stație Test', 'Strada Principală 1', '012345', 'Cluj-Napoca', '46.7700,23.5800'],
        ]);

        $response = $this->actingAs($dispatcher)->post('/statii-peco/excel-import', [
            'fisier_excel' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('statii_peco', [
            'numar_statie' => '9001',
            'nume' => 'Stație Test',
        ]);
    }

    public function test_dispatcher_cannot_mass_delete_fuel_stations(): void
    {
        $dispatcher = $this->createUserWithRoles('dispecer');

        $this->assertTrue($dispatcher->hasPermission('statii-peco'));
        $this->assertFalse($dispatcher->hasPermission('statii-peco-manage'));

        StatiePeco::create([
            'numar_statie' => '7001',
            'nume' => 'Stație Protejată',
            'strada' => 'Strada Libertății 10',
            'cod_postal' => '400100',
            'localitate' => 'Cluj-Napoca',
            'coordonate' => '46.7712,23.6236',
        ]);

        $response = $this->actingAs($dispatcher)->get('/statii-peco?action=massDelete');

        $response->assertForbidden();
        $this->assertDatabaseHas('statii_peco', ['numar_statie' => '7001']);
    }

    public function test_admin_can_mass_delete_fuel_stations(): void
    {
        $admin = $this->createUserWithRoles('admin');

        StatiePeco::create([
            'numar_statie' => '8001',
            'nume' => 'Stație Ștersă',
            'strada' => 'Strada Republicii 5',
            'cod_postal' => '500200',
            'localitate' => 'Brașov',
            'coordonate' => '45.6427,25.5887',
        ]);

        $response = $this->actingAs($admin)->get('/statii-peco?action=massDelete');

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('statii_peco', ['numar_statie' => '8001']);
    }

    private function createStationsWorkbook(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['numar_statie', 'nume', 'strada', 'cod_postal', 'localitate', 'coordonate'],
            ...$rows,
        ]);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'statii_peco');
        $filePath = $temporaryPath . '.xlsx';
        @unlink($temporaryPath);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        return new UploadedFile(
            $filePath,
            'statii-peco.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
