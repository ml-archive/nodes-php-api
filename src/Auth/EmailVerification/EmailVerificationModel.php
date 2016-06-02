<?php
namespace Nodes\Api\Auth\EmailVerification;

use Carbon\Carbon;
use Nodes\Database\Eloquent\Model as NodesModel;

/**
 * Class EmailVerificationModel
 * @package Nodes\Api\Auth\EmailVerification
 * @author Paulius Navickas <pana@nodes.dk>
 */
class EmailVerificationModel extends NodesModel
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'email_verification';

    /**
     * Indicates if the model should be timestamped
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'verification',
        'used',
        'expire_at'
    ];

    /**
     * The attributes that should be mutated to dates
     *
     * @var array
     */
    protected $dates = [
        'expire_at'
    ];

    /**
     * EmailAuthorizationModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Override table with the one from config
        $this->table = config('nodes.api.email-verification.table', 'user_verification');
    }

    /**
     * @return bool
     * @author Paulius Navickas <pana@nodes.dk>
     */
    public function isExpired()
    {
        return (bool) Carbon::now()->gt($this->expire_at);
    }

    /**
     * @return bool
     * @author Paulius Navickas <pana@nodes.dk>
     */
    public function isUsed()
    {
        return (bool) $this->used;
    }

    /**
     * @return bool
     * @author Paulius Navickas <pana@nodes.dk>
     */
    public function markAsUsed()
    {
        return (bool) $this->update(['used' => 1]);
    }
}