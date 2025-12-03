<?php

namespace Qsiq\RobotApi\Domain\Lead;

final class RobotLeadSnapshot
{
    public function __construct(
        public readonly ?string $phone,
        public readonly ?string $name,
        public readonly ?string $licenseSheet,
        public readonly ?string $sumAp,
        public readonly ?string $address
    ) {
    }

    public function toArray(): array
    {
        return [
            'init_phone' => $this->phone,
            'init_name' => $this->name,
            'init_lic_scheet' => $this->licenseSheet,
            'init_sum_ap' => $this->sumAp,
            'init_addr' => $this->address,
        ];
    }
}
