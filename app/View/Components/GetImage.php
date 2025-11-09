<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GetImage extends Component
{
    public $image_url;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($imageUrl)
    {
        $this->image_url = $imageUrl;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.image');
    }
}
