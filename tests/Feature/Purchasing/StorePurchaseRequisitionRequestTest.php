<?php

namespace Tests\Feature\Purchasing;

use App\Http\Requests\Purchasing\StorePurchaseRequisitionRequest;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Purchasing\Item;
use App\Models\Purchasing\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePurchaseRequisitionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Division $division;

    protected User $user;

    protected Unit $unit;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'code' => 'CO1',
            'name' => 'Company One',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'code' => 'DIV1',
            'name' => 'Division One',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'password' => bcrypt('password'),
        ]);

        $this->unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        $this->item = Item::create([
            'code' => 'ITM01',
            'name' => 'Item One',
            'description' => 'Test item',
            'item_type' => 'Raw Material',
            'unit_id' => $this->unit->id,
        ]);
    }

    protected function validateData(array $data): \Illuminate\Validation\Validator
    {
        $request = StorePurchaseRequisitionRequest::create('/api/purchase-requisitions', 'POST', $data);
        $request->setContainer(app());

        return Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );
    }

    public function test_valid_purchase_requisition_input_passes_validation(): void
    {
        $data = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Pengadaan bahan baku',
            'notes' => 'Catatan penting',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 15.5,
                    'notes' => 'Detail spec item',
                ],
            ],
        ];

        $validator = $this->validateData($data);
        $this->assertTrue($validator->passes());
    }

    public function test_fails_when_division_does_not_belong_to_company(): void
    {
        $otherCompany = Company::create([
            'code' => 'CO2',
            'name' => 'Company Two',
            'is_active' => true,
        ]);

        $otherDivision = Division::create([
            'company_id' => $otherCompany->id,
            'code' => 'DIV2',
            'name' => 'Division Two',
            'is_active' => true,
        ]);

        $data = [
            'company_id' => $this->company->id,
            'division_id' => $otherDivision->id, // Mismatched division!
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Test',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $validator = $this->validateData($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('division_id', $validator->errors()->toArray());
    }

    public function test_fails_when_required_date_is_before_request_date(): void
    {
        $data = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-15',
            'required_date' => '2026-09-10', // Before request_date!
            'purpose' => 'Test',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $validator = $this->validateData($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('required_date', $validator->errors()->toArray());
    }

    public function test_fails_when_items_array_is_empty(): void
    {
        $data = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Test',
            'items' => [], // Empty!
        ];

        $validator = $this->validateData($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items', $validator->errors()->toArray());
    }

    public function test_fails_when_quantity_is_zero_or_negative(): void
    {
        $data = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Test',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 0, // Invalid!
                ],
            ],
        ];

        $validator = $this->validateData($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items.0.quantity', $validator->errors()->toArray());
    }
}
