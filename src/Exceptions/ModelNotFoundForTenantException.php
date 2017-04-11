<?php

declare(strict_types=1);

namespace Rinvex\Tenantable\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class ModelNotFoundForTenantException extends ModelNotFoundException
{
    /**
     * @param string $model
     * @param int|array $ids
     *
     * @return $this
     */
    public function setModel($model, $ids = [])
    {
        $this->model = $model;
        $this->message = "No query results for model [{$model}] when scoped by tenant.";

        return $this;
    }
}
