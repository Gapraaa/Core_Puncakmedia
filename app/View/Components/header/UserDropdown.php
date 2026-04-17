<?php

namespace App\View\Components\header;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserDropdown extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.header.user-dropdown', [
            'user' => Auth::user(),
        ]);
    }
}
