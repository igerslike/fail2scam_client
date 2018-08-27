<?php

/**
 * Class Record
 */

namespace F2S;

class Record
{

    protected $data = [];
    protected $isValid = false;
    protected $messages = [];

    /**
     * Add an email to be filtered
     *
     * @param string $email
     * @return $this
     */
    public function filterByEmail($email)
    {
        $this->data['email'] = $email;
        return $this;
    }

    /**
     * Add an phone number to be filtered
     *
     * @param int $number
     * @return $this
     */
    public function filterByPhoneNumber($number)
    {
        $this->data['phone'] = (int) $number;
        return $this;
    }

    /**
     * Add an IP address to be filtered
     *
     * @param string $ip
     * @return $this
     */
    public function filterByIpAddress($ip)
    {
        $this->data['ip'] = $ip;
        return $this;
    }

    /**
     * Add an credit card number to be filtered
     *
     * @param string|int $number
     * @return $this
     */
    public function filterByCreditCardNumber($number)
    {
        $this->data['cc_number'] = $number;
        return $this;
    }

    /**
     * Set report reason
     *
     * @param $reason
     * @return $this
     */
    public function setReason($reason)
    {
        $this->data['report'] = $reason;
        return $this;
    }

    /**
     * Get data from Record
     *
     * @return array
     * @throws Exception
     */
    public function getData()
    {
        if (empty($this->data)) {
            throw new \Exception("At least one field should be specified for search!");
        }

        return array_intersect_key($this->data, array_flip(['email', 'phone', 'ip', 'cc_number', 'report']));
    }

    /**
     * To be set by the client after validation
     *
     * @param $validation
     */
    public function setValidity($validation)
    {
        $this->isValid = $validation;
    }

    /**
     * To be set by the client after validation
     *
     * @param $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    public function dumpMessages()
    {
        return implode(' | ', $this->messages);
    }

    /**
     * Check if record data is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }
}
