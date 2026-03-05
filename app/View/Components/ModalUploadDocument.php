<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ModalUploadDocument extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $categories;
    public $id;

    public function __construct($categories, $id)
    {
        $this->categories = $categories;
        $this->id = $id;
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal-upload-document');
    }
}
