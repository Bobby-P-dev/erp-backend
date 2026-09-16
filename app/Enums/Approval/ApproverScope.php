<?php

declare(strict_types=1);

namespace App\Enums\Approval;

enum ApproverScope: string
{
    case RoleOnly = 'role_only';
    case RoleAndDivision = 'role_and_division';
    case JobLevelAndDivision = 'job_level_and_division';
    case DepartmentHead = 'department_head';
    case SpecificUser = 'specific_user';
}
