<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 11/20/20, 7:06 AM
 * Copyright (c) 2021. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodeMail extends Mailable
{
    use Queueable, SerializesModels;
    public $method, $creator, $model, $session, $bridge;

    public function __construct($method, $creator, $model, $session, $bridge)
    {
        list($this->method, $this->creator, $this->model, $this->session, $this->bridge) = func_get_args();
    }

    public function build()
    {
        return $this->markdown('emails.iauth.code')
            ->subject(_t(ipreference("iauth.sessions.models.{$this->method}.title")))
            ->with([
                'method' => $this->method,
                'creator' => $this->creator,
                'model' => $this->model,
                'session' => $this->session,
                'bridge' => $this->bridge,
            ]);
    }
}
