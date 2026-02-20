<?php

namespace ExprAs\Doctrine\Behavior\FieldAudit;

interface FieldAuditInterface
{
    public function getFieldValue();
    public function setFieldValue(mixed $value);
}
