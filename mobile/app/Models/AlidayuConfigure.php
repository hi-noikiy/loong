<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AlidayuConfigure
 */
class AlidayuConfigure extends Model
{
    protected $table = 'alidayu_configure';

    public $timestamps = false;

    protected $fillable = [
        'temp_id',
        'temp_content',
        'add_time',
        'set_sign',
        'send_time',
        'signature'
    ];

    protected $guarded = [];

    
    /**
     * @return mixed
     */
    public function getTempId()
    {
        return $this->temp_id;
    }

    /**
     * @return mixed
     */
    public function getTempContent()
    {
        return $this->temp_content;
    }

    /**
     * @return mixed
     */
    public function getAddTime()
    {
        return $this->add_time;
    }

    /**
     * @return mixed
     */
    public function getSetSign()
    {
        return $this->set_sign;
    }

    /**
     * @return mixed
     */
    public function getSendTime()
    {
        return $this->send_time;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTempId($value)
    {
        $this->temp_id = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTempContent($value)
    {
        $this->temp_content = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAddTime($value)
    {
        $this->add_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSetSign($value)
    {
        $this->set_sign = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSendTime($value)
    {
        $this->send_time = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSignature($value)
    {
        $this->signature = $value;
        return $this;
    }



}