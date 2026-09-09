<?php

namespace Tests\Feature\Purchasing;

use App\Http\Resources\Purchasing\PurchaseRequisitionItemResource;
use App\Http\Resources\Purchasing\PurchaseRequisitionResource;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Item;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\Purchasing\PurchaseRequisitionItem;
use App\Models\Purchasing\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequisitionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Division $division;

    protected Employee $employee;

    protected User $user;

    protected Unit $unit;

    protected Item $item;

    protected PurchaseRequisition $pr;

    protected PurchaseRequisitionItem $prItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'code' => 'CO_RES',
            'name' => 'Company Resource Test',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'code' => 'DIV_RES',
            'name' => 'Division Resource Test',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'nik' => 'EMP-RES-01',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
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
            'code' => 'ITM-RES',
            'name' => 'Widget A',
            'description' => 'Master widget description',
            'item_type' => 'Raw Material',
            'unit_id' => $this->unit->id,
        ]);

        $this->pr = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-000099',
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'status' => 'draft',
            'purpose' => 'Resource testing purpose',
            'notes' => 'Header notes test',
        ]);

        $this->prItem = PurchaseRequisitionItem::create([
            'purchase_requisition_id' => $this->pr->id,
            'item_id' => $this->item->id,
            'unit_id' => $this->unit->id,
            'quantity' => 25.5,
            'notes' => 'Item detail notes test',
        ]);
    }

    public function test_resource_transforms_header_without_relations(): void
    {
        $resource = (new PurchaseRequisitionResource($this->pr))->resolve();

        $this->assertEquals($this->pr->id, $resource['id']);
        $this->assertEquals('PR-2026-000099', $resource['pr_number']);
        $this->assertEquals($this->company->id, $resource['company_id']);
        $this->assertEquals($this->division->id, $resource['division_id']);
        $this->assertEquals($this->user->id, $resource['requester_id']);
        $this->assertEquals('2026-09-09', $resource['request_date']);
        $this->assertEquals('2026-09-15', $resource['required_date']);
        $this->assertEquals('Resource testing purpose', $resource['purpose']);
        $this->assertEquals('Header notes test', $resource['notes']);
        $this->assertEquals('draft', $resource['status']);
        $this->assertNotNull($resource['created_at']);
        $this->assertNotNull($resource['updated_at']);

        // whenLoaded relations should not be present when not loaded
        $this->assertArrayNotHasKey('company', $resource);
        $this->assertArrayNotHasKey('division', $resource);
        $this->assertArrayNotHasKey('requester', $resource);
        $this->assertArrayNotHasKey('items', $resource);
    }

    public function test_resource_transforms_with_eager_loaded_relations(): void
    {
        $this->pr->load(['company', 'division', 'requester.employee', 'items.item', 'items.unit']);

        $resource = (new PurchaseRequisitionResource($this->pr))->response()->getData(true)['data'];

        $this->assertArrayHasKey('company', $resource);
        $this->assertEquals('CO_RES', $resource['company']['code']);

        $this->assertArrayHasKey('division', $resource);
        $this->assertEquals('DIV_RES', $resource['division']['code']);

        $this->assertArrayHasKey('requester', $resource);
        $this->assertEquals($this->user->id, $resource['requester']['id']);
        $this->assertEquals('John Doe', $resource['requester']['name']);
        $this->assertEquals('john.doe@example.com', $resource['requester']['email']);

        $this->assertArrayHasKey('items', $resource);
        $this->assertCount(1, $resource['items']);

        $itemData = $resource['items'][0];
        $this->assertEquals($this->prItem->id, $itemData['id']);
        $this->assertEquals(25.5, $itemData['quantity']);
        $this->assertEquals('Item detail notes test', $itemData['notes']);
        $this->assertArrayNotHasKey('description', $itemData); // Item detail PR has notes, NOT description!

        // Master item description comes from related item
        $this->assertEquals('Master widget description', $itemData['item']['description']);
        $this->assertEquals('PCS', $itemData['unit']['code']);
    }

    public function test_item_resource_structure(): void
    {
        $this->prItem->load(['item', 'unit']);

        $resource = (new PurchaseRequisitionItemResource($this->prItem))->resolve();

        $this->assertEquals($this->prItem->id, $resource['id']);
        $this->assertEquals($this->pr->id, $resource['purchase_requisition_id']);
        $this->assertEquals($this->item->id, $resource['item_id']);
        $this->assertEquals($this->unit->id, $resource['unit_id']);
        $this->assertEquals(25.5, $resource['quantity']);
        $this->assertEquals('Item detail notes test', $resource['notes']);
        $this->assertEquals('Widget A', $resource['item']['name']);
        $this->assertEquals('PCS', $resource['unit']['code']);
    }
}
