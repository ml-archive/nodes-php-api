<?php
namespace Nodes\Api\Auth\ResetPassword;

use Carbon\Carbon;
use Nodes\Database\Eloquent\Model as NodesModel;

/**
 * Class Model
 *
 * @package Nodes\Api\Auth\ResetPassword
 */
class ResetPasswordModel extends NodesModel
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'user_reset_password_tokens';

    /**
     * Indicates if the model should be timestamped
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'token',
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
     * Constructor
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Override table with the one from config
        $this->table = config('nodes.api.reset-password.table', 'user_reset_password_tokens');
    }

    /**
     * Check if token is expired
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return boolean
     */
    public function isExpired()
    {
        return (bool) Carbon::now()->gt($this->expire_at);
    }

    /**
     * Check if token has already been used
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return boolean
     */
    public function isUsed()
    {
        return (bool) $this->used;
    }

    /**
     * Mark token as used
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return boolean
     */
    public function markAsUsed()
    {
        return (bool) $this->update(['used' => 1]);
    }
}