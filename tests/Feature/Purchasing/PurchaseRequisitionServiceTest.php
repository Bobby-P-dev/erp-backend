<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\DocumentNumberSequence;
use App\Models\Core\Employee;
use App\Models\Purchasing\Item;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\Purchasing\PurchaseRequisitionItem;
use App\Models\Purchasing\Unit;
use App\Models\User;
use App\Services\Purchasing\PurchaseRequisitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class PurchaseRequisitionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PurchaseRequisitionService $service;

    protected Company $company;

    protected Division $division;

    protected Employee $employee;

    protected User $user;

    protected Unit $unit;

    protected Item $item;

    protected DocumentNumberSequence $sequence;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PurchaseRequisitionService::class);

        $this->company = Company::create([
            'code' => 'PSI',
            'name' => 'PT Padma Soode Indonesia',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'code' => 'PUR',
            'name' => 'Purchasing',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'nik' => 'EMP001',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'employee_id' => $this->employee->id,
            'password' => bcrypt('password'),
        ]);

        $this->unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        $this->item = Item::create([
            'code' => 'ITM-001',
            'name' => 'Baut Hexagonal',
            'description' => 'Baut baja hexagonal M8',
            'item_type' => 'Raw Material',
            'unit_id' => $this->unit->id,
        ]);

        $this->sequence = DocumentNumberSequence::create([
            'company_id' => $this->company->id,
            'category' => 'PR',
            'period' => '2026',
            'prefix' => 'PR',
            'current_number' => 0,
            'format' => '{prefix}-{period}-{number}',
            'number_length' => 6,
            'is_active' => true,
        ]);
    }

    public function test_creates_purchase_requisition_with_items_and_generates_pr_number(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Kebutuhan lini produksi',
            'notes' => 'Catatan penting PR',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 100.5,
                    'notes' => 'Toleransi 0.1mm',
                ],
            ],
        ];

        $pr = $this->service->create($payload);

        $this->assertInstanceOf(PurchaseRequisition::class, $pr);
        $this->assertEquals('PR-2026-000001', $pr->pr_number);
        $this->assertEquals($this->company->id, $pr->company_id);
        $this->assertEquals($this->division->id, $pr->division_id);
        $this->assertEquals($this->user->id, $pr->requester_id);
        $this->assertEquals('draft', $pr->status);
        $this->assertEquals('Kebutuhan lini produksi', $pr->purpose);
        $this->assertEquals('Catatan penting PR', $pr->notes);

        // Verify sequence incremented
        $this->assertEquals(1, $this->sequence->fresh()->current_number);

        // Verify items created
        $this->assertCount(1, $pr->items);
        $prItem = $pr->items->first();
        $this->assertEquals($this->item->id, $prItem->item_id);
        $this->assertEquals($this->unit->id, $prItem->unit_id);
        $this->assertEquals(100.5, $prItem->quantity);
        $this->assertEquals('Toleransi 0.1mm', $prItem->notes);

        // Verify relations are loaded on return
        $this->assertTrue($pr->relationLoaded('company'));
        $this->assertTrue($pr->relationLoaded('division'));
        $this->assertTrue($pr->relationLoaded('requester'));
        $this->assertTrue($pr->requester->relationLoaded('employee'));
        $this->assertEquals('Budi Santoso', $pr->requester->employee->name);
        $this->assertTrue($pr->relationLoaded('items'));
        $this->assertTrue($prItem->relationLoaded('item'));
        $this->assertTrue($prItem->relationLoaded('unit'));
    }

    public function test_does_not_save_description_field_into_detail_table(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Test ignore description',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 10,
                    'notes' => 'Catatan sah',
                    'description' => 'Deskripsi tidak boleh masuk ke purchase_requestion_items',
                ],
            ],
        ];

        $pr = $this->service->create($payload);

        $this->assertDatabaseHas('purchase_requestion_items', [
            'purchase_requisition_id' => $pr->id,
            'notes' => 'Catatan sah',
        ]);

        $this->assertEquals(1, PurchaseRequisitionItem::count());
    }

    public function test_transaction_rolls_back_when_item_creation_fails(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Rollback test',
            'items' => [
                [
                    'item_id' => 999999, // Invalid foreign key to cause SQL failure!
                    'unit_id' => $this->unit->id,
                    'quantity' => 10,
                ],
            ],
        ];

        try {
            $this->service->create($payload);
            $this->fail('Expected exception was not thrown');
        } catch (Throwable $e) {
            // Expected
        }

        // Neither PR header nor items should be in DB
        $this->assertEquals(0, PurchaseRequisition::count());
        $this->assertEquals(0, PurchaseRequisitionItem::count());
    }

    public function test_can_submit_purchase_requisition(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Submit test',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $pr = $this->service->create($payload);
        $this->assertEquals('draft', $pr->status);

        $submittedPr = $this->service->submit($pr);
        $this->assertEquals('submitted', $submittedPr->status);
        $this->assertEquals('submitted', $pr->fresh()->status);
    }

    public function test_cannot_submit_already_submitted_purchase_requisition(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Submit twice test',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $pr = $this->service->create($payload);
        $this->service->submit($pr);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot be submitted');

        $this->service->submit($pr);
    }
}
