<?php

namespace Tests\Feature\Core;

use App\Models\Core\Company;
use App\Models\Core\DocumentNumberSequence;
use App\Services\Core\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentNumberService $service;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DocumentNumberService;
        $this->company = Company::firstOrCreate(
            ['code' => 'TEST_CO'],
            ['name' => 'Test Company', 'is_active' => true]
        );
    }

    public function test_can_generate_sequential_document_number(): void
    {
        $sequence = DocumentNumberSequence::create([
            'company_id' => $this->company->id,
            'category' => 'TEST_PR',
            'period' => '2026',
            'prefix' => 'PR',
            'current_number' => 0,
            'format' => '{prefix}-{period}-{number}',
            'number_length' => 6,
            'is_active' => true,
        ]);

        $firstNumber = $this->service->generate($this->company->id, 'TEST_PR', '2026');
        $this->assertEquals('PR-2026-000001', $firstNumber);
        $this->assertEquals(1, $sequence->fresh()->current_number);

        $secondNumber = $this->service->generate($this->company->id, 'TEST_PR', '2026');
        $this->assertEquals('PR-2026-000002', $secondNumber);
        $this->assertEquals(2, $sequence->fresh()->current_number);
    }

    public function test_defaults_to_current_year_when_period_is_null(): void
    {
        $currentYear = (string) date('Y');

        DocumentNumberSequence::create([
            'company_id' => $this->company->id,
            'category' => 'TEST_AUTO_YEAR',
            'period' => $currentYear,
            'prefix' => 'PR',
            'current_number' => 0,
            'format' => '{prefix}-{period}-{number}',
            'number_length' => 4,
            'is_active' => true,
        ]);

        $generated = $this->service->generate($this->company->id, 'TEST_AUTO_YEAR');
        $this->assertEquals("PR-{$currentYear}-0001", $generated);
    }

    public function test_convenience_helper_generates_pr_number(): void
    {
        $currentYear = (string) date('Y');

        DocumentNumberSequence::create([
            'company_id' => $this->company->id,
            'category' => 'PR',
            'period' => '9999',
            'prefix' => 'PR',
            'current_number' => 10,
            'format' => '{prefix}-{period}-{number}',
            'number_length' => 6,
            'is_active' => true,
        ]);

        $generated = $this->service->generatePurchaseRequisitionNumber($this->company->id, '9999');
        $this->assertEquals('PR-9999-000011', $generated);
    }

    public function test_throws_exception_when_sequence_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Document number sequence not found');

        $this->service->generate($this->company->id, 'NON_EXISTENT_CATEGORY', '2026');
    }

    public function test_throws_exception_when_sequence_is_inactive(): void
    {
        DocumentNumberSequence::create([
            'company_id' => $this->company->id,
            'category' => 'TEST_INACTIVE',
            'period' => '2026',
            'prefix' => 'PR',
            'current_number' => 0,
            'format' => '{prefix}-{period}-{number}',
            'number_length' => 6,
            'is_active' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is inactive');

        $this->service->generate($this->company->id, 'TEST_INACTIVE', '2026');
    }
}
