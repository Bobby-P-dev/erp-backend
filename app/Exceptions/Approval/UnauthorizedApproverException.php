<?php

declare(strict_types=1);

namespace App\Exceptions\Approval;

use DomainException;

final class UnauthorizedApproverException extends DomainException {}
