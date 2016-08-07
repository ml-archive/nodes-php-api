<?php

namespace Nodes\Api\Auth\EmailVerification;

use Carbon\Carbon;
use Nodes\Database\Eloquent\Model as NodesModel;

/**
 * Class EmailVerificationModel.
 */
class EmailVerificationModel extends NodesModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_verifications';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token',
        'used',
        'expire_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expire_at',
    ];

    /**
     * EmailAuthorizationModel constructor.
     *
     * @param  array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Override table with the one from config
        $this->table = config('nodes.api.email-verifications.table', 'user_verifications');
    }

    /**
     * Check if token has expired.
     *
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @return bool
     */
    public function isExpired()
    {
        return (bool) Carbon::now()->gt($this->expire_at);
    }

    /**
     * Check if token has already been used.
     *
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @return bool
     */
    public function isUsed()
    {
        return (bool) $this->used;
    }

    /**
     * Mark token has used.
     *
     * @author Paulius Navickas <pana@nodes.dk>
     *
     * @return bool
     */
    public function markAsUsed()
    {
        return (bool) $this->update(['used' => 1]);
    }
}
