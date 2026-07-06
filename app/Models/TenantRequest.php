<?php

namespace App\Models;

use App\Enums\EnumTenantRequestStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A request for a (test) account submitted via the public form on the login
 * page. Deliberately NOT tenant scoped — the requester has no tenant yet;
 * super admins review these requests centrally.
 *
 * @property string $name
 * @property string $email
 * @property string $solawiName
 * @property string|null $websiteUrl
 * @property EnumTenantRequestStatus $status
 * @property string|null $tenant_id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 */
class TenantRequest extends BaseModel
{
    use HasFactory;

    public const TABLE = 'tenant_request';

    protected $table = self::TABLE;

    public const COL_NAME = 'name';

    public const COL_EMAIL = 'email';

    public const COL_SOLAWI_NAME = 'solawiName';

    public const COL_WEBSITE_URL = 'websiteUrl';

    public const COL_STATUS = 'status';

    public const COL_FK_TENANT = 'tenant_id';

    protected $fillable = [
        self::COL_NAME,
        self::COL_EMAIL,
        self::COL_SOLAWI_NAME,
        self::COL_WEBSITE_URL,
        self::COL_STATUS,
    ];

    protected $casts = [
        self::COL_STATUS => EnumTenantRequestStatus::class,
    ];

    public function isPending(): bool
    {
        return $this->status === EnumTenantRequestStatus::PENDING;
    }
}
